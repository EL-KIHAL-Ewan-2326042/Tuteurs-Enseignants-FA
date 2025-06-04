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
        
        // Vérifier si l'URL contient déjà des paramètres pour précharger le bon contenu
        checkUrlAndLoadContent();
    }
);

// Vérifie l'URL au chargement pour déterminer le contenu à afficher
function checkUrlAndLoadContent() {
    const pathParts = window.location.pathname.split('/');
    // La catégorie est la dernière partie de l'URL (après le dernier /)
    const category = pathParts.length > 2 ? pathParts[pathParts.length - 1] : '';
    
    // Si l'URL est simplement /dashboard/xxx, category sera xxx
    if (category && category !== 'dashboard') {
        const urlParams = new URLSearchParams(window.location.search);
        // Par défaut, type est 'import' pour les catégories normales, 'association' pour association
        const type = category === 'association' ? 'association' : (urlParams.get('type') || 'import');
        
        // Met à jour le toggle selon le type (sauf pour association)
        const importExportToggle = document.getElementById('import-export-toggle');
        if (importExportToggle && category !== 'association') {
            importExportToggle.checked = (type === 'export');
        }
        
        // Active le bouton de la catégorie
        const categoryButton = document.getElementById(`choose-${category}`);
        if (categoryButton) {
            // Désactive tous les boutons d'abord
            document.querySelectorAll('.choose-item').forEach(item => item.classList.remove('active'));
            // Active celui-ci
            categoryButton.classList.add('active');
            
            // Charge le contenu approprié
            loadContent(type, category);
        }
    }
}

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

// Fonction pour mettre à jour l'URL dynamiquement
function updateUrl(type, category) {
    let newUrl = '/dashboard';
    
    if (category) {
        // Si c'est l'association, on ne met pas de type dans l'URL
        if (category === 'association') {
            newUrl = `/dashboard/${category}`;
        } else {
            // Pour les autres catégories, on ajoute la catégorie et le type comme paramètre
            newUrl = `/dashboard/${category}`;
            if (type) {
                newUrl += `?type=${type}`;
            }
        }
    }
    
    history.pushState({type, category}, '', newUrl);
    return newUrl;
}

// Fonction pour charger le contenu
async function loadContent(type, category = '') {
    try {
        // Si la catégorie est vide, afficher seulement un message de sélection
        if (!category) {
            const targetContent = type === 'export' ? exportContent : importContent;
            targetContent.innerHTML = '<div class="center-align" style="padding: 20px;"><p>Veuillez sélectionner une catégorie</p></div>';
            
            // Mise à jour de l'URL pour refléter l'absence de catégorie
            updateUrl('', '');
            
            // Mise à jour du texte du mode
            updateModeText('');
            return;
        }
        
        // Mise à jour de l'URL dans le navigateur
        updateUrl(type, category);
        
        // Mise à jour du texte du mode
        updateModeText(category);
        
        // Afficher un indicateur de chargement
        const targetContent = type === 'export' ? exportContent : importContent;
        targetContent.innerHTML = '<div class="center-align" style="padding: 20px;"><div class="preloader-wrapper active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div><p>Chargement...</p></div>';
        
        let url = `/api/${type}`;
        if (category) {
            url += `?category=${category}`;
        }
        
        console.log(`Chargement de l'URL: ${url}`); // Débogage
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Impossible de charger le contenu ${type}, erreur: ${response.status}`);
        }

        const html = await response.text();

        if (type === 'import') {
            importContent.innerHTML = html;
            importContent.style.display = 'block';
            exportContent.style.display = 'none';
        } else if (type === 'export') {
            exportContent.innerHTML = html;
            exportContent.style.display = 'block';
            importContent.style.display = 'none';
            setupExportToggle(); // Initialiser le toggle d'export après chargement
        } else if (type === 'association') {
            importContent.innerHTML = html;
            importContent.style.display = 'block';
            exportContent.style.display = 'none';
        }

        // Initialisation des composants sans vérification préalable
        setTimeout(function() {
            // Réinitialiser les selects
            var elems = document.querySelectorAll('select');
            if (elems.length > 0) {
                M.FormSelect.init(elems);
            }
            
            updateSelectOptions();
            initTooltips();
        }, 100);
    } catch (error) {
        console.error(`Error loading content: ${error.message}`);
        const errorMessage = `<div class="error-message card-panel red lighten-4" style="margin: 20px;">
            <p>Une erreur s'est produite lors du chargement du contenu. Veuillez réessayer.</p>
            <p class="error-details">${error.message}</p>
        </div>`;

        if (type === 'import' || type === 'association') {
            importContent.innerHTML = errorMessage;
        } else {
            exportContent.innerHTML = errorMessage;
        }
    }
}

// Remplace les gestionnaires d'événements multiples par une seule fonction
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const importExportToggle = document.getElementById('import-export-toggle');
    const items = document.querySelectorAll('.choose-item');

    // Gestionnaire pour le toggle import/export
    importExportToggle.addEventListener('change', function() {
        const type = this.checked ? 'export' : 'import';
        
        // Récupérer la catégorie actuellement active, si elle existe
        const activeItem = document.querySelector('.choose-item.active');
        const category = activeItem ? activeItem.id.replace('choose-', '') : '';
        
        if (category && category !== 'association') {
            loadContent(type, category);
            updateUrl(type, category);
        }
    });

    // Gestionnaire pour tous les boutons de catégorie
    items.forEach(item => {
        item.addEventListener('click', function() {
            const category = this.id.replace('choose-', '');
            const isAssociation = category === 'association';
            const type = isAssociation ? 'association' : (importExportToggle.checked ? 'export' : 'import');
            
            // Vérifier si le bouton est déjà actif
            const wasActive = this.classList.contains('active');
            
            // Réinitialiser tous les boutons
            items.forEach(btn => btn.classList.remove('active'));
            
            if (!wasActive) {
                // Activer ce bouton et charger le contenu
                this.classList.add('active');
                loadContent(type, category);
            } else {
                // Désactiver ce bouton (déselection) et vider le contenu
                // Revenir à l'URL de base /dashboard
                updateUrl('', '');
                
                const targetContent = type === 'export' ? exportContent : importContent;
                targetContent.innerHTML = '<div class="center-align" style="padding: 20px;"><p>Veuillez sélectionner une catégorie</p></div>';
            }
        });
    });

    // Support de la navigation avec les boutons précédent/suivant du navigateur
    window.addEventListener('popstate', function(event) {
        if (event.state) {
            const { type, category } = event.state;
            
            // Met à jour le toggle selon le type (sauf pour association)
            if (importExportToggle && category !== 'association') {
                importExportToggle.checked = (type === 'export');
            }
            
            // Met à jour l'élément actif
            document.querySelectorAll('.choose-item').forEach(item => item.classList.remove('active'));
            if (category) {
                const categoryButton = document.getElementById(`choose-${category}`);
                if (categoryButton) {
                    categoryButton.classList.add('active');
                    loadContent(type, category);
                }
            } else {
                // Affiche un contenu vide
                const targetContent = type === 'export' ? exportContent : importContent;
                targetContent.innerHTML = '<div class="center-align" style="padding: 20px;"><p>Veuillez sélectionner une catégorie</p></div>';
            }
        }
    });
});

// Supprimer les gestionnaires d'événements dupliqués
// document.addEventListener('DOMContentLoaded', () => { ... });
// document.addEventListener('DOMContentLoaded', function() { ... });

const importExportToggle = document.getElementById('import-export-toggle');
const simpleAdvancedToggle = document.getElementById('simple-advanced-toggle');

// Fonction pour mettre à jour le texte du mode
function updateModeText(currentCategory) {
    const modeText = document.getElementById('mode-text');

    if (modeText) {
        // Si la catégorie est passée en paramètre, l'utiliser
        // Sinon, essayer de la détecter à partir de l'élément actif
        let category = currentCategory;
        if (!category) {
            const activeItem = document.querySelector('.choose-item.active');
            if (activeItem) {
                category = activeItem.id.replace('choose-', '');
            }
        }

        // Si c'est association, afficher un texte spécifique
        if (category === 'association') {
            modeText.textContent = 'Mode avancé - Association';
        } else {
            const importExportMode = importExportToggle.checked ? 'Exporter' : 'Importer';
            const simpleAdvancedMode = simpleAdvancedToggle.checked ? 'avancé' : 'simple';
            modeText.textContent = `Mode ${simpleAdvancedMode} - ${importExportMode}`;
        }
    }
}

importExportToggle.addEventListener('change', function() {
    // Récupérer la catégorie active pour la passer à updateModeText
    const activeItem = document.querySelector('.choose-item.active');
    const category = activeItem ? activeItem.id.replace('choose-', '') : '';
    updateModeText(category);
});

simpleAdvancedToggle.addEventListener('change', function() {
    const activeItem = document.querySelector('.choose-item.active');
    const category = activeItem ? activeItem.id.replace('choose-', '') : '';
    updateModeText(category);
});

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
