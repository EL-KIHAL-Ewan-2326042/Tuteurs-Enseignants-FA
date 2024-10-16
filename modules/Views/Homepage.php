<?php
namespace Blog\Views;
use Database;

class Homepage {

    public function __construct(private readonly \Blog\Models\Homepage $model) { }

    /**
     * Vue de la homepage
     * @return void
     */
    public function showView() {
        ?>
            <main>
                <div> contenu </div>
        <?
        if(isset($_SESSION['identifier']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
        ?>
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
            foreach($this->model->getEleves(20, $_SESSION['identifier']) as $eleve) {
                $infoStage = $this->model->getStageEleve($eleve["num_eleve"])
                ?>
                    <tr>
                        <td><? echo $eleve["nom_eleve"] . " " . $eleve["prenom_eleve"] ?></td>
                        <td>...</td>
                        <td> <? if(!$infoStage) echo "...";
                                                else echo $infoStage["adresse_entreprise"] ?> </td>
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
            <?
            /*
            $duree = 10;
            $coeffDuree = 7;
            $asso = 1;
            $coeffAsso = 2;
            $scorePert = 0.728;
            $coeffPert = 10;
            $nbAssoEleve = 2;

            echo "  --------------  <strong>" . $this->model->calculScore($duree, $coeffDuree, $asso, $coeffAsso, $scorePert, $coeffPert, $nbAssoEleve) . "</strong>/5"
            */
        }
        ?>
        </main>
    <?php
    }
}