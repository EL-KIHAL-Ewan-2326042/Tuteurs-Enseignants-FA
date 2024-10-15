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

            <div class="row">
                <div class="col s2">ELEVE</div>
                <div class="col s2">HISTORIQUE</div>
                <div class="col s1">POSITION</div>
                <div class="col s2">SUJET</div>
                <div class="col s2">ENTREPRISE</div>
                <div class="col s2">TOTAL</div>
                <div class="col s1">CHOIX</div>
            </div>

            <?
            foreach($this->model->getEleves(5, $_SESSION['identifier']) as $eleve) {
                $infoStage = $this->model->getStageEleve($eleve["num_eleve"])
                ?>
                    <div class="row">
                        <div class="col s2"><? echo $eleve["nom_eleve"] . " " . $eleve["prenom_eleve"] ?></div>
                        <div class="col s2">...</div>
                        <div class="col s1"> <? if(!$infoStage) echo "...";
                                                else echo $infoStage["adresse_entreprise"] ?> </div>
                        <div class="col s2"> <? if(!$infoStage) echo "...";
                                                else echo str_replace('_', ' ', $infoStage["sujet_stage"]) ?> </div>
                        <div class="col s2"> <? if(!$infoStage) echo "...";
                                                else echo $infoStage["nom_entreprise"] ?> </div>
                        <div class="col s2">...</div>
                        <div class="col s1">...</div>
                    </div>
                <?
            }
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