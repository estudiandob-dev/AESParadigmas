/**
 * Parte B – Aplicación práctica en el proyecto TP1
 * Captura en tiempo real el cambio de cantidad (y precio)
 * y muestra el subtotal a abonar sin refrescar la página.
 *
 * Subtotal = cantidad × precio de venta unitario
 */

(function () {
    'use strict';

    const inputCantidad = document.getElementById('quantity');
    const inputPrecio   = document.getElementById('salePrice');
    const spanSubtotal  = document.getElementById('subtotal');

    if (!inputCantidad || !inputPrecio || !spanSubtotal) {
        console.warn('Parte B: no se encontraron los elementos quantity / salePrice / subtotal');
        return;
    }

    function actualizarSubtotal() {
        // Captura de valores numéricos (vacío → 0)
        const cantidad = Number(inputCantidad.value) || 0;
        const precio   = Number(inputPrecio.value)   || 0;

        // Cálculo en memoria
        const subtotal = cantidad * precio;

        // Inyección en el DOM (formato con 2 decimales)
        spanSubtotal.textContent = subtotal.toFixed(2);
    }

    // Eventos en tiempo real: 'input' se dispara en cada tecla / cambio
    inputCantidad.addEventListener('input', actualizarSubtotal);
    inputPrecio.addEventListener('input', actualizarSubtotal);

    // Valor inicial al cargar
    actualizarSubtotal();
})();
