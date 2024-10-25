<?php
namespace Blog\Views;

class Homepage {

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
                <form class="col" id="searchForm" onsubmit="return false;" method="POST">
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
            <div class="center">
                <?php
                if (isset($_SESSION['selected_student']['firstName']) && isset($_SESSION['selected_student']['lastName'])) {
                    echo '<h4 class="left-align"> Résultat pour: ' . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . '</h4>';
                }
                ?>
            </div>
            <?php
            if (!isset($_SESSION['selected_student']['address']) || $_SESSION['selected_student']['address'] === '') {
                echo "<p>Cet étudiant n'a pas de stage ...</p>";
            }
            else {
                ?>
                <div id="map"></div>
                <?php
            }
            ?>

            <h4 class="center">Sélectionnez le(s) département(s) :</h4>

            <div class="row"></div>

            <? if (isset($_POST['selecDepSubmitted'])) {

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
                <form method="post" class="center-align">
                    <div class="selection">
                        <?
                        foreach($departments as $dep): ?>
                        <label class="formCell">
                            <input type="checkbox" name="selecDep[]" class="filled-in" value="<?= $dep['department_name'] ?>" <? if(isset($_SESSION['selecDep']) && in_array($dep['department_name'], $_SESSION['selecDep'])): ?> checked="checked" <? endif; ?> />
                            <span><? echo str_replace('_', ' ', $dep['department_name']) ?></span>
                        </label>
                        <? endforeach; ?>
                    </div>
                    <input type="hidden" name="selecDepSubmitted" value="1">
                    <button class="waves-effect waves-light btn" type="submit">Afficher</button>
                </form>

                <div class="row"></div>

                <?
                if(isset($_POST['selecStudentSubmitted'])) {

                    if(isset($_POST['selecStudent'])) {
                        $update = $this->model->updateRequests($_POST['selecStudent']);

                    } else {
                        $update = $this->model->updateRequests(array());
                    }

                    if(!$update || gettype($update) !== 'boolean') {
                        echo '<p class="red-text">Une erreur est survenue</p>';
                    }
                }

                if(!empty($_SESSION['selecDep'])):
                    $table = $this->model->getStudentsList($_SESSION['selecDep'], $_SESSION['identifier']);
                    if(isset($_POST['sortSubmitted'])) {
                        $_SESSION['sortBy'] = $_POST['sortBy'] ?? 0;
                        $_SESSION['decreasing'] = $_POST['decreasing'] ?? false;
                    }
                    $table = $this->model->sortRows($table, $_SESSION['sortBy'] ?? 0, $_SESSION['decreasing'] ?? 0);
                    if(empty($table)):
                        echo "<h6 class='left-align'>Aucun stage disponible</h6>";
                    else: ?>
                        <form method="post" class="center-align">
                            <label for="sortBy">Trier par:</label>
                            <div class="input-field">
                                <select id="sortBy" name="sortBy">
                                    <option value=0 <? if(!isset($_SESSION['sortBy']) || $_SESSION['sortBy'] === "0") echo "selected"; ?> >Choix</option>
                                    <option value=1 <? if(isset($_SESSION['sortBy']) && $_SESSION['sortBy'] === "1") echo "selected"; ?> >Total</option>
                                    <option value=2 <? if(isset($_SESSION['sortBy']) && $_SESSION['sortBy'] === "2") echo "selected"; ?> >Élève</option>
                                    <option value=3 <? if(isset($_SESSION['sortBy']) && $_SESSION['sortBy'] === "3") echo "selected"; ?> >Sujet</option>
                                </select>
                            </div>
                            <label for="decreasing">Trier par ordre:</label>
                            <div class="input-field">
                                <select id="decreasing" name="decreasing">
                                    <option value="0" <? if(!isset($_SESSION['decreasing']) || $_SESSION['decreasing'] === "0") echo "selected"; ?> >Croissant</option>
                                    <option value="1" <? if(isset($_SESSION['decreasing']) && $_SESSION['decreasing'] === "1") echo "selected"; ?> >Décroissant</option>
                                </select>
                            </div>
                            <input type="hidden" name="sortSubmitted" value="1">
                            <button class="waves-effect waves-light btn" type="submit">Trier</button>
                        </form>

                        <div class="row"></div>

                        <form method="post" class="center-align">
                            <div class="scrollable-table-container">
                                <table class="highlight centered">
                                    <thead>
                                    <tr>
                                        <th>ELEVE</th>
                                        <th>HISTORIQUE</th>
                                        <th>DISTANCE</th>
                                        <th>SUJET</th>
                                        <th>ENTREPRISE</th>
                                        <th>TOTAL</th>
                                        <th>CHOIX</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($table as $row): ?>
                                        <tr>
                                            <td><?= $row["student_name"] . " " . $row["student_firstname"] ?></td>
                                            <td>
                                                <?php
                                                echo $row['internshipTeacher'] > 0 ? 'Oui' : 'Non';
                                                ?>
                                            </td>
                                            <td>~<?= $row['duration'] ?> minutes</td>
                                            <td><?= str_replace('_', ' ', $row["internship_subject"]) ?></td>
                                            <td><?= str_replace('_', ' ', $row["company_name"]) ?></td>
                                            <td><strong><?= round($row['score'], 2) ?></strong>/5</td>
                                            <td>
                                                <label class="center">
                                                    <input type="checkbox" name="selecStudent[]" class="center-align filled-in" value="<?= $row['student_number'] ?>" <?= $row['requested'] ? 'checked="checked"' : '' ?> />
                                                    <span></span>
                                                </label>
                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="selecStudentSubmitted" value="1">
                            <button class="waves-effect waves-light btn" type="submit">Valider</button>
                        </form>
                    <? endif;
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

