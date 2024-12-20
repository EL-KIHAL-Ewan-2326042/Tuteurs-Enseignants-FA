<?php
namespace Blog\Views;

/**
 * Vue du Dispatcher
 * @return void
 */
class Dispatcher {

    /**
     * @param \Blog\Models\Dispatcher $dispatcherModel
     * @param string $errorMessageAfterSort
     * @param string $errorMessageDirectAssoc
     * @param string $checkMessageDirectAssoc
     * @param string $checkMessageAfterSort
     */
    public function __construct(private readonly \Blog\Models\Dispatcher $dispatcherModel, private readonly string $errorMessageAfterSort,private readonly string $errorMessageDirectAssoc, private readonly string $checkMessageDirectAssoc,private readonly string $checkMessageAfterSort) {
    }

    public function showView(): void {
        ?>
        <main>
            <div class="col">
                <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

                <?php
                $coucou = $this->dispatcherModel->RelevanceInternship('V08756321AB', ['A été responsable' => 1]);
                print_r($coucou);
                ?>

                <?php if (!isset($_POST['action']) || $_POST['action'] !== 'generate'): ?>
                <div class="row" id="forms-section">
                    <div class="col card-panel white z-depth-3 s12 m6" style="padding: 20px; margin-right: 10px">
                        <form class="col s12" action="./dispatcher" method="post" onsubmit="showLoading();">
                            <?php
                            $saves = $this->dispatcherModel->showCoefficients();
                            if ($saves): ?>
                                <div class="input-field">
                                    <label for="save-selector"></label>
                                    <select id="save-selector" name="save-selector">
                                        <?php if (isset($_POST['save-selector']) && $_POST['save-selector'] !== 'new'):?>
                                            <option value='new'>Sauvegarde #<?= $_POST['save-selector']?></option>
                                        <?php else:?>
                                            <option value='default'>Choisir une sauvegarde</option>
                                        <?php endif;?>
                                        <?php foreach ($saves as $save): ?>
                                        <?php if (isset($_POST['save-selector']) && $save['id_backup'] == $_POST['save-selector']) {
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


                            <p class="red-text" id="checkboxError"><?php echo $this->errorMessageAfterSort; ?></p>
                            <p class="green-text"><?php echo $this->checkMessageAfterSort; ?></p>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action-save" value="<?= $id_backup ?>" id="save-btn">Enregister
                                <i class="material-icons right">arrow_downward</i>
                            </button>
                            <button class="btn waves-effect waves-light button-margin" type="submit" name="action" value="generate" id="generate-btn">Générer
                                <i class="material-icons right">send</i>
                            </button>
                        </form>
                    </div>

                    <form class="col card-panel white z-depth-3 s12 m5" style="padding: 20px;" action="./dispatcher" method="post" id="associate-form">
                        <div class="row">
                            <p class="text">Associe un professeur à un stage (ne prend pas en compte le nombre maximum d'étudiant, ni le fait que le stage soit déjà attribué)</p>
                            <div class="input-field col s6">
                                <input id="searchTeacher" name="searchTeacher" type="text" class="validate">
                                <label for="searchTeacher">ID professeur</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="searchInternship" name="searchInternship" type="text" class="validate">
                                <label for="searchInternship">ID Stage</label>
                            </div>
                            <div id="searchResults"></div>
                            <p class="red-text"><?php echo $this->errorMessageDirectAssoc; ?></p>
                            <p class="green-text"><?php echo $this->checkMessageDirectAssoc; ?></p>
                            <div class="col s12">
                                <button class="btn waves-effect waves-light button-margin" type="submit" name="action">Associer
                                    <i class="material-icons right">arrow_downward</i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="loading-section" class="center-align" style="display: none;">
                    <p style="font-size: 24px;">Chargement en cours, veuillez patienter...</p>
                    <div class="progress">
                        <div class="indeterminate"></div>
                    </div>
                </div>
                    <?php endif?>

                <?php
                function renderStars($score) {
                    $fullStars = floor($score);

                    $decimalPart = $score - $fullStars;

                    $halfStars = (abs($decimalPart - 0.5) <= 0.1) ? 1 : 0;

                    $emptyStars = 5 - $fullStars - $halfStars;

                    $stars = '';

                    for ($i = 0; $i < $fullStars; $i++) {
                        $stars .= '<span class="filled"></span>';
                    }

                    if ($halfStars) {
                        $stars .= '<span class="half"></span>';
                    }

                    for ($i = 0; $i < $emptyStars; $i++) {
                        $stars .= '<span class="empty"></span>';
                    }

                    return $stars;
                }
                ?>


                <?php if (isset($_POST['coef']) && isset($_POST['action']) && $_POST['action'] === 'generate'): ?>
                    <div class="row card-panel white z-depth-3 s12 m6">
                        <div class="col s12">
                            <form class="col s12" action="./dispatcher" method="post">
                                <div class="dispatch-table-wrapper selection">
                                    <table class="highlight centered" id="dispatch-table">
                                        <thead>
                                        <tr>
                                            <th>Enseignant</th>
                                            <th>Etudiant</th>
                                            <th>Stage</th>
                                            <th>Formation</th>
                                            <th>Groupe</th>
                                            <th>Date Expérience</th>
                                            <th>Sujet</th>
                                            <th>Adresse</th>
                                            <th>Score</th>
                                            <th>Associer</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $dictCoef = array_filter($_POST['coef'], function ($coef, $key) {
                                            return isset($_POST['criteria_enabled'][$key]);
                                        }, ARRAY_FILTER_USE_BOTH);

                                        if (!empty($dictCoef)) {
                                            $escapedJson = htmlspecialchars(json_encode($dictCoef), ENT_QUOTES);

                                            echo "<input type='hidden' id='dictCoefJson' value='" . $escapedJson . "'>";
                                        } else {
                                            header('location: ./dispatcher');
                                        }

                                        $resultDispatchList = $this->dispatcherModel->dispatcher($dictCoef)[0];
                                        foreach ($resultDispatchList as $resultDispatch):
                                            ?>
                                            <tr class="dispatch-row" data-internship-identifier='<?= $resultDispatch['internship_identifier'] . '$' . $resultDispatch['id_teacher']; ?>'>
                                                <td><?= $resultDispatch['teacher_firstname'] . ' ' . $resultDispatch['teacher_name'] . ' (' . $resultDispatch['id_teacher'] . ')'; ?></td>
                                                <td><?= $resultDispatch['student_firstname'] . ' ' . $resultDispatch['student_name'] . ' (' . $resultDispatch['student_number'] . ')' ?></td>
                                                <td><?= $resultDispatch['company_name'] . ' (' .$resultDispatch['internship_identifier'] . ')'; ?></td>
                                                <td><?= $resultDispatch['formation'] ?></td>
                                                <td><?= $resultDispatch['class_group'] ?></td>
                                                <td><?= 'dd/mm/yyyy' ?></td>
                                                <td><?=  $resultDispatch['internship_subject'] ?></td>
                                                <td><?= $resultDispatch['address'] ?></td>
                                                <td>
                                                    <div class="star-rating" data-tooltip="<?= $resultDispatch['score']; ?>" data-position="top">
                                                        <?php echo renderStars($resultDispatch['score']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="center">
                                                        <input type="checkbox" class="dispatch-checkbox center-align filled-in" id="listTupleAssociate[]" name="listTupleAssociate[]" value="<?= $resultDispatch['id_teacher'] . "$". $resultDispatch['internship_identifier'] . "$". $resultDispatch['score']; ?>" />
                                                        <span></span>
                                                    </label>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <br>

                                <div class="row">
                                    <div class="input-field col s12">
                                        <label for="rows-per-page"></label>
                                        <select id="rows-per-page">
                                            <option value="10" selected>10</option>
                                            <option value="20">20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="<?= count($resultDispatchList)?>">Tout</option>
                                        </select>
                                        <label>Nombre de lignes par page</label>
                                    </div>
                                </div>

                                <div id="pagination-controls" class="center-align">
                                    <button type="button" class="waves-effect waves-light btn" id="first-page"><i class="material-icons">first_page</i></button>
                                    <button type="button" class="waves-effect waves-light btn" id="prev-page"><i class="material-icons">arrow_back</i></button>
                                    <div id="page-numbers"></div>
                                    <button type="button" class="waves-effect waves-light btn" id="next-page"><i class="material-icons">arrow_forward</i></button>
                                    <button type="button" class="waves-effect waves-light btn" id="last-page"><i class="material-icons">last_page</i></button>
                                </div>

                                <div class="row s12 center">
                                    <input type="hidden" id="selectStudentSubmitted" name="selectStudentSubmitted" value="1">
                                    <button class="waves-effect waves-light btn" type="submit">Valider</button>
                                    <input type="hidden" name="restartDispatcherButton" value="1">
                                    <button class="waves-effect waves-light btn" type="submit">Recommencer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
<?php
    }
}