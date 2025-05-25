document.addEventListener(
    'DOMContentLoaded', function () {
        let elems = document.querySelectorAll('select');
        let instances = M.FormSelect.init(elems);

        // Attendre un moment avant de mettre à jour les options
        setTimeout(
            function () {
                updateSelectOptions();
            }, 500
        );
        initTooltips();
    }
);

function updateSelectOptions()
{
    let selectElements = document.querySelectorAll('select');
    selectElements.forEach(
        select =>
        {
            let selectInstance = M.FormSelect.getInstance(select);
            if (selectInstance) {
                try {
                    selectInstance.update();
                }
                catch (error) {

                }
            }
        }
    );
}

function initTooltips()
{
    document.querySelectorAll(
        '.tooltip-container'
    ).forEach(
        container =>
        {
            container.addEventListener(
                'mouseenter', () =>
                {
                    // Vérifie s'il existe déjà un tooltip pour éviter les doublons
                    let existingTooltip = document.querySelector('.tooltip');
                    if (existingTooltip) {
                        return;
                    }  // Si un tooltip existe, on ne crée pas un nouveau

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
                    container.addEventListener(
                        'mousemove', (e) =>
                        {
                            const mouseX = e.pageX;
                            const mouseY = e.pageY;

                            // Positionner le tooltip au-dessus de la souris
                            tooltip.style.left = `${mouseX - (tooltip.offsetWidth / 2)}px`;
                            tooltip.style.top = `${mouseY - tooltip.offsetHeight - 10}px`;
                        }
                    );

                }
            );

            container.addEventListener(
                'mouseleave', () =>
                {
                    // Supprime le tooltip
                    const tooltip = document.querySelector('.tooltip');
                    if (tooltip) {
                        tooltip.remove();
                    }
                }
            );
        }
    );
}

/* Séparateur */
/*
document.addEventListener('DOMContentLoaded', () => {
    const mapping = {
        'choose-students': 'students-section',
        'choose-teachers': 'teachers-section',
        'choose-internships': 'internships-section'
    };

    const items = document.querySelectorAll('.choose-item');

    items.forEach(item => {
        item.addEventListener('click', () => {
            const targetId = mapping[item.id];
            const targetSection = document.getElementById(targetId);

            const isActive = item.classList.contains('active');

            // Réinitialiser tous les éléments et masquer toutes les sections
            items.forEach(i => i.classList.remove('active'));
            Object.values(mapping).forEach(id => {
                document.getElementById(id).style.display = 'none';
            });

            // Si l'élément n'était pas actif, on l'active
            if (!isActive) {
                item.classList.add('active');
                targetSection.style.display = 'block';
            }
        });
    });

}); */
document.addEventListener('DOMContentLoaded', () => {
    const mapping = {
        'choose-students': 'students-section',
        'choose-teachers': 'teachers-section',
        'choose-internships': 'internships-section'
    };

    const items = document.querySelectorAll('.choose-item');

    items.forEach(item => {
        item.addEventListener('click', () => {
            const targetId = mapping[item.id];
            const targetSection = document.getElementById(targetId);
            const isActive = item.classList.contains('active');

            // Cacher toutes les sections
            items.forEach(i => i.classList.remove('active'));
            Object.values(mapping).forEach(id => {
                const section = document.getElementById(id);
                section.classList.remove('visible');
                // petit délai pour laisser l'animation se finir
                setTimeout(() => {
                    if (!section.classList.contains('visible')) {
                        section.style.display = 'none';
                    }
                }, 300); // même durée que la transition CSS
            });

            if (!isActive) {
                item.classList.add('active');
                targetSection.style.display = 'block';
                void targetSection.offsetWidth;
                targetSection.classList.add('visible');
                if (window.innerWidth <= 600) {
                    targetSection.scrollIntoView({behavior: 'smooth'});
                }
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const importExportToggle = document.getElementById('import-export-toggle');
    const simpleAdvancedToggle = document.getElementById('simple-advanced-toggle');

    importExportToggle.addEventListener('change', function() {
        const mode = this.checked ? 'export' : 'import';
        console.log('Mode sélectionné:', mode);
    });

    simpleAdvancedToggle.addEventListener('change', function() {
        const mode = this.checked ? 'advanced' : 'simple';
        console.log('Mode sélectionné:', mode);
    });
});