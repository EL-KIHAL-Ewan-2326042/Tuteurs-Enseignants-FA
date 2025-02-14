<?php
/**
 * Fichier contenant la vue de la page 'Accueil'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/homepage
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\homepage;

use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Student;
use Blog\Models\Teacher;

/**
 * Classe gérant l'affichage de la page 'Accueil'
 *
 * @category View
 * @package  TutorMap/modules/Views/homepage
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
readonly class Homepage
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Internship $internshipModel Instance de la classe Internship
     *                                    servant de modèle
     * @param Student    $studentModel    Instance de la classe Student
     *                                    servant de modèle
     * @param Teacher    $teacherModel    Instance de la classe Teacher
     *                                    servant de modèle
     * @param Department $departmentModel Instance de la classe Department
     *                                    servant de modèle
     */
    public function __construct(private Internship $internshipModel,
        private Student $studentModel,
        private Teacher $teacherModel,
        private Department $departmentModel
    ) {
    }

    /**
     * Affiche la page 'Accueil'
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <h1 class="center-align
            flow-text">Répartiteur de tuteurs enseignants</h1>

            <div class="card-panel white">
                <label ="Numéro Etudiant"
                <form class="col" id="searchForm"
                      onsubmit="return false;" method="POST">
                    <div class="inline">
                        <div class="searchCell">
                            <label for="searchType">Type de recherche:</label>
                            <div class="input-field">
                                <select id="searchType" name="searchType"
                                        aria-label="Type de recherche">
                                    <option value="studentNumber"
                                            selected>Numéro Etudiant</option>
                                    <option value="name">Nom et Prénom</option>
                                    <option value="company">Entreprise</option>
                                </select>
                            </div>
                        </div>

                        <div class="searchCell">
                            <label for="search">Rechercher:</label>
                            <input type="text" id="search" name="search"
                                   autocomplete="off" maxlength="50" required
                                   aria-label="Rechercher"/>
                        </div>
                    </div>

                    <div class="searchCell">
                        <p>Stage(s) et alternance(s):</p>
                        <div id="searchResults" role="region" aria-live="polite"
                             aria-label="Résultats de recherche"></div>
                    </div>
                </form>
            </div>

            <div class="center">
                <?php
                if (isset($_POST['cancelSearch'])) {
                    unset($_SESSION['selected_student']);
                }

                if (isset($_POST['searchedStudentSubmitted'])) {

                    $update = $this->internshipModel
                        ->updateSearchedStudentInternship(
                            isset($_POST['searchedStudent']),
                            $_SESSION['identifier'],
                            $_POST['searchedStudentSubmitted']
                        );

                    if (!$update || gettype($update) !== 'boolean') {
                        echo '<h6 class="red-text">Une erreur est survenue</h6>';
                    }
                }

                if (isset($_POST['selecInternshipSubmitted'])) {
                    $update = $this->internshipModel->updateRequests(
                        $_POST['selecInternship'] ?? array(),
                        $_SESSION['identifier']
                    );

                    if (!$update || gettype($update) !== 'boolean') {
                        echo '<h6 class="red-text">Une erreur est survenue</h6>';
                    } else {
                        unset(
                            $_SESSION['unconfirmed'],
                            $_SESSION['lastPage'], $_POST['page']
                        );
                    }
                }
                ?>
                <div id="map"></div>
                <?php
                if (isset($_SESSION['selected_student']['firstName'])
                    && isset($_SESSION['selected_student']['lastName'])
                ) {
                    echo '<h2 class="left-align"> Résultat pour: ' .
                        $_SESSION['selected_student']['firstName'] . ' ' .
                        $_SESSION['selected_student']['lastName'] . '</h2>';
                    if (!isset($_SESSION['selected_student']['address'])
                        || $_SESSION['selected_student']['address'] === ''
                    ) {
                        echo "<p>Cet étudiant n'a pas de stage ...</p>";
                    } else {
                        $internshipInfos = $this->internshipModel
                            ->getInternshipStudent(
                                $_SESSION['selected_student']['id']
                            );
                        if ($internshipInfos) {
                            $internships = $this->internshipModel->getInternships(
                                $_SESSION['selected_student']['id']
                            );
                            $year = "";
                            $nbInternships = $this->internshipModel
                                ->getInternshipTeacher(
                                    $internships, $_SESSION['identifier'], $year
                                );
                            $distance = $this->internshipModel->getDistance(
                                $internshipInfos['internship_identifier'],
                                $_SESSION['identifier'],
                                isset($internshipInfos['id_teacher'])
                            );
                            $inDep = false;
                            foreach ($this->studentModel->getDepStudent(
                                $_SESSION['selected_student']['id']
                            ) as $dep
                            ) {
                                if (in_array(
                                    $dep, $this->teacherModel
                                        ->getDepTeacher($_SESSION['identifier'])
                                )
                                ) {
                                    $inDep = true;
                                    break;
                                }
                            }
                            if (!$internshipInfos['id_teacher'] && $inDep) {
                                echo '<form method="post" class="center-align">';
                            } else {
                                echo '<div class="center-align">';
                            }
                            ?>
                                <table class="highlight centered" id="search-table">
                                    <thead>
                                    <tr>
                                        <th>FORMATION</th>
                                        <th>GROUPE</th>
                                        <th>HISTORIQUE</th>
                                        <th>ENTREPRISE</th>
                                        <th>SUJET</th>
                                        <th>ADRESSE</th>
                                        <th>
                                            <div class="tooltip-container tooltip"
                                                data-tooltip="Durée moyenne vous
                                                séparant du lieu du stage"
                                                data-position="top">(?)</div>
                                            POSITION
                                        </th>
                                        <th>
                                            <div class="tooltip-container tooltip"
                                                data-tooltip="Voeux formulés"
                                                data-position="top">(?)</div>
                                            CHOIX
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php
                                                echo str_replace(
                                                    '_', ' ',
                                                    $internshipInfos['formation']
                                                ) ?></td>
                                            <td><?php
                                                echo str_replace(
                                                    '_', ' ',
                                                    $internshipInfos['class_group']
                                                ) ?></td>
                                            <td>
                                                <?php echo $nbInternships > 0
                                                    ? $year : 'Non' ?>
                                            </td>
                                            <td><?php
                                                echo str_replace(
                                                    '_', ' ',
                                                    $internshipInfos["company_name"]
                                                ) ?></td>
                                            <td><?php
                                                echo str_replace(
                                                    '_', ' ',
                                                    $internshipInfos[
                                                    "internship_subject"
                                                    ]
                                                ) ?></td>
                                            <td><?php
                                                echo str_replace(
                                                    '_', "'",
                                                    $internshipInfos['address']
                                                ) ?></td>
                                            <td>~<?php
                                                echo $distance . " minute"
                                                    . ($distance > 1 ? "s" : "")
                                                ?></td>
                                            <td>
                            <?php
                            if (!$inDep) {
                                echo "<strong>" .
                                $_SESSION['selected_student']['firstName']. ' ' .
                                $_SESSION['selected_student']['lastName'] .
                                "</strong> ne fait partie d'aucun " .
                                "de vos départements";
                            } else {
                                $id_teacher = $internshipInfos['id_teacher'];
                                if ($id_teacher) {
                                    if ($id_teacher === $_SESSION['identifier']
                                    ) {
                                        echo "Vous êtes déjà le tuteur de " .
                                        "<strong>" . $_SESSION['selected_student']
                                        ['firstName'] . ' ' .
                                        $_SESSION['selected_student']
                                        ['lastName'] . "</strong> !";
                                    } else {
                                        echo "<strong>";
                                        if ($internshipInfos['teacher_name']
                                            && $internshipInfos['teacher_firstname']
                                        ) {
                                            echo $internshipInfos
                                                ['teacher_name'] . " " .
                                                $internshipInfos
                                                ['teacher_firstname'];
                                        } else {
                                            echo $id_teacher;
                                        }
                                        echo "</strong>";
                                    }
                                } else {
                                    ?>
                                    <label class="center">
                                        <input type="checkbox"
                                               name="searchedStudent"
                                               class="center-align filled-in"
                                               value="1"
                                            <?php echo in_array(
                                                $internshipInfos
                                                ["internship_identifier"],
                                                $this->internshipModel->getRequests(
                                                    $_SESSION['identifier']
                                                )
                                            ) ? 'checked="checked"' : ''
                                            ?> />
                                        <span>Cocher</span>
                                    </label>
                                    <?php
                                }
                            }
                            ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php
                                if (!$internshipInfos['id_teacher'] && $inDep) {
                                    ?>
                                <div class="row"></div>
                                <button class="waves-effect waves-light btn tooltip"
                                    name="searchedStudentSubmitted" value="<?php echo
                                    $internshipInfos["internship_identifier"] ?>"
                                    type="submit" formmethod="post" data-tooltip=
                                    "Valider votre choix" data-position="top">
                                    Valider
                                </button>
                                    <?php
                                    echo "</form>";
                                } else {
                                    echo "</div>";
                                }
                                echo '<div class="row"></div>';
                        } else {
                            echo "<p>Cet étudiant n'a pas de stage ...</p>";
                        }
                    }
                    ?>
                    <form method="post" class="center-align">
                        <button class=
                            "waves-effect waves-light btn btn-annuler tooltip"
                            name="cancelSearch" value="1" type="submit"
                            formmethod="post" data-tooltip="Annuler la recherche"
                            data-position="top">Annuler</button>
                    </form>
                    <?php
                } else {
                    echo '</div>';
                }
                ?>
            </div>

            <h2 class="center flow-text">Sélectionnez le(s) département(s) :</h2>

            <div class="row"></div>

            <?php
            if (isset($_POST['selecDepSubmitted'])) {
                echo "<script> sessionStorage.clear() </script>";

                if (isset($_POST['selecDep'])) {
                    $_SESSION['selecDep'] = $_POST['selecDep'];

                } else {
                    unset($_SESSION['selecDep']);
                }
            }

            $departments = $this->teacherModel
                ->getDepTeacher($_SESSION['identifier']);
            if(!$departments) : ?>
                <h6 class="left-align">Vous ne faîtes partie d'aucun département</h6>
                    <?php
                    else: ?>
                <form method="post" class="center-align table">
                        <?php
                        foreach ($departments as $dep): ?>
                    <label class="formCell">
                        <input type="checkbox" name="selecDep[]" class="filled-in"
                           value="<?php echo $dep['department_name'] ?>"
                            <?php
                            if (isset($_SESSION['selecDep']) && in_array(
                                $dep['department_name'], $_SESSION['selecDep']
                            )
                            ) : ?>
                                checked="checked" <?php
                            endif; ?> />
                        <span>
                            <?php echo str_replace(
                                '_', ' ', $dep['department_name']
                            ) ?></span>
                    </label>
                        <?php endforeach; ?>
                    <div class="row"></div>
                    <button class="waves-effect waves-light btn tooltip"
                        name="selecDepSubmitted" value="1" type="submit"
                        formmethod="post"
                        data-tooltip="Afficher les tutorats disponibles"
                        data-position="top">Afficher</button>
                </form>

                <div class="row"></div>

                        <?php
                        if(!empty($_SESSION['selecDep'])) :
                            $table = $this->teacherModel->getStudentsList(
                                $_SESSION['selecDep'], $_SESSION['identifier'],
                                $this->internshipModel, $this->departmentModel
                            );
                            if(empty($table)) :
                                echo "<h6 class='left-align'>
                                    Aucun stage disponible</h6>";
                            else: ?>
                        <form method="post" class="center-align table">
                            <table class="highlight centered" id="homepage-table">
                                <thead <?php
                                if (count($table) > 1) {
                                    echo 'class="clickable"';
                                } ?>>
                                <tr>
                                    <th>ETUDIANT</th>
                                    <th>FORMATION</th>
                                    <th>GROUPE</th>
                                    <th>HISTORIQUE</th>
                                    <th>ENTREPRISE</th>
                                    <th>SUJET</th>
                                    <th>ADRESSE</th>
                                    <th>
                                        <div class="tooltip-container tooltip"
                                            data-tooltip="Durée moyenne vous
                                            séparant du lieu du stage"
                                            data-position="top">(?)</div>
                                        POSITION
                                    </th>
                                    <th>
                                        <div class="tooltip-container tooltip"
                                            data-tooltip="Voeux formulés"
                                            data-position="top">(?)</div>
                                        CHOIX
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($table as $row): ?>
                                    <tr class="homepage-row"
                                        data-selected-row="<?php
                                        echo str_replace('_', "'", $row['address'])
                                            . '$' . $row['student_name']
                                        ?>">
                                        <td><?php
                                            echo $row["student_name"] . " " .
                                                $row["student_firstname"]
                                        ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ',
                                                $row["formation"]
                                            )
                                            ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ', $row["class_group"]
                                            )
                                            ?></td>
                                        <td><?php
                                            echo $row['internshipTeacher'] > 0
                                                ? $row['year'] : '❌';
                                        ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ',
                                                $row["company_name"]
                                            )
                                            ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ', $row["internship_subject"]
                                            )
                                            ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', "'", $row['address']
                                            )
                                            ?>
                                            <br>
                                            <i class=
                                               "material-icons clickable tooltip"
                                               data-position="top"
                                               data-tooltip=
                                               "Voir la position de l'entreprise"
                                            >map</i></td>
                                        <td>~<?php echo
                                                $row['duration'] . " minute"
                                                . ($row['duration'] > 1 ? "s" : "")
                                            ?></td>
                                        <td>
                                            <label class="center">
                                                <input type="checkbox"
                                                   name="selecInternship[]"
                                                   class="center-align filled-in"
                                                   value="<?php echo
                                                    $row['internship_identifier'] ?>"
                                                    <?php echo $row['requested'] ?
                                                    'checked="checked"' : '' ?>
                                                />
                                                <span>Cocher</span>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="input-field col s2">
                                    <select id="rows-per-page">
                                        <option value="10" selected>10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="<?php echo count($table)?>">
                                            Tout
                                        </option>
                                    </select>
                                    <label
                                    for="rows-per-page"
                                    >Nombre de lignes par page</label>
                                </div>
                            </div>

                            <div id="pagination-controls" class="center-align">
                                <button type="button" class=
                                    "waves-effect waves-light btn" id="first-page">
                                    <i class="material-icons" type="button">
                                        first_page
                                </i></button>
                                <button type="button" class=
                                    "waves-effect waves-light btn" id="prev-page">
                                    <i class="material-icons" type="button">
                                        arrow_back
                                </i></button>
                                <div id="page-numbers"></div>
                                <button type="button" class=
                                    "waves-effect waves-light btn" id="next-page">
                                    <i class="material-icons" type="button">
                                        arrow_forward
                                </i></button>
                                <button type="button" class=
                                    "waves-effect waves-light btn" id="last-page">
                                    <i class="material-icons" type="button">
                                        last_page
                                </i></button>
                            </div>

                            <div class="row"></div>

                            <div class="inline">
                                <button class="waves-effect waves-light
                                btn btn-annuler tooltip"
                                    type="reset" id="resetForm" data-tooltip=
                                    "Annuler les modifications"
                                    data-position="top">Annuler</button>
                                <button class="waves-effect waves-light btn tooltip"
                                    name="selecInternshipSubmitted" value="1"
                                    type="submit" data-tooltip="Envoyer vos choix"
                                    data-position="top">Valider</button>
                            </div>
                        </form>
                            <?php endif;
                        endif;
                    endif; ?>
            <script>
                <?php if(isset($_SESSION['address'])) : ?>
                    const teacherAddress =
                        "<?php echo $_SESSION['address'][0]['address']; ?>";
                <?php endif;
                if(isset($_SESSION['selected_student']['address'])) : ?>
                    const companyAddress =
                        "<?php echo $_SESSION['selected_student']['address']; ?>";
                <?php endif; ?>
            </script>
            <?php unset($_SESSION['selected_student']); ?>
        </main>
        <?php
    }
}