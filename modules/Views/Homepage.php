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
                <div class="row">
                    <div class="col s2">ELEVE</div>
                    <div class="col s2">HISTORIQUE</div>
                    <div class="col s1">POSITION</div>
                    <div class="col s2">SUJET</div>
                    <div class="col s2">ENTREPRISE</div>
                    <div class="col s2">TOTAL</div>
                    <div class="col s1">CHOIX</div>

                    <?
                    foreach($this->model->getEleves(5) as $eleve) {
                        ?>
                            <div class="col s2"><? echo $eleve["nom_eleve"] . " " . $eleve["prenom_eleve"] ?></div>
                            <div class="col s2">...</div>
                            <div class="col s1">...<? /*echo $this->model->getAdresseEleve($eleve["num_eleve"])["adresse_entreprise"]*/ ?></div>
                            <div class="col s2">...<? /*echo $this->model->getAdresseEleve($eleve["num_eleve"])["sujet_stage"]*/ ?></div>
                            <div class="col s2">...<? /*echo $this->model->getAdresseEleve($eleve["num_eleve"])["nom_entreprise"]*/ ?></div>
                            <div class="col s2">...</div>
                            <div class="col s1">...</div>
                        <?
                    }
                    ?>
                </div>
            </main>
        <?php
    }
}