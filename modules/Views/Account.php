<?php

namespace Blog\Views;

class Account {

    public function __construct(private readonly \Blog\Models\Account $model) { }

    /**
     * Vue de Account
     * @return void
     */
    public function showView(): void {
        ?>
        <main>
            <h3 class="center-align">Stages et alternances assignés</h3>

            <?
            $interns = $this->model->getInterns($_SESSION['identifier']);
            $max = $this->model->getMaxNumberInterns($_SESSION['identifier']);
            $internship = 0;
            $alternance = 0;
            $this->model->getCountInternsPerType($interns, $internship, $alternance);

            if (isset($_POST['newMaxNumberSubmitted'])) {
                if (isset($_POST['newMaxNumber']) && (!$max || $max !== $_POST['newMaxNumber'])
                    && intval($_POST['newMaxNumber']) > 0 && intval($_POST['newMaxNumber']) <= 100) {
                    if (intval($_POST['newMaxNumber']) >= $internship + $alternance) {
                        $update = $this->model->updateMaxiNumberTrainees($_SESSION['identifier'], intval($_POST['newMaxNumber']));
                        if (!$update || gettype($update) !== 'boolean') {
                            echo '<h6 class="red-text">Une erreur est survenue</h6>';
                        } else $max = $_POST['newMaxNumber'];
                    } else $tooLow = true;
                }
            }

            ?>

            <div class="row"></div>

            <div id="countInternships">
                <div>
                    <?
                    echo '<h5>';
                    if ($internship + $alternance > 0) {
                        if ($internship > 0) {
                            if ($internship === 1) {
                                echo "Vous tutorez <strong>1</strong> stage ";
                            } else {
                                echo "Vous tutorez <strong>" . $internship . "</strong> stages ";
                            }
                        } else echo "Vous ne tutorez <strong>aucun</strong> stage ";
                        if ($alternance > 0) {
                            if ($alternance === 1) {
                                echo "et <strong>1</strong> alternance";
                            } else {
                                echo "et <strong>" . $alternance . "</strong> alternances";
                            }
                        } else echo "mais <strong>aucune</strong> alternance";
                    } else echo "Vous ne tutorez <strong>aucun stage ni alternance</strong>";
                    if ($max) echo " sur un maximum de <strong>" . $max . "</strong> au total";
                    echo '</h5>';
                    ?>
                </div>
                <div class="card-panel white">
                    <div class="inline">
                        <? if (!$max): ?>
                            <p class="countTrainees cell">Valeur maximale introuvable, veuillez en entrer une nouvelle</p>
                        <? else: ?>
                            <p class="countTrainees cell">Valeur maximale actuelle: <?= $max ?></p>
                        <? endif; ?>
                        <p class="countTrainees cell">Nombre total de tutorats en cours: <?= $internship + $alternance ?></p>
                    </div>
                    <form method="post" class="inline">
                        <div class="input-field cell">
                            <label for="newMaxNumber">Nouvelle valeur maximale:</label>
                            <input type="number" name="newMaxNumber" id="newMaxNumber" min="1" max="100" value="<?= ($max) ? $max : 1 ?>" />
                        </div>
                        <div class="cell">
                            <div>
                                <button type="submit" name="newMaxNumberSubmitted" value="1" class="waves-effect waves-light btn">Valider</button>
                            </div>
                            <div>
                                <button type="reset" class="waves-effect waves-light btn">Annuler</button>
                            </div>
                        </div>
                    </form>
                </div>
                <?
                if (isset($update) && (!$update || gettype($update) !== 'boolean')) {
                    echo '<h6 class="red-text">Une erreur est survenue</h6>';
                } else if (isset($tooLow) && $tooLow) {
                    echo '<h6 class="red-text">Vous avez déjà plus de <strong>' . $_POST['newMaxNumber'] . '</strong> tutorat' . ($_POST['newMaxNumber'] > 1 ? 's' : '') . ' en cours</h6>';
                }
                ?>
            </div>

            <? if ($interns): ?>
                <div class="row"></div>
                <div id="table">
                    <table class="highlight centered" id="account-table">
                        <thead>
                        <tr>
                            <th class="clickable">ETUDIANT</th>
                            <th class="clickable">FORMATION</th>
                            <th class="clickable">GROUPE</th>
                            <th class="clickable">HISTORIQUE</th>
                            <th class="clickable">ENTREPRISE</th>
                            <th class="clickable">SUJET</th>
                            <th class="clickable">ADRESSE</th>
                            <th class="clickable">DISTANCE</th>
                        </tr>
                        </thead>
                        <tbody>
                        <? foreach ($interns as $row): ?>
                            <tr class="account-row">
                                <td><?= $row["student_name"] . " " . $row["student_firstname"] ?></td>
                                <td><?= str_replace('_', ' ', $row["formation"]) ?></td>
                                <td><?= str_replace('_', ' ', $row["class_group"]) ?></td>
                                <td><?= $row['internshipTeacher'] > 0 ? $row['year'] : 'Non'; ?></td>
                                <td><?= str_replace('_', ' ', $row["company_name"]) ?></td>
                                <td><?= str_replace('_', ' ', $row["internship_subject"]) ?></td>
                                <td><?= str_replace('_', "'", $row['address']) ?></td>
                                <td>~<?= $row['duration'] ?> minutes</td>
                            </tr>
                        <? endforeach; ?>
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
                                <option value="<?= count($interns)?>">Tout</option>
                            </select>
                            <label>Nombre de lignes par page</label>
                        </div>
                    </div>

                    <div id="pagination-controls" class="center-align">
                        <button type="button" class="waves-effect waves-light btn" id="first-page"><i class="material-icons" type="button">first_page</i></button>
                        <button type="button" class="waves-effect waves-light btn" id="prev-page"><i class="material-icons" type="button">arrow_back</i></button>
                        <div id="page-numbers"></div>
                        <button type="button" class="waves-effect waves-light btn" id="next-page"><i class="material-icons" type="button">arrow_forward</i></button>
                        <button type="button" class="waves-effect waves-light btn" id="last-page"><i class="material-icons" type="button">last_page</i></button>
                    </div>
                </div>
            <? endif; ?>
        </main>
    <?php
    }
}