<?php
namespace Blog\Views;

/**
 * Vue du Dispatcher
 * @return void
 */
class Dispatcher {

    /**
     * @param \Blog\Models\Dispatcher $dispatcherModel
     * @param string $errorMessage
     */
    public function __construct(private readonly \Blog\Models\Dispatcher $dispatcherModel, private readonly string $errorMessage1,private readonly string $errorMessage2) {
    }

    public function showView(): void {
        ?>
        <main>
            <div class="col">
                <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

                <div class="row" id="forms-section">
                    <div class="col card-panel white z-depth-3 s12 m6" style="padding: 20px; margin-right: 10px">
                        <form class="col s12" action="./dispatcher" method="post" id="pushCoef">
                            <?php
                            $listCriteria = $this->dispatcherModel->getCriteria();
                            foreach ($listCriteria as $criteria) {
                                ?>
                                <div class="row">
                                    <div class="col s6">
                                        <p>
                                            <label>
                                                <input type="checkbox" class="filled-in criteria-checkbox"
                                                       name="criteria_enabled[<?php echo $criteria['name_criteria']; ?>]"
                                                       data-coef-input-id="<?php echo $criteria['name_criteria']; ?>"
                                                       checked="checked" />
                                                <span><?php echo $criteria['name_criteria']; ?></span>
                                            </label>
                                        </p>
                                    </div>
                                    <div class="col s6">
                                        <div class="input-field" style="margin: 0;">
                                            <input type="number" name="coef[<?php echo $criteria['name_criteria']; ?>]"
                                                   id="<?php echo $criteria['name_criteria']; ?>"
                                                   min="0" max="100"
                                                   value="<?php echo $criteria['coef']; ?>"
                                                   class="coef-input">
                                            <label for="<?php echo $criteria['name_criteria']; ?>">Coefficient</label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <p class="red-text"><?php echo $this->errorMessage2; ?></p>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action" value="save">Enregister
                                <i class="material-icons right">arrow_downward</i>
                            </button>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action" value="generate" id="generate-btn">Générer
                                <i class="material-icons right">send</i>
                            </button>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action" value="load">Charger
                                <i class="material-icons right">arrow_upward</i>
                            </button>
                        </form>
                    </div>

                    <form class="col card-panel white z-depth-3 s12 m5" style="padding: 20px;" action="./dispatcher" method="post" id="associate-form">
                        <div class="row">
                            <div class="input-field col s6">
                                <input id="Id_teacher" name="Id_teacher" type="text" class="validate">
                                <label for="Id_teacher">Id_teacher</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="Internship_identifier" name="Internship_identifier" type="text" class="validate">
                                <label for="Internship_identifier">Internship_identifier</label>
                            </div>
                            <p class="red-text"><?php echo $this->errorMessage1; ?></p>
                            <div class="col s12">
                                <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associer
                                    <i class="material-icons right">arrow_downward</i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="loading-section" class="center-align" style="display: none;">
                    <p>Chargement en cours, veuillez patienter...</p>
                </div>

                <?php if (isset($_POST['action']) && $_POST['action'] === 'generate'): ?>
                    <div class="row card-panel white z-depth-3 s12 m6">
                        <div class="col s12">
                            <form class="col s12" action="./dispatcher" method="post">
                                <div class="selection">
                                    <table class="highlight centered">
                                        <thead>
                                        <tr>
                                            <th>Enseignant</th>
                                            <th>N° Stage</th>
                                            <th>Score</th>
                                            <th>Associer</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $dictCoef = $_POST['coef'];
                                        $resultDispatchList = $this->dispatcherModel->dispatcher($dictCoef)[0];
                                        foreach ($resultDispatchList as $resultDispatch):
                                            ?>
                                            <tr>
                                                <td><?= $resultDispatch['id_teacher']; ?></td>
                                                <td><?= $resultDispatch['internship_identifier']; ?></td>
                                                <td><?= $resultDispatch['score']; ?></td>
                                                <td>
                                                    <label class="center">
                                                        <input type="checkbox" id="listTupleAssociate[]" name="listTupleAssociate[]" class="center-align filled-in" value="<?= $resultDispatch['id_teacher'] . "$". $resultDispatch['internship_identifier'] . "$". $resultDispatch['score']; ?>" />
                                                        <span></span>
                                                    </label>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row s12 center">
                                    <input type="hidden" id=selectStudentSubmitted" name="selectStudentSubmitted" value="1">
                                    <button class="waves-effect waves-light btn" type="submit">Valider</button>
                                    <span> Tout cocher :</span>
                                    <p>
                                        <label>
                                            <input type="checkbox" class="filled-in" checked="checked" />
                                            <span></span>
                                        </label>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkboxes = document.querySelectorAll('.criteria-checkbox');

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        const coefInput = document.getElementById(this.dataset.coefInputId);
                        if (this.checked) {
                            coefInput.removeAttribute('disabled');
                        } else {
                            coefInput.setAttribute('disabled', 'disabled');
                        }
                    });
                });

                document.querySelectorAll('.coef-input').forEach(input => {
                    input.addEventListener('input', function () {
                        const value = parseInt(this.value);
                        if (value > 100) {
                            this.value = 100;
                        } else if (value < 0) {
                            this.value = 0;
                        }
                    });
                });
            });
        </script>


        <?php
    }
}
