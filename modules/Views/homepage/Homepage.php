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

use Blog\Models\GlobalModel;
use function Blog\Views\gettype;

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
     * @param \Blog\Models\Homepage $model       Instance de la classe Homepage
     *                                           servant de modèle pour la vue
     * @param GlobalModel           $globalModel Instance de la calsse GlobalModel
     *                                           servant de modèle
     */
    public function __construct(private \Blog\Models\Homepage $model,
        private GlobalModel $globalModel
    ) {
    }

    /**
     * Vue de la homepage
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

            <div class="card-panel white">
                <form class="col table" id="searchForm"
                onsubmit="return false;" method="POST">
                    <label for="searchType">Type de recherche:</label>
                    <div class="input-field">
                        <select id="searchType" name="searchType">
                            <option value="studentNumber"
                                selected>Numéro Etudiant</option>
                            <option value="name">Nom et Prénom</option>
                            <option value="company">Entreprise</option>
                        </select>
                    </div>
                    <label for="search">Rechercher:</label>
                    <input type="text" id="search" name="search"
                        autocomplete="off" maxlength="50" required>
                    <p>Etudiant(s):</p>
                    <div id="searchResults"></div>
                </form>
            </div>
            <div class="center">
                <?php
                if (isset($_POST['cancelSearch'])) {
                    unset($_SESSION['selected_student']);
                }

                if (isset($_POST['searchedStudentSubmitted'])) {

                    $update = $this->model->updateSearchedStudent(
                        isset($_POST['searchedStudent']), $_SESSION['identifier'],
                        $_POST['searchedStudentSubmitted']
                    );

                    if (!$update || gettype($update) !== 'boolean') {
                        echo '<h6 class="red-text">Une erreur est survenue</h6>';
                    }
                }

                if (isset($_POST['selecInternshipSubmitted'])) {
                    $update = $this->model->updateRequests(
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

                if (isset($_SESSION['selected_student']['firstName'])
                    && isset($_SESSION['selected_student']['lastName'])
                ) {
                    echo '<h4 class="left-align"> Résultat pour: ' .
                        $_SESSION['selected_student']['firstName'] . ' ' .
                        $_SESSION['selected_student']['lastName'] . '</h4>';
                    if (!isset($_SESSION['selected_student']['address'])
                        || $_SESSION['selected_student']['address'] === ''
                    ) {
                        echo "<p>Cet étudiant n'a pas de stage ...</p>";
                    } else {
                        $internshipInfos = $this->model->getInternshipStudent(
                            $_SESSION['selected_student']['id']
                        );
                        if ($internshipInfos) {
                            $internships = $this->globalModel->getInternships(
                                $_SESSION['selected_student']['id']
                            );
                            $year = "";
                            $nbInternships = $this->globalModel
                                ->getInternshipTeacher(
                                    $internships, $_SESSION['identifier'], $year
                                );
                            $distance = $this->globalModel->getDistance(
                                $internshipInfos['internship_identifier'],
                                $_SESSION['identifier'],
                                isset($internshipInfos['id_teacher'])
                            );
                            ?>
                            <div id="map"></div>
                            <div class="row"></div>
                            <?php
                            $inDep = false;
                            foreach ($this->model->getDepStudent(
                                $_SESSION['selected_student']['id']
                            ) as $dep) {
                                if (in_array(
                                    $dep, $this->globalModel
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
                                <table class="highlight centered">
                                    <thead>
                                    <tr>
                                        <th>FORMATION</th>
                                        <th>GROUPE</th>
                                        <th>HISTORIQUE</th>
                                        <th>ENTREPRISE</th>
                                        <th>SUJET</th>
                                        <th>ADRESSE</th>
                                        <th>DISTANCE</th>
                                        <th>CHOIX</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                            <?php echo str_replace(
                                                '_', ' ',
                                                $internshipInfos['formation']
                                            ) ?>
                                            </td>
                                            <td>
                                                <?php echo str_replace(
                                                    '_', ' ',
                                                    $internshipInfos['class_group']
                                                ) ?>
                                            </td>
                                            <td>
                                                    <?php echo $nbInternships > 0
                                                    ? $year : 'Non' ?>
                                            </td>
                                            <td>
                                                <?php echo str_replace(
                                                    '_', ' ',
                                                    $internshipInfos["company_name"]
                                                ) ?>
                                            </td>
                                            <td>
                                                    <?php echo str_replace(
                                                        '_', ' ',
                                                        $internshipInfos[
                                                        "internship_subject"
                                                        ]
                                                    ) ?>
                                            </td>
                                            <td>
                                                    <?php echo str_replace(
                                                        '_', "'",
                                                        $internshipInfos['address']
                                                    ) ?>
                                            </td>
                                            <td>
                                                ~<?php echo $distance ?> minutes
                                            </td>
                                            <td>
                            <?php
                            if (!$inDep) {
                                echo "<strong>" .
                                $_SESSION['selected_student']['firstName']. ' ' .
                                $_SESSION['selected_student']['lastName']
                                . "</strong> ne fait partie d'aucun
                                de vos départements";
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
                                               class="center-align
                                               filled-in" value="1"
                                            <?php echo in_array(
                                                $internshipInfos
                                                ["internship_identifier"],
                                                $this->model->getRequests(
                                                    $_SESSION['identifier']
                                                )
                                            ) ? 'checked="checked"' : ''
                                            ?> />
                                        <span></span>
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
                                    type="submit" formmethod="post" data-tooltip
                                    ="Valider votre choix" data-position="top">
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
                        <button class="waves-effect waves-light btn tooltip"
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

            <h4 class="center">Sélectionnez le(s) département(s) :</h4>

            <div class="row"></div>

            <?php
            if (isset($_POST['selecDepSubmitted'])) {
                if (isset($_POST['selecDep'])) {
                    $_SESSION['selecDep'] = $_POST['selecDep'];

                } else {
                    unset($_SESSION['selecDep']);
                }
            }

            $departments = $this->globalModel
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
                        formmethod="post" data-tooltip="Afficher les tutorats
                        disponibles" data-position="top">Afficher</button>
                </form>

                <div class="row"></div>

                        <?php
                        if(!empty($_SESSION['selecDep'])) :
                            $table = $this->model->getStudentsList(
                                $_SESSION['selecDep'], $_SESSION['identifier']
                            );
                            if(empty($table)) :
                                echo "<h6 class='left-align'>
                                    Aucun stage disponible</h6>";
                            else: ?>
                        <form method="post" class="center-align table">
                            <table class="highlight centered" id="homepage-table">
                                <thead>
                                <tr>
                                    <th class="clickable">ETUDIANT</th>
                                    <th class="clickable">FORMATION</th>
                                    <th class="clickable">GROUPE</th>
                                    <th class="clickable">HISTORIQUE</th>
                                    <th class="clickable">ENTREPRISE</th>
                                    <th class="clickable">SUJET</th>
                                    <th class="clickable">ADRESSE</th>
                                    <th class="clickable tooltip" data-tooltip
                                    ="Distance moyenne vous séparant du lieu du
                                    stage" data-position="top">DISTANCE</th>
                                    <th class="clickable tooltip" data-tooltip
                                    ="Voeux formulés" data-position="top">CHOIX</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($table as $row): ?>
                                    <tr class="homepage-row">
                                        <td>
                                            <?php echo $row["student_name"] . " " .
                                                $row["student_firstname"] ?>
                                        </td>
                                        <td>
                                            <?php echo str_replace(
                                                '_', ' ',
                                                $row["formation"]
                                            ) ?>
                                        </td>
                                        <td>
                                            <?php echo str_replace(
                                                '_', ' ', $row["class_group"]
                                            ) ?>
                                        </td>
                                        <td>
                                            <?php echo $row['internshipTeacher'] > 0
                                                ? $row['year'] : 'Non'; ?>
                                        </td>
                                        <td>
                                            <?php echo str_replace(
                                                '_', ' ',
                                                $row["company_name"]
                                            ) ?>
                                        </td>
                                        <td>
                                            <?php echo str_replace(
                                                '_', ' ', $row["internship_subject"]
                                            ) ?>
                                        </td>
                                        <td>
                                            <?php echo str_replace(
                                                '_', "'", $row['address']
                                            ) ?>
                                        </td>
                                        <td>
                                            ~<?php echo $row['duration'] ?> minutes
                                        </td>
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
                                                <span></span>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="input-field col s2">
                                    <label for="rows-per-page"></label>
                                    <select id="rows-per-page">
                                        <option value="10" selected>10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="<?php echo count($table)?>">
                                            Tout
                                        </option>
                                    </select>
                                    <label>Nombre de lignes par page</label>
                                </div>
                            </div>

                            <div id="pagination-controls" class="center-align">
                                <button type="button" class="waves-effect waves-light
                                    btn" id="first-page"><i class="material-icons"
                                    type="button">first_page</i></button>
                                <button type="button" class="waves-effect waves-light
                                    btn" id="prev-page"><i class="material-icons"
                                    type="button">arrow_back</i></button>
                                <div id="page-numbers"></div>
                                <button type="button" class="waves-effect waves-light
                                    btn" id="next-page"><i class="material-icons"
                                    type="button">arrow_forward</i></button>
                                <button type="button" class="waves-effect waves-light
                                    btn" id="last-page"><i class="material-icons"
                                    type="button">last_page</i></button>
                            </div>

                            <div class="row"></div>

                            <div class="selection"> <div class="formCell">
                                <button class="waves-effect waves-light btn tooltip"
                                    name="selecInternshipSubmitted" value="1"
                                    type="submit" data-tooltip="Envoyer vos
                                    choix" data-position="top">Valider</button>
                                <button class="waves-effect waves-light btn tooltip"
                                    type="reset" data-tooltip="Annuler les
                                    modifications" data-position="top">
                                    Annuler
                                </button>
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
        </main>
        <?php
    }
}