<?php
namespace Blog\Views;

class Homepage {

    public function __construct(private readonly \Blog\Models\Homepage $model) { }

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
                if (isset($_SESSION['selected_student']) && isset($_SESSION['selected_student']['firstName']) && isset($_SESSION['selected_student']['lastName'])) {
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

            <h4 class="left-align">Sélectionnez le(s) département(s) :</h4>

            <div class="row"></div>

            <?
            $departments = $this->model->getDepTeacher();
            if(!$departments): ?>
                <h6 class="left-align">Vous ne faîtes partie d'aucun département</h6>
            <?
            else: ?>
                <form method="post" class="center-align">
                    <div class="selection">
                        <?
                        foreach($departments as $dep): ?>
                        <label class="formCell">
                            <input type="checkbox" id="selecDep[]" name="selecDep[]" class="filled-in" value="<?= $dep['department_name'] ?>" <? if(isset($_POST['selecDep']) && in_array($dep['department_name'], $_POST['selecDep'])): ?> checked="checked" <? endif; ?> />
                            <span><? echo str_replace('_', ' ', $dep['department_name']) ?></span>
                        </label>
                        <? endforeach; ?>
                    </div>
                    <button class="waves-effect waves-light btn" type="submit">Afficher</button>
                </form>

                <div class="row"></div>

                <?
                if(!empty($_POST['selecDep'])):
                ?>
                    <form method="post" class="center-align">
                        <table class="highlight centered">
                            <thead>
                            <tr>
                                <th>ELEVE</th>
                                <th>HISTORIQUE</th>
                                <th>POSITION</th>
                                <th>SUJET</th>
                                <th>ENTREPRISE</th>
                                <th>TOTAL</th>
                                <th>CHOIX</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?
                            foreach($this->model->getStudentsList($_POST['selecDep']) as $eleve): ?>
                                <tr>
                                    <td><? echo $eleve["student_name"] . " " . $eleve["student_firstname"] ?></td>
                                    <td><? echo $eleve["internshipTeacher"] ?></td>
                                    <td> <? echo str_replace('_', "'", $eleve["address"]) ?> </td>
                                    <td> <? echo str_replace('_', ' ', $eleve["internship_subject"]) ?> </td>
                                    <td> <? echo $eleve["company_name"] ?> </td>
                                    <td><? echo "<strong>" . round($eleve['relevance'], 2) . "</strong>/5" ?></td>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="selecStudent[]" name="selecStudent[]" class="center-align filled-in" value="<?= $eleve['student_name'] ?>" />
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            <? endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                <? endif; ?>
            <? endif; ?>

            <script>
                const teacherAddress = "<?php echo isset($_SESSION['address']) ? $_SESSION['address'] : 'Aix-En-Provence'; ?>";
                const companyAddress = "<?php echo isset($_SESSION['selected_student']['address']) ? $_SESSION['selected_student']['address'] : 'Marseille'; ?>";
            </script>
        </main>
        <?php
    }
}

