<?php

namespace Blog\Views;

/**
 * Vue du Dispatcher
 * @return void
 */
class Dispatcher{

    /**
     * @param \Blog\Models\Dispatcher $dispatcherModel
     */
    public function __construct(private readonly \Blog\Models\Dispatcher $dispatcherModel) {
    }
    public function showView(): void {
        ?>
        <main>
            <div class="row">
                <div class="col s12 m6" style="margin-bottom: 20px;">
                    <?php
                    $listCriteria = $this->dispatcherModel->getCriteria();
                    foreach ($listCriteria as $criteria) {
                        ?>
                        <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                            <!-- Adding margin-right for the checkbox container to create space -->
                            <div class="col s12 m6" style="flex: 1; margin-right: 20px;">
                                <p>
                                    <label>
                                        <input type="checkbox" class="filled-in" checked="checked" />
                                        <span><?php echo $criteria['name_criteria']; ?></span>
                                    </label>
                                </p>
                            </div>
                            <!-- The slider field -->
                            <div class="col s12 m6" style="flex: 1;">
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
                        <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Enregister
                            <i class="material-icons right">arrow_downward</i>
                        </button>
                        <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Générer
                            <i class="material-icons right">send</i>
                        </button>
                        <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Charger
                            <i class="material-icons right">arrow_upward</i>
                        </button>
                    </div>
                </div>

                <div class="col s12 m6" style="margin-top: 20px;">
                    <div class="row">
                        <div class="input-field col s6">
                            <input id="Id_teacher" type="text" class="validate">
                            <label for="Id_teacher">First Name</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="Student_number" type="text" class="validate">
                            <label for="Student_number">Last Name</label>
                        </div>
                    </div>

                    <div class="col s12">
                        <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associé
                            <i class="material-icons right">arrow_downward</i>
                        </button>
                    </div>
                </div>

                <div class="row s12">
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
                </div>
                <div class="row s6">
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
                <div class="col s6">
                    <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associé
                        <i class="material-icons right">arrow_downward</i>
                    </button>
                </div>
            </div>
        </main>
        <?php
    }
}