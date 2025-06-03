<?php

namespace Blog\Views\dispatcher;

use Blog\Views\components\CoefBackup;
use Blog\Views\components\Table;
use Blog\Views\components\ViewStage;
use Blog\Models\Internship;

class Dispatcher
{
    public function __construct(
        private Internship $internshipModel,
        private $userModel,
        private $teacherModel,
        private $departmentModel,
        private string $errorMessageAfterSort,
        private string $errorMessageDirectAssoc,
        private string $checkMessageDirectAssoc,
        private string $checkMessageAfterSort
    ) {}

    public function showView(): void
    {
        ?>
        <main>
            <?php
            $internshipId = $_GET['internship'] ?? null;
            $btnDisabled = $internshipId ? '' : 'disabled';
            $btnIcon = $internshipId ? 'apps' : 'assignment_ind';

            if (($_POST['action'] ?? '') !== 'generate') {
                CoefBackup::render(
                    $this->userModel,
                    $this->errorMessageAfterSort,
                    $this->checkMessageAfterSort
                );
            }

            if (isset($_POST['coef'], $_POST['action']) && $_POST['action'] === 'generate'):
                $_SESSION['last_dict_coef'] = array_filter($_POST['coef'], fn($coef, $key) =>
                isset($_POST['criteria_on'][$key]), ARRAY_FILTER_USE_BOTH);
                ?>
                <div class="partie2">
                    <div id="tableContainer" class="dataTable">
                        <form action="./dispatcher" method="post">
                            <?php
                            Table::render(
                                'dispatch-table',
                                ['Etudiant','Enseignant', 'Stage', 'Formation', 'Groupe', 'Sujet', 'Adresse', 'Score', 'internship_identifier', 'teacher_address', 'Associer'],
                                [
                                    ['data' => 'student'],
                                    ['data' => 'teacher'],
                                    ['data' => 'internship'],
                                    ['data' => 'formation'],
                                    ['data' => 'group'],
                                    ['data' => 'subject'],
                                    ['data' => 'address'],
                                    ['data' => 'score'],
                                    ['data' => 'internship_identifier'],
                                    ['data' => 'teacher_address'],
                                    ['data' => 'associate']
                                ],
                                '/api/dispatch-list'
                            );
                            ?>
                            <button type="submit" name="selecInternshipSubmitted" value="1" class="btn">Valider</button>
                        </form>
                    </div>

                    <div id="viewStageContainer" class="dataTable" <?= $internshipId ? '' : 'style="display:none;"' ?>>
                        <?php if ($internshipId) ViewStage::render($internshipId); ?>
                    </div>

                    <div class="cont-map">
                        <button id="toggleViewBtn" title="Basculer la vue" <?= $btnDisabled ?>>
                            <i class="material-icons" id="toggleIcon"><?= $btnIcon ?></i>
                        </button>
                        <section id="map"></section>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <?php
    }
}