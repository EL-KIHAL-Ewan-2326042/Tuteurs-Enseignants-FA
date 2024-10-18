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
                <form id="searchForm" onsubmit="return false;" method="POST">
                    <label for="search">Rechercher un étudiant:</label>
                    <input type="text" id="search" name="search" autocomplete="off" maxlength="50" required>
                    <div id="searchResults"></div>
                </form>
            </div>
            <div class="center">
                <?php if (isset($_SESSION['selected_student']['address']) && $_SESSION['selected_student']['address'] != '') {
                    echo '<h4 class="left-align"> Résultat pour: ' . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . '</h4>';
                ?>
            </div>
            <div id="map"></div>
            <?php
            } else {
                echo "<p>Cet étudiant n'a pas de stage ...</p>";
            }
            ?>

            <h4 class="left-align">Sélectionnez le(s) département(s) :</h4>

            <div class="row"></div>

            <form method="post" class="center-align">
                <div class="selection">
                    <?
                    foreach($this->model->getDepEnseignant() as $dep): ?>
                    <label class="formCell">
                        <input type="checkbox" id="selecDep[]" name="selecDep[]" class="filled-in" value="<?= $dep['nom_departement'] ?>" <? if(isset($_POST['selecDep']) && in_array($dep['nom_departement'], $_POST['selecDep'])): ?> checked="checked" <? endif; ?> />
                        <span><? echo str_replace('_', ' ', $dep['nom_departement']) ?></span>
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
                                <td><? echo $eleve["nom_eleve"] . " " . $eleve["prenom_eleve"] ?></td>
                                <td><? echo $eleve["internshipTeacher"] ?></td>
                                <td> <? echo str_replace('_', "'", $eleve["adresse_entreprise"]) ?> </td>
                                <td> <? echo str_replace('_', ' ', $eleve["sujet_stage"]) ?> </td>
                                <td> <? echo $eleve["nom_entreprise"] ?> </td>
                                <td><? echo "<strong>" . round($eleve['relevance'], 2) . "</strong>/5" ?></td>
                                <td>
                                    <label>
                                        <input type="checkbox" id="selecStudent[]" name="selecStudent[]" class="center-align filled-in" value="<?= $eleve['num_eleve'] ?>" />
                                        <span></span>
                                    </label>
                                </td>
                            </tr>
                        <? endforeach; ?>
                        </tbody>
                    </table>
                </form>
            <? endif; ?>

            <script>
                const teacherAddress = "<?php echo isset($_SESSION['address']) ? $_SESSION['address'] : 'Aix-En-Provence'; ?>";
                const companyAddress = "<?php echo isset($_SESSION['selected_student']['address']) ? $_SESSION['selected_student']['address'] : 'Marseille'; ?>";
            </script>
        </main>
        <?php
    }
}

