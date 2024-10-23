<?php

namespace Blog\Views;

/**
 * Vue du Dispatcher
 * @return void
 */
class Dispatcher{

    /**
     * @param \Blog\Models\Dispatcher $dispatcherModel
     * @param string $errorMessage
     */
    public function __construct(private readonly \Blog\Models\Dispatcher $dispatcherModel, private readonly string $errorMessage) {
    }
    public function showView(): void {
        ?>
        <main>
            <div class="col">
                <div class="row" >
                    <div class="col card-panel white z-depth-3 s12 m6" style="padding: 20px; margin-right: 10px">
                        <?php
                        $listCriteria = $this->dispatcherModel->getCriteria();
                        foreach ($listCriteria as $criteria) {
                            ?>
                            <div class="row">
                                <div class="col s6">
                                    <p>
                                        <label>
                                            <input type="checkbox" class="filled-in" checked="checked" />
                                            <span><?php echo $criteria['name_criteria']; ?></span>
                                        </label>
                                    </p>
                                </div>
                                <div class="col s6">
                                    <form action="#">
                                        <p class="range-field" style="margin: 0;">
                                            <input type="range" id="<?php echo $criteria['name_criteria']; ?>" min="0" max="5" value="<?php echo $criteria['coef']; ?>" />
                                        </p>
                                    </form>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                    <div class="col s12">
                        <form action="./dispatcher" method="post" id="pushCoef">
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Enregister
                                <i class="material-icons right">arrow_downward</i>
                            </button>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Générer
                                <i class="material-icons right">send</i>
                            </button>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Charger
                                <i class="material-icons right">arrow_upward</i>
                            </button>
                        </form>
                    </div>
                </div>
                <form action="./dispatcher" method="post" id="">
                    <div class="col card-panel white z-depth-3 s12 m5" style="padding: 20px;">
                        <div class="row">
                            <div class="input-field col s6">
                                <input id="Id_teacher" name= "Id_teacher" type="text" class="validate">
                                <label for="Id_teacher">Id_teacher</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="Student_number" name="Student_number" type="text" class="validate">
                                <label for="Student_number">Student_number</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s6">
                                <input id="Start_date" name="Start_date" type="text" class="validate">
                                <label for="Start_date">Date de début</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="End_date" name="End_date" type="text" class="validate">
                                <label for="End_date">Date de fin</label>
                            </div>
                        </div>
                        <p class="red-text"><?php echo $this->errorMessage?></p>
                        <div class="col s12">
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associé
                                <i class="material-icons right">arrow_downward</i>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="row card-panel white z-depth-3 s12 m6">
                    <div class="col s12">
                        <table>
                            <thead>
                            <tr>
                                <th>Enseignant</th>
                                <th>Eleve</th>
                                <th>Score</th>
                                <th>Associé</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr>
                                <td>Alvin</td>
                                <td>Eclair</td>
                                <td>$0.87</td>
                            </tr>
                            <tr>
                                <td>Alan</td>
                                <td>Jellybean</td>
                                <td>$3.76</td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col s6">
                                <span> Tout cocher :</span>
                            </div>
                            <div class="col s6">
                                <p>
                                    <label>
                                        <input type="checkbox" class="filled-in" checked="checked" />
                                        <span></span>
                                    </label>
                                </p>
                            </div>
                        </div>

                        <div class="col s12">
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associé
                                <i class="material-icons right">arrow_downward</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php
    }
}