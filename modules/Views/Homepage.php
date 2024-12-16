<?php
namespace Blog\Views;

class   Homepage {

    public function __construct(private readonly \Blog\Models\Homepage $model, private readonly \Blog\Models\GlobalModel $globalModel) { }

    /**
     * Vue de la homepage
     * @return void
     */
    public function showView(): void {
        ?>
        <main>
            <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

            <div class="card-panel white">
                <form class="col table" id="searchForm" onsubmit="return false;" method="POST">
                    <label for="searchType">Type de recherche:</label>
                    <div class="input-field">
                        <select id="searchType" name="searchType">
                            <option value="studentNumber" selected>Numéro Etudiant</option>
                            <option value="name">Nom et Prénom</option>
                            <option value="company">Entreprise</option>
                        </select>
                    </div>
                    <label for="search">Rechercher un étudiant:</label>
                    <input type="text" id="search" name="search" autocomplete="off" maxlength="50" required>
                    <p>Etudiant(s):</p>
                    <div id="searchResults"></div>
                </form>
            </div>
            <form class="center">
                <?php
                if(isset($_POST['cancel'])) {
                    unset($_SESSION['selected_student']);
                }

                if (isset($_POST['page'])) {
                    $_SESSION['unconfirmed'][$_SESSION['lastPage'] ?? 1] = $_POST['selecInternship'] ?? array();
                    $_SESSION['unconfirmed']['all'] = array();
                    foreach ($_SESSION['unconfirmed'] as $page => $internships) {
                        if ($page == 'all') continue;
                        $_SESSION['unconfirmed']['all'] = array_merge(gettype($internships) == "array" ? $internships : [$internships], $_SESSION['unconfirmed']['all']);
                    }
                    $_SESSION['lastPage'] = $_POST['page'];
                }

                if(isset($_POST['searchedStudentSubmitted'])) {

                    if(isset($_POST['searchedStudent'])) {
                        $update = $this->model->updateRequests([$_POST['searchedStudent']], $_SESSION['identifier']);

                    } else {
                        $update = $this->model->updateRequests(array(), $_SESSION['identifier']);
                    }

                    if(!$update || gettype($update) !== 'boolean') {
                        echo '<h6 class="red-text">Une erreur est survenue</h6>';
                    }
                }

                if(isset($_POST['selecInternshipSubmitted'])) {

                    $_SESSION['unconfirmed'][$_SESSION['lastPage']] = $_POST['selecInternship'] ?? array();
                    $_SESSION['unconfirmed']['all'] = array();
                    foreach ($_SESSION['unconfirmed'] as $page => $internships) {
                        if ($page == 'all') continue;
                        $_SESSION['unconfirmed']['all'] = array_merge(gettype($internships) == "array" ? $internships : [$internships], $_SESSION['unconfirmed']['all']);
                    }

                    $update = $this->model->updateRequests($_SESSION['unconfirmed']['all'], $_SESSION['identifier']);

                    if(!$update || gettype($update) !== 'boolean') {
                        echo '<h6 class="red-text">Une erreur est survenue</h6>';
                    } else unset($_SESSION['unconfirmed']);
                }

                if (isset($_SESSION['selected_student']['firstName']) && isset($_SESSION['selected_student']['lastName'])) {
                    echo '<h4 class="left-align"> Résultat pour: ' . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . '</h4>' . '</div>';
                if (!isset($_SESSION['selected_student']['address']) || $_SESSION['selected_student']['address'] === '') {
                    echo "<p>Cet étudiant n'a pas de stage ...</p>";
                }
                else {
                    $internshipInfos = $this->model->getInternshipStudent($_SESSION['selected_student']['id']);
                    if ($internshipInfos) {
                        $internships = $this->globalModel->getInternships($_SESSION['selected_student']['id']);
                        $nbInternships = $this->model->getInternshipTeacher($internships, $_SESSION['identifier']);
                        $distance = $this->globalModel->getDistance($internshipInfos['internship_identifier'], $_SESSION['identifier']);
                        ?>
                        <div id="map"></div>
                        <div class="row"></div>
                        <?
                        $inDep = false;
                        foreach ($this->model->getDepStudent($_SESSION['selected_student']['id']) as $dep) {
                            if (in_array($dep, $this->globalModel->getDepTeacher($_SESSION['identifier']))) {
                                $inDep = true;
                                break;
                            }
                        }
                        if (!$internshipInfos['id_teacher'] && $inDep) {
                            echo '<form method="post" class="center-align table">';
                        } else {
                            echo '<div class="center-align table">';
                        }
                        ?>
                            <table class="highlight centered">
                                <thead>
                                <tr>
                                    <th>STAGE/ALTERNANCE</th>
                                    <th>HISTORIQUE</th>
                                    <th>DISTANCE</th>
                                    <th>SUJET</th>
                                    <th>ENTREPRISE</th>
                                    <th>CHOIX</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= $internshipInfos['internship_identifier']?></td>
                                        <td><?= $nbInternships > 0 ? 'Oui' : 'Non' ?></td>
                                        <td>~<?= $distance ?> minutes</td>
                                        <td><?= str_replace('_', ' ', $internshipInfos["internship_subject"]) ?></td>
                                        <td><?= str_replace('_', ' ', $internshipInfos["company_name"]) ?></td>
                                        <td>
                                            <?
                                            if (!$inDep) {
                                                echo "<strong>" . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . "</strong> ne fait partie d'aucun de vos départements";
                                            } else {
                                                if ($internshipInfos['id_teacher']) {
                                                    if ($internshipInfos['id_teacher'] === $_SESSION['identifier']) {
                                                        echo "Vous êtes déjà le tuteur de " . "<strong>" . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . "</strong> !";
                                                    } else {
                                                        echo "<strong>" . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . "</strong> a déjà <strong>" . $internshipInfos['id_teacher'] . "</strong> comme tuteur.";
                                                    }
                                                } else {
                                                    ?>
                                                    <label class="center">
                                                        <input type="checkbox" name="searchedStudent" class="center-align filled-in" value="<?= $internshipInfos["internship_identifier"] ?>" <?= in_array($internshipInfos["internship_identifier"], $this->model->getRequests($_SESSION['identifier'])) ? 'checked="checked"' : '' ?> />
                                                        <span></span>
                                                    </label>
                                                    <?
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?
                        if (!$internshipInfos['id_teacher'] && $inDep) {
                            ?>
                            <div class="row"></div>
                            <input type="hidden" name="searchedStudentSubmitted" value="1">
                            <button class="waves-effect waves-light btn" type="submit">Valider</button>
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
                <form method="post" class="center-align table">
                    <input type="hidden" name="cancel" value="1">
                    <button class="waves-effect waves-light btn" type="submit">Annuler</button>
                </form>
            <?
            } else {
                echo '</div>';
            }
            ?>

            <h4 class="center">Sélectionnez le(s) département(s) :</h4>

            <div class="row"></div>

            <?
            if (isset($_POST['selecDepSubmitted'])) {
                if (isset($_POST['selecDep'])) {
                    $_SESSION['selecDep'] = $_POST['selecDep'];

                } else {
                    unset($_SESSION['selecDep']);
                }
            }

            $departments = $this->globalModel->getDepTeacher($_SESSION['identifier']);
            if(!$departments): ?>
                <h6 class="left-align">Vous ne faîtes partie d'aucun département</h6>
            <?
            else: ?>
                <form method="post" class="center-align table">
                    <?
                    foreach($departments as $dep): ?>
                    <label class="formCell">
                        <input type="checkbox" name="selecDep[]" class="filled-in" value="<?= $dep['department_name'] ?>" <? if(isset($_SESSION['selecDep']) && in_array($dep['department_name'], $_SESSION['selecDep'])): ?> checked="checked" <? endif; ?> />
                        <span><? echo str_replace('_', ' ', $dep['department_name']) ?></span>
                    </label>
                    <? endforeach; ?>
                    <div class="row"></div>
                    <button class="waves-effect waves-light btn" name="selecDepSubmitted" value="1" type="submit" formmethod="post">Afficher</button>
                </form>

                <div class="row"></div>

                <?
                if(!empty($_SESSION['selecDep'])):
                    $table = $this->model->getStudentsList($_SESSION['selecDep'], $_SESSION['identifier']);
                    if(isset($_POST['sortSubmitted'])) {
                        $_SESSION['sortBy'] = $_POST['sortBy'] ?? 0;
                        $_SESSION['decreasing'] = $_POST['decreasing'] ?? false;
                    }
                    $table = $this->model->sortRows($table, $_SESSION['sortBy'] ?? 0, $_SESSION['decreasing'] ?? 0);

                    if (!isset($_SESSION['unconfirmed'])) {
                        $_SESSION['unconfirmed'] = array();
                        $_SESSION['unconfirmed']['all'] = array();
                        for ($i = 0; $i < count($table); ++$i) {
                            if (!isset($_SESSION['unconfirmed'][ceil(($i+1)/10)])) $_SESSION['unconfirmed'][ceil(($i+1)/10)] = array();
                            if ($table[$i]['requested']) {
                                $_SESSION['unconfirmed'][ceil(($i+1)/10)][count($_SESSION['unconfirmed'][ceil(($i+1)/10)])] = $table[$i]['internship_identifier'];
                                $_SESSION['unconfirmed']['all'][count($_SESSION['unconfirmed']['all'])] = $table[$i]['internship_identifier'];
                            }
                        }
                    }

                    if(empty($table)):
                        echo "<h6 class='left-align'>Aucun stage disponible</h6>";
                    else: ?>
                        <form method="post" class="center-align table">
                            <div class="selection">
                                <div class="formCell">
                                    <label for="sortBy">Trier par:</label>
                                    <div class="input-field">
                                        <select id="sortBy" name="sortBy">
                                            <option value=0 <? if(!isset($_SESSION['sortBy']) || $_SESSION['sortBy'] === "0") echo "selected"; ?> >Choix</option>
                                            <!-- <option value=1 <? //if(isset($_SESSION['sortBy']) && $_SESSION['sortBy'] === "1") echo "selected"; ?> >Total</option> -->
                                            <option value=2 <? if(isset($_SESSION['sortBy']) && $_SESSION['sortBy'] === "2") echo "selected"; ?> >Élève</option>
                                            <option value=3 <? if(isset($_SESSION['sortBy']) && $_SESSION['sortBy'] === "3") echo "selected"; ?> >Sujet</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="formCell">
                                    <label for="decreasing">Trier par ordre:</label>
                                    <div class="input-field">
                                        <select id="decreasing" name="decreasing">
                                            <option value="0" <? if(!isset($_SESSION['decreasing']) || $_SESSION['decreasing'] === "0") echo "selected"; ?> >Croissant</option>
                                            <option value="1" <? if(isset($_SESSION['decreasing']) && $_SESSION['decreasing'] === "1") echo "selected"; ?> >Décroissant</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="sortSubmitted" value="1">
                            <button class="waves-effect waves-light btn" type="submit">Trier</button>
                        </form>

                        <div class="row"></div>

                        <form method="post" class="center-align table">
                            <table class="highlight centered">
                                <thead>
                                <tr>
                                    <th>STAGE/ALTERNANCE</th>
                                    <th>ELEVE</th>
                                    <th>HISTORIQUE</th>
                                    <th>DISTANCE</th>
                                    <th>SUJET</th>
                                    <th>ENTREPRISE</th>
                                    <th>CHOIX</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?
                                $totalPages = ceil(count($table) / 10);
                                $page = isset($_POST['page']) && $_POST['page'] > 0 && $_POST['page'] <= $totalPages ? (int)$_POST['page'] : 1;

                                for ($i = ($page-1)*10 ; $i < $page*10 && $i < count($table) ; ++$i):
                                    $row = $table[$i];
                                    ?>
                                    <tr>
                                        <td><?= $row['internship_identifier']?></td>
                                        <td><?= $row["student_name"] . " " . $row["student_firstname"] ?></td>
                                        <td>
                                            <?php
                                            echo $row['internshipTeacher'] > 0 ? 'Oui' : 'Non';
                                            ?>
                                        </td>
                                        <td>~<?= $row['duration'] ?> minutes</td>
                                        <td><?= str_replace('_', ' ', $row["internship_subject"]) ?></td>
                                        <td><?= str_replace('_', ' ', $row["company_name"]) ?></td>
                                        <td>
                                            <label class="center">
                                                <input type="checkbox" name="selecInternship[]" class="center-align filled-in" value="<?= $row['internship_identifier'] ?>" <?= isset($_SESSION['unconfirmed']['all']) && in_array($row['internship_identifier'], $_SESSION['unconfirmed']['all']) ? 'checked="checked"' : '' ?> />
                                                <span></span>
                                            </label>
                                        </td>
                                    </tr>
                                <? endfor; ?>
                                </tbody>
                            </table>
                            <div class="row"></div>
                            <button class="waves-effect waves-light btn" name="selecInternshipSubmitted" value="1" type="submit">Valider</button>
                            <?
                            if ($totalPages > 1):
                                $start = ($totalPages>9 && $page > 1) ? $page-1 : 1;
                                $end = ($totalPages>9 && $start+8 < $totalPages) ? $start+8 : $totalPages;
                                while ($start > 1 && $end - $start < 8) --$start;
                                ?>
                                <div class="row"></div>
                                <div class="pagination">
                                    <?php if ($start > 1): ?>
                                        <button class="waves-effect waves-light btn first" name="page" value="1" type="submit"><<</button>
                                    <?php endif;

                                    if ($page > 1): ?>
                                        <button class="waves-effect waves-light btn prev" name="page" value="<?= $page - 1 ?>" type="submit">Précédent</button>
                                    <?php endif;

                                    if ($start > 1): ?>
                                        <span class="hidden">...</span>
                                    <?php endif;

                                    for ($i = $start ; $i <= $end; ++$i): ?>
                                        <button class="waves-effect waves-light btn <?= $i == $page ? 'active' : '' ?>" name="page" value="<?= $i ?>" type="submit"><?= $i ?></button>
                                    <?php endfor;

                                    if ($end < $totalPages): ?>
                                        <span class="hidden">...</span>
                                    <?php endif;

                                    if ($page < $totalPages): ?>
                                        <button class="waves-effect waves-light btn next" name="page" value="<?= $page + 1 ?>" type="submit">Suivant</button>
                                    <?php endif;

                                    if ($end < $totalPages): ?>
                                        <button class="waves-effect waves-light btn last" name="page" value="<?= $totalPages ?>" type="submit">>></button>
                                    <?php endif; ?>
                                </div>
                        </form>
                        <? endif;
                    endif;
                endif;
            endif; ?>
            <script>
                <? if(isset($_SESSION['address'])): ?>
                    const teacherAddress = "<?= $_SESSION['address'][0]['address']; ?>";
                <? endif;
                if(isset($_SESSION['selected_student']['address'])): ?>
                    const companyAddress = "<?= $_SESSION['selected_student']['address']; ?>";
                <? endif; ?>
            </script>
        </main>
        <?php
    }
}

