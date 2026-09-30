/**
 * Parte A – Algorítmica (paso a paso)
 * 1. Captura dos valores numéricos desde <input type="number">
 * 2. Calcula la sumatoria en memoria (RAM)
 * 3. Inyecta el resultado en <span id="resultado">
 *    indicando si es positivo, cero o negativo.
 */

(function () {
    'use strict';

    const input1 = document.getElementById('valor1');
    const input2 = document.getElementById('valor2');
    const btn = document.getElementById('btnCalcular');
    const spanResultado = document.getElementById('resultado');

    function calcularSumatoria() {
        // 1. Captura de valores (convertidos a número)
        //    Si el campo está vacío, Number('') === 0
        const a = Number(input1.value);
        const b = Number(input2.value);

        // 2. Sumatoria acumulada en memoria (variable local)
        const total = a + b;

        // 3. Inyección en el DOM + clasificación
        let texto;
        let clase;

        if (total > 0) {
            texto = total + ' (positivo)';
            clase = 'positivo';
        } else if (total === 0) {
            texto = total + ' (cero)';
            clase = 'cero';
        } else {
            texto = total + ' (negativo)';
            clase = 'negativo';
        }

        spanResultado.textContent = texto;
        spanResultado.className = clase;
    }

    // Evento del botón
    btn.addEventListener('click', calcularSumatoria);

    // Opcional: recalcular al cambiar cualquier input (tiempo real)
    input1.addEventListener('input', calcularSumatoria);
    input2.addEventListener('input', calcularSumatoria);
})();
