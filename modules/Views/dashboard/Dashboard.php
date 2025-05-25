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
                            <input type="checkbox" id="simple-advanced-toggle">
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

            <!-- Sections -->
            <section id="students-section">Contenu des étudiants</section>
            <section id="teachers-section">Contenu des enseignants</section>
            <section id="internships-section">Contenu des stages</section>

            <!--Exportation-->
            <div class="export">
                <div class="card-panel white">
                    <!--Exportation des listes : Etudiants/enseignants/Stages-->
                    <div class="tooltip-container" data-tooltip=
                    "Exportation des données d'une liste
                        choisie dans un fichier .csv">(?)</div>
                    <h2>Exporter une liste :</h2>
                    <form action="/dashboard" method="POST">
                        <div>
                            <label>
                                <span>Choisissez la liste à exporter</span>

                                <select name="export_list" required>
                                    <option value="" disabled
                                            selected>Choisir
                                    </option>
                                    <option value="student">Etudiants</option>
                                    <option value="teacher">Enseignants</option>
                                    <option value="internship">Stages</option>
                                </select>
                            </label>
                        </div>
                        <div class="input-field">
                            <button class="btn waves-effect waves-light"
                                    type="submit">Exporter
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>

                <!--Exportation des modèles des tables-->
                <div class="card-panel white">
                    <div class="tooltip-container" data-tooltip=
                    "Exportation d'un modèle d'une liste choisie dans un fichier
                        .csv (Pour avoir seulement le nom des colonnes)">(?)</div>
                    <h2>Exporter un modèle:</h2>
                    <form action="/dashboard" method="POST">
                        <div>
                            <label>
                                <span>Choisissez le modèle à exporter</span>
                                <select name="export_model" required>
                                    <option
                                        disabled selected>Choisir
                                    </option>
                                    <option value="student">Etudiants</option>
                                    <option value="teacher">Enseignants</option>
                                    <option value="internship">Stages</option>
                                </select>
                            </label>
                        </div>
                        <div class="input-field">
                            <button class="btn waves-effect waves-light"
                                    type="submit">Exporter
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-panel white">
                <div class="tooltip-container" data-tooltip=
                    "1. Exporter le modèle correspondant <br>
                     2. Inscrivez vos données <br>
                     3. Vérifiez que le séparateur est
                     bien une virgule (avec un éditeur de texte) <br>
                     4. Vérifiez qu'un texte comportant une
                     virgule soit encadrer par
                     des guillemets (avec un éditeur de texte) <br>
                     5. Importez le fichier csv ci-dessous">(?)</div>
                <h2>Importer :</h2>

                <!--Importation de nouveaux étudiants-->
                <div class="row">
                    <h3> Rajouter des étudiants : </h3>
                    <form action="/dashboard" method="POST"
                          enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="student">
                        <div class="file-field input-field">
                            <div class="btn">
                                <label for="student" style="cursor: pointer;">
                                    <span>Fichier CSV</span>
                                </label>
                                <input type="file" name="student"
                                       id="student" accept=".csv"
                                       multiple style="display: none;">
                            </div>

                            <div class="file-path-wrapper">
                                <label>
                                    <input class="file-path validate" type="text"
                                           placeholder="Choisir un fichier" required>
                                    <span>Format CSV</span>
                                </label>
                            </div>
                            <button class="btn waves-effect waves-light"
                                type="submit" name="submit_student">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>

                <!--Importation de nouveaux enseignants-->
                <div class="row">
                    <h3> Rajouter des enseignants : </h3>
                    <form action="/dashboard" method="POST"
                        enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="teacher">
                        <div class="file-field input-field">
                            <div class="btn">
                                <label for="teacher" style="cursor: pointer;">
                                    <span>Fichier CSV</span>
                                </label>
                                <input type="file"
                                       name="teacher"
                                       id="teacher"
                                       accept=".csv"
                                       multiple style="display: none;">
                            </div>
                            <div class="file-path-wrapper">
                                <label>
                                    <input class="file-path validate" type="text"
                                        placeholder="Choisir un fichier" required>
                                    <span>Format CSV</span>
                                </label>
                            </div>
                            <button class="btn waves-effect waves-light"
                                type="submit" name="submit_teacher">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>

                <!--Importation de nouveaux stages-->
                <div class="row">
                    <h3> Rajouter des stages : </h3>
                    <form action="/dashboard" method="POST"
                        enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="internship">
                        <div class="file-field input-field">
                            <div class="btn">
                                <label for="internship" style="cursor: pointer;">
                                    <span>Fichier CSV</span>
                                </label>
                                <input type="file"
                                       name="internship"
                                       id="internship"
                                       accept=".csv"
                                       multiple style="display: none;">
                            </div>
                            <div class="file-path-wrapper">
                                <label>
                                    <input class="file-path validate" type="text"
                                        placeholder="Choisir un fichier"
                                           required>
                                    <span>Format CSV</span>
                                </label>
                            </div>
                            <button class="btn waves-effect waves-light"
                                type="submit" name="submit_internship">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>

                <p class="message"><?php echo $this->message; ?></p>
                <p class="errorMessage"><?php echo $this->errorMessage?></p>

            </div>
        </main>
        <?php
    }
}