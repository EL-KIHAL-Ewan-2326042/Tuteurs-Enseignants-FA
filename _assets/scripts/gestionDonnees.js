document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems);

    // Attendre un moment avant de mettre à jour les options
    setTimeout(function() {
        updateSelectOptions();
    }, 500);
});

function updateSelectOptions() {
    var selectElements = document.querySelectorAll('select');
    selectElements.forEach(select => {
        var selectInstance = M.FormSelect.getInstance(select);
        if (selectInstance) {
            selectInstance.update();
        }
    });
}

function initTooltips() {
    document.querySelectorAll('.tooltip-container').forEach(container => {
        container.addEventListener('mouseenter', (e) => {
            // Vérifie s'il existe déjà un tooltip pour éviter les doublons
            let existingTooltip = document.querySelector('.tooltip');
            if (existingTooltip) return;  // Si un tooltip existe, on ne crée pas un nouveau

            // Crée un tooltip unique
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.innerHTML = container.getAttribute('data-tooltip'); // Interprète les balises HTML


            // Ajoute des styles pour afficher
            tooltip.style.position = 'absolute';
            tooltip.style.backgroundColor = '#333';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.boxShadow = '0px 4px 6px rgba(0, 0, 0, 0.1)';
            tooltip.style.zIndex = '1000';
            tooltip.style.whiteSpace = 'normal';

            // Ajoute le tooltip au body
            document.body.appendChild(tooltip);

            // Suivre la position de la souris
            container.addEventListener('mousemove', (e) => {
                const mouseX = e.pageX;
                const mouseY = e.pageY;

                // Positionner le tooltip au-dessus de la souris
                tooltip.style.left = `${mouseX - (tooltip.offsetWidth / 2)}px`;
                tooltip.style.top = `${mouseY - tooltip.offsetHeight - 10}px`;
            });

        });

        container.addEventListener('mouseleave', () => {
            // Supprime le tooltip
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });
}