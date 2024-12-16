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
     * @param string $errorMessage2
     */
    public function __construct(private readonly \Blog\Models\Dispatcher $dispatcherModel, private readonly string $errorMessage1,private readonly string $errorMessage2) {
    }

    public function showView(): void {
        ?>
        <main>
            <div class="col">
                <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

                <?php if (!isset($_POST['action']) || $_POST['action'] !== 'generate'): ?>
                <div class="row" id="forms-section">
                    <div class="col card-panel white z-depth-3 s12 m6" style="padding: 20px; margin-right: 10px">
                        <form class="col s12" action="./dispatcher" method="post" id="pushCoef" onsubmit="showLoading();">
                            <?php
                            $saves = $this->dispatcherModel->showCoefficients();
                            if ($saves): ?>
                                <div class="input-field">
                                    <select id="save-selector" name="save-selector">
                                        <?php if (isset($_POST['save-selector']) && $_POST['save-selector'] !== 'new'):?>
                                            <option value='new'>Sauvegarde #<?= $_POST['save-selector']?></option>
                                        <?php else:?>
                                            <option value='new'>Choisir une sauvegarde</option>
                                        <?php endif;?>
                                        <?php foreach ($saves as $save): ?>
                                        <?php if ($save['id_backup'] == $_POST['save-selector']) {
                                            continue;
                                            }?>
                                            <option value="<?php echo $save['id_backup']; ?>">
                                                Sauvegarde #<?= $save['id_backup']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                    <?php endif; ?>


                            <?php
                            $id_backup = $_POST['save-selector'] ?? 'default';

                            if ($id_backup === 'default' || $id_backup === 'new') {
                                $defaultCriteria = $this->dispatcherModel->getDefaultCoef();
                                $listCriteria = [];

                                foreach ($defaultCriteria as $key => $value) {
                                    $listCriteria[$key] = $value;
                                }
                            } else {
                                $listCriteria = $this->dispatcherModel->loadCoefficients($_SESSION['identifier'], $id_backup);
                            }
                            ?>

                            <?php foreach ($listCriteria as $criteria): ?>
                                <div class="row">
                                    <div class="col s6">
                                        <p>
                                            <label>
                                                <input type="hidden" name="is_checked[<?php echo $criteria['name_criteria']; ?>]" value="0">
                                                <input type="checkbox" class="filled-in criteria-checkbox"
                                                       name="criteria_enabled[<?php echo $criteria['name_criteria']; ?>]"
                                                       data-coef-input-id="<?php echo $criteria['name_criteria']; ?>"
                                                       <?php if ($criteria['is_checked']): ?>checked="checked"<?php endif; ?> />
                                                <span><?= $criteria['name_criteria']; ?></span>
                                            </label>
                                        </p>
                                    </div>
                                    <div class="col s6">
                                        <div class="input-field">
                                            <input type="number" name="coef[<?= $criteria['name_criteria']; ?>]" id="<?= $criteria['name_criteria']; ?>"
                                                   min="1" max="100" value="<?= $criteria['coef']; ?>" />
                                            <label for="<?= $criteria['name_criteria']; ?>">Coefficient</label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>


                            <p class="red-text"><?php echo $this->errorMessage2; ?></p>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action-save" value="<?= $id_backup ?>">Enregister
                                <i class="material-icons right">arrow_downward</i>
                            </button>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action" value="generate" id="generate-btn">Générer
                                <i class="material-icons right">send</i>
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
                    <?php endif?>

                <?php if (isset($_POST['action']) && $_POST['action'] === 'generate'): ?>
                    <div class="row card-panel white z-depth-3 s12 m6">
                        <div class="col s12">
                            <form class="col s12" action="./dispatcher" method="post">
                                <div class="selection">
                                    <table class="highlight centered" id="dispatch-table">
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
                                        if (!isset($_POST['coef'])) {
                                            header('location: ./dispatcher');
                                        }

                                        $dictCoef = array_filter($_POST['coef'], function ($coef, $key) {
                                            return isset($_POST['criteria_enabled'][$key]);
                                        }, ARRAY_FILTER_USE_BOTH);

                                        if (empty($dictCoef)) {
                                            header('location: ./dispatcher');
                                        }

                                        $resultDispatchList = $this->dispatcherModel->dispatcher($dictCoef)[0];
                                        foreach ($resultDispatchList as $resultDispatch):
                                            ?>
                                            <tr class="dispatch-row">
                                                <td><?= $resultDispatch['id_teacher']; ?></td>
                                                <td><?= $resultDispatch['internship_identifier']; ?></td>
                                                <td><strong><?= $resultDispatch['score']; ?></strong>/5</td>
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

                                <br>

                                <div id="pagination-controls" class="center-align">
                                    <button type="button" class="waves-effect waves-light btn" id="prev-page"><i class="material-icons">arrow_back</i></button>
                                    <span id="page-number">Page 1</span>
                                    <button type="button" class="waves-effect waves-light btn" id="next-page"><i class="material-icons">arrow_forward</i></button>
                                </div>

                                <br>

                                <div class="row s12 center">
                                    <input type="hidden" id="selectStudentSubmitted" name="selectStudentSubmitted" value="1">
                                    <button class="waves-effect waves-light btn" type="submit">Valider</button>
                                    <input type="hidden" name="restartDispatcherButton" value="1">
                                    <button class="waves-effect waves-light btn" type="submit">Recommencer</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const rowsPerPage = 10;
                            const rows = document.querySelectorAll('.dispatch-row');
                            const totalRows = rows.length;
                            const totalPages = Math.ceil(totalRows / rowsPerPage);
                            let currentPage = 1;

                            const prevButton = document.getElementById('prev-page');
                            const nextButton = document.getElementById('next-page');
                            const pageNumberSpan = document.getElementById('page-number');

                            function showPage(page) {
                                if (page < 1 || page > totalPages) return;

                                currentPage = page;
                                pageNumberSpan.textContent = `Page ${currentPage}`;

                                rows.forEach((row, index) => {
                                    const start = (currentPage - 1) * rowsPerPage;
                                    const end = currentPage * rowsPerPage;
                                    if (index >= start && index < end) {
                                        row.style.display = '';
                                    } else {
                                        row.style.display = 'none';
                                    }
                                });

                                prevButton.disabled = currentPage === 1;
                                nextButton.disabled = currentPage === totalPages;
                            }

                            prevButton.addEventListener('click', () => {
                                showPage(currentPage - 1);
                            });

                            nextButton.addEventListener('click', () => {
                                showPage(currentPage + 1);
                            });

                            showPage(1);
                        });
                    </script>
                <?php endif; ?>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const selects = document.querySelectorAll('select');
                    M.FormSelect.init(selects);

                    const saveSelector = document.getElementById('save-selector');
                    if (saveSelector) {
                        saveSelector.addEventListener('change', function () {
                            const form = this.closest('form');
                            form.submit();
                        });
                    }

                    const checkboxes = document.querySelectorAll('.criteria-checkbox');

                    checkboxes.forEach(checkbox => {
                        const hiddenInput = document.querySelector(`input[name="is_checked[${checkbox.dataset.coefInputId}]"]`);

                        if (checkbox.checked) {
                            hiddenInput.value = '1';
                        } else {
                            hiddenInput.value = '0';
                        }

                        checkbox.addEventListener('change', function () {
                            if (this.checked) {
                                hiddenInput.value = '1';
                            } else {
                                hiddenInput.value = '0';
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

                document.querySelectorAll('.criteria-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        const hiddenInput = document.querySelector(`input[name="is_checked[${this.dataset.coefInputId}]"]`);
                        hiddenInput.value = this.checked ? '1' : '0';
                    });
                });

                function showLoading() {
                    const loadingSection = document.getElementById('loading-section');
                    const formsSection = document.getElementById('forms-section');

                    if (loadingSection && formsSection) {
                        loadingSection.style.display = 'block';
                        formsSection.style.display = 'none';
                    }
                }
            </script>
        </main>
<?php
    }
}