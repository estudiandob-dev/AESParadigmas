<?php
/*
 * Endpoint de compra: descuenta el stock directamente en data/inventario.json.
 * Recibe:   POST JSON  { "items": [ { "id": 1, "cantidad": 2 }, ... ] }
 * Responde: JSON       { "ok": true|false, "mensaje": "...", "productos": [...] }
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder(int $codigo, array $cuerpo): void
{
    http_response_code($codigo);
    echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
}

$entrada = json_decode(file_get_contents('php://input'), true);
$items = is_array($entrada) ? ($entrada['items'] ?? null) : null;

if (!is_array($items) || count($items) === 0) {
    responder(400, ['ok' => false, 'mensaje' => 'El pedido está vacío.']);
}

// Se agrupan las cantidades por id y se validan.
$pedido = [];
foreach ($items as $item) {
    $id = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
    $cantidad = filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $cantidad === false || $cantidad < 1) {
        responder(400, ['ok' => false, 'mensaje' => 'Pedido inválido.']);
    }

    $pedido[$id] = ($pedido[$id] ?? 0) + $cantidad;
}

$ruta = __DIR__ . '/../data/inventario.json';
$archivo = @fopen($ruta, 'c+');

if (!$archivo) {
    responder(500, ['ok' => false, 'mensaje' => 'No se pudo abrir el inventario.']);
}

// Bloqueo exclusivo: evita que dos compras simultáneas pisen el archivo.
flock($archivo, LOCK_EX);

$productos = json_decode(stream_get_contents($archivo), true);

if (!is_array($productos)) {
    flock($archivo, LOCK_UN);
    fclose($archivo);
    responder(500, ['ok' => false, 'mensaje' => 'El inventario está dañado o vacío.']);
}

// 1) Validar todo el pedido antes de modificar nada.
foreach ($pedido as $id => $cantidad) {
    $indice = array_search($id, array_column($productos, 'id'), true);

    if ($indice === false) {
        flock($archivo, LOCK_UN);
        fclose($archivo);
        responder(404, ['ok' => false, 'mensaje' => "El producto $id no existe.", 'productos' => $productos]);
    }

    if ($productos[$indice]['cantidad'] < $cantidad) {
        $nombre = $productos[$indice]['nombre'];
        $quedan = $productos[$indice]['cantidad'];
        flock($archivo, LOCK_UN);
        fclose($archivo);
        responder(409, [
            'ok' => false,
            'mensaje' => "Stock insuficiente de $nombre: quedan $quedan unidades.",
            'productos' => $productos
        ]);
    }
}

// 2) Descontar y guardar.
foreach ($pedido as $id => $cantidad) {
    $indice = array_search($id, array_column($productos, 'id'), true);
    $productos[$indice]['cantidad'] -= $cantidad;
}

ftruncate($archivo, 0);
rewind($archivo);
fwrite($archivo, json_encode($productos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
fflush($archivo);
flock($archivo, LOCK_UN);
fclose($archivo);

responder(200, ['ok' => true, 'mensaje' => 'Compra registrada.', 'productos' => $productos]);
