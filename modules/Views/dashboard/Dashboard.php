<?php
/**
 * Fichier contenant la vue de la page 'Gestion des données'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/dashboard
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\dashboard;

/**
 * Classe gérant l'affichage de la page 'Gestion des données'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/dashboard
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Dashboard
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param string $message      Message s'affichant quand l'import
     *                             de données a fonctionné
     * @param string $errorMessage Message d'erreur s'affichant quand
     *                             l'import de données n'a pas fonctionné
     */
    public function __construct(private string $message,
        private string                         $errorMessage
    ) {
    }

    /**
     * Vue de la page de Gestion des données
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <h1 class="center-align flow-text"> Gestion des données </h1>

            <div id="mode-title" class="center-align">
                <span class="tooltip-container" data-tooltip=
                "1. Exporter le modèle correspondant <br>
                 2. Inscrivez vos données <br>
                 3. Vérifiez que le séparateur est bien une virgule (avec un éditeur de texte) <br>
                 4. Vérifiez qu'un texte comportant une virgule soit encadré par
                 des guillemets (avec un éditeur de texte) <br>
                 5. Importez le fichier csv ci-dessous">(?)</span>
                <span id="mode-text">Mode avancé - Importer</span>
            </div>

            <!-- Toggles switches -->
            <div class="toggles-container">
                <div class="switch-container">
                    <div class="switch">
                        <label>
                            Import
                            <input type="checkbox" id="import-export-toggle">
                            <span class="lever"></span>
                            Export
                        </label>
                    </div>
                </div>
                <div class="switch-container">
                    <div class="switch">
                        <label>
                            Simple
                            <input type="checkbox" id="simple-advanced-toggle"
                                   checked
                                   onclick="return false;">
                            <span class="lever"></span>
                            Avancé
                        </label>
                    </div>
                </div>
            </div>

            <div class="choose">
                <div class="choose-item" id="choose-students">
                    <div class="icon-circle">
                        <i class="material-icons main-icon">school</i>
                        <i class="material-icons plus-icon">add</i>
                    </div>
                    <label>Étudiants</label>
                </div>

                <div class="choose-item" id="choose-teachers">
                    <div class="icon-circle">
                        <i class="material-icons main-icon">supervisor_account</i>
                        <i class="material-icons plus-icon">add</i>
                    </div>
                    <label>Enseignants</label>
                </div>

                <div class="choose-item" id="choose-internships">
                    <div class="icon-circle">
                        <i class="material-icons main-icon">work</i>
                        <i class="material-icons plus-icon">add</i>
                    </div>
                    <label>Stages</label>
                </div>
            </div>

            <!-- Sections pour le contenu dynamique -->
            <div id="content-container">
                <div id="import-content" class="content-section">

                </div>

                <div id="export-content" class="content-section" style="display:none;">

                </div>
            </div>
            <form class="col card-panel white z-depth-3 s10 m5 l5"
                  style="padding: 20px;" action="./dispatcher"
                  method="post" id="associate-form">
                <div class="row">
                    <p class="text">Associe un enseignant à un stage
                        (ne prend pas en compte le nombre maximum d'étudiant,
                        mais le fait que le stage soit déjà attribué)</p>
                    <div class="input-field col s6">
                        <input id="searchTeacher" name="searchTeacher"
                               type="text" class="validate">
                        <label for="searchTeacher">Enseignant</label>
                    </div>
                    <div class="input-field col s6">
                        <input id="searchInternship" name="searchInternship"
                               type="text" class="validate">
                        <label for="searchInternship">Stage</label>
                    </div>
                    <div id="searchResults"></div>
                    <div class="col s12">
                        <button
                                type="submit" name="action"
                                data-position="top"
                                data-tooltip="Valider l'association">
                            Associer
                            <i class=
                               "material-icons right">arrow_downward</i>
                        </button>
                    </div>
                </div>
            </form>
            <p class="message"><?php echo $this->message; ?></p>
            <p class="errorMessage"><?php echo $this->errorMessage; ?></p>

        </main>
        <?php
    }
}