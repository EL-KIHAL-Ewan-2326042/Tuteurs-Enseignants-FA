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
const importContent = document.getElementById('import-content');
const exportContent = document.getElementById('export-content');

// Fonction pour charger le contenu
async function loadContent(type, category = '') {
    try {
        const response = await fetch(`/api/${type}?category=${category}`);

        if (!response.ok) {
            throw new Error(`Impossible de charger le contenu ${type}, erreur: ${response.status}`);
        }

        const html = await response.text();

        if (type === 'import') {
            importContent.innerHTML = html;
            importContent.style.display = 'block';
            exportContent.style.display = 'none';
        } else {
            exportContent.innerHTML = html;
            exportContent.style.display = 'block';
            importContent.style.display = 'none';
        }

        updateSelectOptions();
        initTooltips();
    } catch (error) {
        console.error(`Error loading content: ${error.message}`);
        const errorMessage = `<div class="error-message card-panel red lighten-4">
            <p>Une erreur s'est produite lors du chargement du contenu. Veuillez réessayer.</p>
        </div>`;

        if (type === 'import') {
            importContent.innerHTML = errorMessage;
        } else {
            exportContent.innerHTML = errorMessage;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('.choose-item');
    const importExportToggle = document.getElementById('import-export-toggle');

    items.forEach(item => {
        item.addEventListener('click', function() {
            // Vérifie si l'élément était déjà actif
            const wasActive = this.classList.contains('active');

            // Réinitialiser tous les éléments
            items.forEach(i => i.classList.remove('active'));

            if (!wasActive) {
                // Si l'élément n'était PAS actif, on l'active
                this.classList.add('active');

                // Charger le contenu correspondant
                const category = this.id.replace('choose-', '');
                const type = importExportToggle.checked ? 'export' : 'import';
                loadContent(type, category);
            } else {
                // Si l'élément était déjà actif, on le laisse désélectionné
                // et on cache le contenu
                importContent.style.display = 'none';
                exportContent.style.display = 'none';
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const importExportToggle = document.getElementById('import-export-toggle');
    const importContent = document.getElementById('import-content');
    const exportContent = document.getElementById('export-content');

    // Gestionnaires des catégories
    const studentsBtn = document.getElementById('choose-students');
    const teachersBtn = document.getElementById('choose-teachers');
    const internshipsBtn = document.getElementById('choose-internships');

    // Gestionnaire pour le toggle import/export
    importExportToggle.addEventListener('change', function() {
        if (this.checked) {
            loadContent('export');
        } else {
            loadContent('import');
        }
    });

    // Gestionnaires pour les catégories
    studentsBtn.addEventListener('click', function() {
        const type = importExportToggle.checked ? 'export' : 'import';
        loadContent(type, 'students');
    });

    teachersBtn.addEventListener('click', function() {
        const type = importExportToggle.checked ? 'export' : 'import';
        loadContent(type, 'teachers');
    });

    internshipsBtn.addEventListener('click', function() {
        const type = importExportToggle.checked ? 'export' : 'import';
        loadContent(type, 'internships');
    });

    // Chargement initial
    loadContent('import');
});

const importExportToggle = document.getElementById('import-export-toggle');
const simpleAdvancedToggle = document.getElementById('simple-advanced-toggle');

// Fonction pour mettre à jour le texte du mode
function updateModeText() {
    const modeText = document.getElementById('mode-text');

    if (modeText) {
        const importExportMode = importExportToggle.checked ? 'Exporter' : 'Importer';
        const simpleAdvancedMode = simpleAdvancedToggle.checked ? 'avancé' : 'simple';

        modeText.textContent = `Mode ${simpleAdvancedMode} - ${importExportMode}`;
    }
}

importExportToggle.addEventListener('change', updateModeText);
simpleAdvancedToggle.addEventListener('change', updateModeText);

// Initialiser le texte au chargement
updateModeText();

// Export
// Gestion du toggle d'exportation (liste vs modèle)
function setupExportToggle() {
    const toggle = document.getElementById('export-type-toggle');
    if (!toggle) return;

    const exportField = document.getElementById('export-field');
    const listInfo = document.getElementById('list-info');
    const modelInfo = document.getElementById('model-info');

    // État initial
    if (toggle.checked) {
        exportField.name = 'export_model';
        listInfo.style.display = 'none';
        modelInfo.style.display = 'block';
    } else {
        exportField.name = 'export_list';
        listInfo.style.display = 'block';
        modelInfo.style.display = 'none';
    }

    // Gestion du changement
    toggle.addEventListener('change', function() {
        if (this.checked) {
            // Mode modèle
            exportField.name = 'export_model';
            listInfo.style.display = 'none';
            modelInfo.style.display = 'block';
        } else {
            // Mode liste
            exportField.name = 'export_list';
            listInfo.style.display = 'block';
            modelInfo.style.display = 'none';
        }
    });
}

// Modification de la fonction loadContent pour initialiser les toggles après chargement
async function loadContent(type, category = '') {
    try {
        const response = await fetch(`/api/${type}?category=${category}`);

        if (!response.ok) {
            throw new Error(`Impossible de charger le contenu ${type}, erreur: ${response.status}`);
        }

        const html = await response.text();

        if (type === 'import') {
            importContent.innerHTML = html;
            importContent.style.display = 'block';
            exportContent.style.display = 'none';
        } else {
            exportContent.innerHTML = html;
            exportContent.style.display = 'block';
            importContent.style.display = 'none';
            setupExportToggle(); // Initialiser le toggle d'export après chargement
        }

        updateSelectOptions();
        initTooltips();
    } catch (error) {
        console.error(`Error loading content: ${error.message}`);
        const errorMessage = `<div class="error-message card-panel red lighten-4">
            <p>Une erreur s'est produite lors du chargement du contenu. Veuillez réessayer.</p>
        </div>`;

        if (type === 'import') {
            importContent.innerHTML = errorMessage;
        } else {
            exportContent.innerHTML = errorMessage;
        }
    }
}