<?php
/**
 * Fichier contenant la vue de la page 'Répartiteur'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/dispatcher
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\dispatcher;

use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Teacher;
use Blog\Models\User;

/**
 * Classe gérant l'affichage de la page 'Répartiteur'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/dispatcher
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Dispatcher
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Internship $internshipModel         Instance de la classe Internship
     *                                            servant de modèle
     * @param User       $userModel               Instance de la classe User
     *                                            servant de modèle
     * @param Teacher    $teacherModel            Instance de la classe Teacher
     *                                            servant de modèle
     * @param Department $departmentModel         Instance de la classe Department
     *                                            servant de modèle
     * @param string     $errorMessageAfterSort   Message d'erreur s'affichant quand
     *                                            la répartition n'a pas fonctionné
     * @param string     $errorMessageDirectAssoc Message d'erreur s'affichant quand
     *                                            l'association n'a pas fonctionné
     * @param string     $checkMessageDirectAssoc Message s'affichant quand
     *                                            la répartition a fonctionné
     * @param string     $checkMessageAfterSort   Message s'affichant quand
     *                                            l'association a fonctionné
     */
    public function __construct(
        private Internship $internshipModel,
        private User $userModel,
        private Teacher $teacherModel,
        private Department $departmentModel,
        private string $errorMessageAfterSort,
        private string $errorMessageDirectAssoc,
        private string $checkMessageDirectAssoc,
        private string $checkMessageAfterSort
    ) {
    }

    /**
     * Affiche la page 'Répartiteur'
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <div class="col">
                <h3 class="center-align flow-text">
                    Répartiteur de tuteurs enseignants
                </h3>

                <?php
                if (!isset($_POST['action']) || $_POST['action'] !== 'generate') : ?>
                <div class="row" id="forms-section">
                    <div class="col card-panel white z-depth-3 s10 m5 l5"
                         style="padding: 20px; margin-right: 10px">
                        <form class="col s12" action="./dispatcher"
                              method="post" onsubmit="showLoading();">
                            <?php
                            $saves = $this->userModel
                                ->showCoefficients($_SESSION['identifier']);
                            if ($saves) : ?>
                                <div class="input-field">
                                    <label
                                    for="save-selector"
                                    >Sélectionnez une sauvegarde</label>
                                    <br><br>
                                    <select id="save-selector" name="save-selector">
                                        <option
                                        value="new">Nouvelle Sauvegarde
                                        </option>
                                        <?php foreach ($saves as $save): ?>
                                            <?php $id_backup = $save['id_backup'];
                                                  $name_save = $save['name_save'];?>
                                        <option value="<?php echo $id_backup; ?>"
                                            <?php
                                            if (isset($_POST['save-selector'])) {
                                                $saveSelected
                                                    = $_POST['save-selector'];
                                            }
                                            if (isset($_POST['save-selector'])
                                                && $saveSelected == $id_backup
                                            ) : ?>
                                        selected
                                            <?php endif; ?>
                                        ><?php echo $name_save; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>


                            <?php
                            unset($id_backup);
                            $id_backup = $_POST['save-selector'] ?? 'new';
                            $name_save = '';

                            if ($id_backup === 'new'
                                || $id_backup === 'default'
                                || $id_backup === 'i'
                            ) {
                                $defaultCriteria = $this->
                                userModel->getDefaultCoef();
                                $listCriteria = [];
                                foreach ($defaultCriteria as $key => $value) {
                                    $listCriteria[$key] = $value;
                                }
                                $name_save = 'Nouvelle sauvegarde';
                            } else {
                                $listCriteria = $this->userModel->loadCoefficients(
                                    $_SESSION['identifier'],
                                    $id_backup
                                );
                                $name_save = $listCriteria[0]['name_save'];
                            }
                            ?>

                            <?php foreach ($listCriteria as $criteria):
                                $value = $criteria['coef'];
                                $name = $criteria['name_criteria'];
                                $description = $criteria['description'];
                                ?>
                                <div class="row">
                                    <div class="col s6">
                                        <p>
                                            <label>
                                               <input type="hidden"
                                               name="is_checked[<?php echo $name;?>]"
                                               value="0">
                                                <input type="checkbox"
                                                   class=
                                                   "filled-in criteria-checkbox"
                                                   name=
                                                   "criteria_on[<?php echo $name;?>]"
                                           data-coef-input-id="<?php echo $name; ?>"
                                                   <?php
                                                    if ($criteria['is_checked']
                                                    ) : ?>
                                                   checked="checked"
                                                        <?php
                                                    endif; ?> />
                                                <span class="tooltipped"
                                                data-position="top"
                                                data-tooltip=
                                                  "<?php echo $description ?>">
                                                    <?php echo $name; ?>
                                                </span>
                                            </label>
                                        </p>
                                    </div>
                                    <div class="col s6">
                                        <div class="input-field">
                                            <input type="number" class="coef-input"
                                               name="coef[<?php echo $name; ?>]"
                                               id="<?php echo $name; ?>"
                                               min="1" max="100"
                                               value="<?php echo $value ?>"
                                            />
                                            <label for="<?php echo $name; ?>">Coeff
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="input-field">
                                <input type="text" id="save-name"
                                       name="save-name"
                                       value="<?php echo $name_save ?>">
                                <label for="save-name">Nom de la sauvegarde</label>
                            </div>

                            <p class="red-text" id="checkboxError"><?php
                                echo $this->errorMessageAfterSort; ?></p>
                            <p class="green-text"><?php
                                echo $this->checkMessageAfterSort; ?></p>

                            <?php
                            if ($id_backup == 'new') {
                                $tooltip = "Créer la sauvegarde";
                                $btnValue = "Créer";
                            } else {
                                $tooltip = "Enregistrer la sauvegarde";
                                $btnValue = "Enregistrer";
                            }
                            ?>
                            <div class="row">
                                <div class="col s12 m8 l8">
                                    <div class="flex-container">
                                        <button class="btn waves-effect
                                        waves-light button-margin tooltipped red"
                                                type="submit" name="action-delete"
                                                value="<?php echo $id_backup ?>"
                                                id="delete-btn"
                                                data-position="top"
                                                data-tooltip="
                                                Supprimer la sauvegarde">
                                            Supprimer
                                            <i class="material-icons right"
                                            >delete</i>
                                        </button>
                                        <button class="btn waves-effect
                                        waves-light button-margin tooltipped"
                                                type="submit" name="action-save"
                                                value="<?php echo $id_backup ?>"
                                                id="save-btn"
                                                data-position="top"
                                                data-tooltip=
                                                <?php echo $tooltip ?>>
                                            <?php echo $btnValue ?>
                                            <i class=
                                            "material-icons right">arrow_downward</i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col s12">
                                    <div>
                                        <button class="btn waves-effect
                                        waves-light button-margin tooltipped"
                                                type="submit" name="action"
                                                value="generate" id="generate-btn"
                                                data-position="top"
                                                data-tooltip=
                                                "Commencer la répartition">
                                            Générer
                                            <i class="material-icons right">send</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <form class="col card-panel white z-depth-3 s10 m5 l5"
                          style="padding: 20px;" action="./dispatcher"
                          method="post" id="associate-form">
                        <div class="row">
                            <p class="text">Associe un professeur à un stage
                                (ne prend pas en compte le nombre maximum d'étudiant,
                                mais le fait que le stage soit déjà attribué)</p>
                            <div class="input-field col s6">
                                <input id="searchTeacher" name="searchTeacher"
                                   type="text" class="validate">
                                <label for="searchTeacher">ID professeur</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="searchInternship" name="searchInternship"
                                   type="text" class="validate">
                                <label for="searchInternship">ID Stage</label>
                            </div>
                            <div id="searchResults"></div>
                            <p class="red-text">
                                <?php echo $this->errorMessageDirectAssoc; ?>
                            </p>
                            <p class="green-text">
                                <?php echo $this->checkMessageDirectAssoc; ?>
                            </p>
                            <div class="col s12">
                                <button class=
                                        "btn waves-effect
                                        waves-light button-margin tooltipped"
                                        type="submit" name="action"
                                        data-position="top"
                                        data-tooltip="Valider l'association">
                                    Associer
                                    <i
                                    class="material-icons right">arrow_downward</i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="loading-section" class="center-align"
                     style="display: none;">
                    <p>
                        Chargement en cours, veuillez patienter...
                    </p>
                    <div class="progress">
                        <div class="indeterminate"></div>
                    </div>
                </div>
                <?php endif;

                if (isset($_POST['coef']) && isset($_POST['action'])
                    && $_POST['action'] === 'generate'
                ) : ?>
                    <div id="map"></div>
                    <div class="row"></div>

                    <form action="./dispatcher" method="post">
                        <div class=
                             "dispatch-table-wrapper selection table-container">
                            <table class
                                   ="highlight centered"
                                id="dispatch-table">
                                <thead class="clickable">
                                <tr>
                                    <th>Enseignant</th>
                                    <th>Etudiant</th>
                                    <th>Stage</th>
                                    <th>Formation</th>
                                    <th>Groupe</th>
                                    <th>Sujet</th>
                                    <th>Adresse</th>
                                    <th>Score</th>
                                    <th>Associer</th>
                                </tr>
                                </thead>
                                <tbody>
                        <?php
                        $dictCoef = array_filter(
                            $_POST['coef'], function ($coef, $key) {
                                return isset($_POST['criteria_on'][$key]);
                            }, ARRAY_FILTER_USE_BOTH
                        );

                        if (!empty($dictCoef)) {
                            $escapedJson = htmlspecialchars(
                                json_encode($dictCoef),
                                ENT_QUOTES
                            );

                            echo "<input type='hidden' id='dictCoefJson' value='"
                                . $escapedJson . "'>";
                        } else {
                            header('location: ./dispatcher');
                        }

                        $resultDispatchList = $this->internshipModel
                            ->dispatcher(
                                $this->departmentModel,
                                $this->teacherModel,
                                $dictCoef
                            )[0];
                        foreach ($resultDispatchList as $resultDispatch):
                            $internship = $resultDispatch['internship_identifier'];
                            $id_teacher = $resultDispatch['id_teacher'];
                            $address = $resultDispatch['address'];
                            $score = $resultDispatch['score'];

                            ?>
                            <tr class="dispatch-row"
                                data-internship-identifier=
                                "<?php
                                echo $internship . '$'. $id_teacher . '$' . $address;
                                ?>">
                                <td>
                                    <?php echo $resultDispatch['teacher_firstname'] .
                                    ' ' . $resultDispatch['teacher_name'] . ' (' .
                                    $resultDispatch['id_teacher'] . ')'; ?>
                                </td>
                                <td>
                                    <?php echo $resultDispatch['student_firstname'] .
                                        ' ' . $resultDispatch['student_name'] . ' ('
                                        . $resultDispatch['student_number'] . ')'; ?>
                                    <br>
                                    <i class="material-icons clickable tooltipped"
                                       data-position="top"
                                       data-tooltip=
                                       "Afficher la vue étudiante ">face</i>
                                </td>
                                <td>
                                    <?php echo $resultDispatch['company_name'] . ' ('
                                        . $resultDispatch['internship_identifier']
                                        . ')'; ?>
                                    <br>
                                    <i class="material-icons clickable tooltipped"
                                       data-position="top"
                                       data-tooltip=
                                       "Voir la position de l'entreprise">map</i>
                                </td>

                                <td>
                                    <?php echo $resultDispatch['formation']; ?>
                                </td>
                                <td>
                                    <?php echo $resultDispatch['class_group']; ?>
                                </td>
                                <td>
                                    <?php echo $resultDispatch
                                    ['internship_subject']; ?>
                                </td>
                                <td>
                                    <?php echo $resultDispatch['address']; ?>
                                </td>
                                <td>
                                    <div class="star-rating" data-tooltip="
                                    <?php echo $resultDispatch['score']; ?>
                                    " data-position="top">
                                        <?php
                                        echo $this
                                            ->renderStars($resultDispatch['score']);
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <p>
                                        <label class="center">
                                            <input type="checkbox" class=
                                           "dispatch-checkbox center-align filled-in"
                                            id="listTupleAssociate[]"
                                            name="listTupleAssociate[]" value="<?php
                                            echo
                                            $id_teacher."$".$internship."$".$score;
                                            ?>" />
                                            <span data-type="checkbox">Cocher</span>
                                        </label>
                                    </p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                                </tbody>
                            </table>

                            <br>

                            <div class="row">
                                <div class="input-field col s2">
                                    <select id="rows-per-page">
                                        <option value="10" selected>10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="
                                        <?php echo count($resultDispatchList); ?>
                                        ">Tout</option>
                                    </select>
                                    <label
                                    for="rows-per-page"
                                    >Nombre de lignes par page</label>
                                </div>
                            </div>

                            <div id="pagination-controls" class="center-align">
                                <button type="button"
                                        class="waves-effect waves-light btn"
                                        id="first-page">
                                    <i class="material-icons">first_page</i>
                                </button>
                                <button type="button"
                                        class="waves-effect waves-light btn"
                                        id="prev-page">
                                    <i class="material-icons">arrow_back</i>
                                </button>
                                <div id="page-numbers"></div>
                                <button type="button"
                                        class="waves-effect waves-light btn"
                                        id="next-page">
                                    <i class="material-icons">arrow_forward</i>
                                </button>
                                <button type="button"
                                        class="waves-effect waves-light btn"
                                        id="last-page">
                                    <i class="material-icons">last_page</i>
                                </button>
                            </div>


                            <div>
                                <button class="btn waves-effect
                                waves-light button-margin tooltipped"
                                        type="submit" name="action-save"
                                        value="<?php echo $id_backup ?>"
                                        id="save-btn"
                                        data-position="top"
                                        data-tooltip="Enregistrer la sauvegarde">
                                    Enregister
                                    <i class="material-icons
                                    right">arrow_downward</i>
                                </button>
                                <button class="btn waves-effect
                                waves-light button-margin tooltipped"
                                        type="submit" name="action"
                                        value="generate" id="generate-btn"
                                        data-position="top"
                                        data-tooltip="Commencer la répartition">
                                    Générer
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>
                            <br>
                            <br>

                        </div>
                    </form>
                <?php endif; ?>

            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var tooltips = document.querySelectorAll('.tooltipped');
                    M.Tooltip.init(tooltips);
                })
            </script>
        </main>
        <?php
    }

    /**
     * Renvoie les éléments HTML correspondant à l'affichage
     * en étoiles du score passé en paramètre
     *
     * @param float $score Score de pertinence que
     *                     l'on veut convertir en étoiles
     *
     * @return string Chaîne de caractères contenant
     * les étoiles correspondant au score
     */
    function renderStars(float $score): string
    {
        $fullStars = floor($score);

        $decimalPart = $score - $fullStars;

        $halfStars = ($decimalPart > 0 && $decimalPart < 1) ? 1 : 0;

        $emptyStars = 5 - $fullStars - $halfStars;

        $stars = '';

        for ($i = 0; $i < $fullStars; $i++) {
            $stars .= '<span class="filled"></span>';
        }

        if ($halfStars) {
            $stars .= '<span class="half"></span>';
        }

        for ($i = 0; $i < $emptyStars; $i++) {
            $stars .= '<span class="empty"></span>';
        }

        return $stars;
    }
}