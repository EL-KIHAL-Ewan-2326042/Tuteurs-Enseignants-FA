/**
 * Dès le chargement de la page on associe un listener à l'input 'newMaxNumber'
 * @type {HTMLElement}
 */
document.addEventListener('DOMContentLoaded', function() {
    const maxNumberInput = document.getElementById("newMaxNumber");
    maxNumberInput.addEventListener("change", () => {
        if (maxNumberInput.value < maxNumberInput.min) maxNumberInput.value = maxNumberInput.min;
        if (maxNumberInput.value > maxNumberInput.max) maxNumberInput.value = maxNumberInput.max;
    });
});