<?php
namespace Blog\Views;

class Homepage {

    public function __construct(private readonly \Blog\Models\Homepage $model) { }

    /**
     * Vue de la homepage
     * @return void
     */
    public function showView() {
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

            <div class="row"></div>

            <table class="highlight centered center-align">
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
                foreach($this->model->getEleves(20, $_SESSION['identifier']) as $eleve) {
                    $infoStage = $this->model->getStageEleve($eleve["num_eleve"])
                    ?>
                    <tr>
                        <td><? echo $eleve["nom_eleve"] . " " . $eleve["prenom_eleve"] ?></td>
                        <td><? echo $this->model->getNbAsso($eleve["num_eleve"], $_SESSION['identifier']) ?></td>
                        <td> <? if(!$infoStage) echo "...";
                            else echo str_replace('_', "'", $infoStage["adresse_entreprise"]) ?> </td>
                        <td> <? if(!$infoStage) echo "...";
                            else echo str_replace('_', ' ', $infoStage["sujet_stage"]) ?> </td>
                        <td> <? if(!$infoStage) echo "...";
                            else echo $infoStage["nom_entreprise"] ?> </td>
                        <td>...</td>
                        <td>...</td>
                    </tr>
                    <?
                }
                ?>
                </tbody>
            </table>

            <script>
                const teacherAddress = "<?php echo isset($_SESSION['address']) ? $_SESSION['address'] : 'Aix-En-Provence'; ?>";
                const companyAddress = "<?php echo isset($_SESSION['selected_student']['address']) ? $_SESSION['selected_student']['address'] : 'Marseille'; ?>";
            </script>
        </main>
        <?php
    }
}

