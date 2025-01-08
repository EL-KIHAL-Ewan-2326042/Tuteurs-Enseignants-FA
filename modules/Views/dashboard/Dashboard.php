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
readonly class Dashboard
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
            <h3> Gestion des données </h3>
            <div class="card-panel white">
                <div class="tooltip-container" data-tooltip=
                    "1. Exporter le modèle correspondant <br>
                     2. Inscrivez vos données <br>
                     3. Vérifiez que le séparateur est bien un point-virgule <br>
                     4. Importez le fichier csv ci-dessous">(?)</div>
                <h4>Importer :</h4>

                <!--Importation de nouveaux étudiants-->
                <div class="row">
                    <h5> Rajouter des étudiants : </h5>
                    <form action="/dashboard" method="POST"
                          enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="student">
                        <div class="file-field input-field">
                            <div class="btn">
                                <span>Fichier CSV</span>
                                <input type="file" name="student" id="student"
                                   accept=".csv" multiple>
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text"
                                   placeholder="Choisissez un fichier CSV" required>
                            </div>
                            <button class="btn waves-effect waves-light"
                                type="submit" name="submit_student">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>

                <!--Importation de nouveaux professeurs-->
                <div class="row">
                    <h5> Rajouter des professeurs : </h5>
                    <form action="/dashboard" method="POST"
                        enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="teacher">
                        <div class="file-field input-field">
                            <div class="btn">
                                <span>Fichier CSV</span>
                                <input type="file" name="teacher"
                                    id="teacher" accept=".csv" multiple>
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text"
                                    placeholder="Choisissez un fichier CSV" required>
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
                    <h5> Rajouter des stages : </h5>
                    <form action="/dashboard" method="POST"
                        enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="internship">
                        <div class="file-field input-field">
                            <div class="btn">
                                <span>Fichier CSV</span>
                                <input type="file" name="internship"
                                    id="internship" accept=".csv" multiple>
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text"
                                    placeholder="Choisissez un fichier CSV" required>
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

            <!--Exportation-->
            <div class="export">
                <div class="card-panel white">
                    <!--Exportation des listes : Etudiants/Professeurs/Stages-->
                    <div class="tooltip-container" data-tooltip=
                        "Exportation des données d'une liste
                        choisie dans un fichier .csv">(?)</div>
                    <h4>Exporter une liste :</h4>
                    <form action="/dashboard" method="POST">
                        <div>
                            <select name="export_list" required>
                                <option value="" disabled selected>
                                    Choisissez la liste à exporter
                                </option>
                                <option value="student">Etudiants</option>
                                <option value="teacher">Professeurs</option>
                                <option value="internship">Stages</option>
                            </select>
                            <label>Choisissez la liste à exporter</label>
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
                    <h4>Exporter un modèle:</h4>
                    <form action="/dashboard" method="POST">
                        <div>
                            <select name="export_model" required>
                                <option value="" disabled selected>
                                    Choisissez le modèle à exporter
                                </option>
                                <option value="student">Etudiants</option>
                                <option value="teacher">Professeurs</option>
                                <option value="internship">Stages</option>
                            </select>
                            <label>Choisissez la liste à exporter</label>
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
        </main>
        <?php
    }
}