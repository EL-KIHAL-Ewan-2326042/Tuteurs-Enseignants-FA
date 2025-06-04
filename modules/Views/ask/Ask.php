<?php

namespace Blog\Views\ask;

use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Student;
use Blog\Models\Teacher;
use Blog\Views\components\Table;
use Blog\Views\components\ViewStage;

readonly class Ask
{
    public function __construct(
        private Internship $internshipModel,
        private Student $studentModel,
        private Teacher $teacherModel,
        private Department $departmentModel
    ) {}

    public function showView(): void
    {
        /* ---------- table principale ---------- */

        $headers = ['Demander', 'Élève', 'Position', 'Formation', 'Entreprise', 'Groupe', 'Historique', 'Sujet', 'Adresse'];

        $jsColumns = [
            ['data' => 'associate', 'orderable' => false, 'searchable' => false],
            ['data' => 'student'],
            ['data' => 'distance'],
            ['data' => 'formation'],
            ['data' => 'company'],
            ['data' => 'group'],
            ['data' => 'history'],
            ['data' => 'subject'],
            ['data' => 'address'],
            ['data' => 'internship_identifier'],
        ];

        $internshipId = $_GET['internship'] ?? null;
        $btnDisabled = $internshipId ? '' : 'disabled';
        $btnIcon = $internshipId ? 'apps' : 'assignment_ind';
        ?>
        <main>
            <div>
                <section>
                    <form id="tableContainer" method="post" <?= $internshipId ? 'style="display:none;"' : '' ?>>
                        <?php Table::render(
                            'homepage-table',
                            $headers,
                            $jsColumns,
                            '/api/datatable/ask'
                        ); ?>
                        <div id="askBtnCont">
                            <button class=" btn-annuler tooltip" type="reset"  data-tooltip="Annuler les modifications" data-position="top">Annuler</button>
                            <button class="btn-send" name="selecInternshipSubmitted" value="1" type="submit" data-tooltip="Envoyer vos choix" data-position="top">Valider</button>
                        </div>
                    </form>



                    <div id="viewStageContainer" <?= $internshipId ? '' : 'style="display:none;"' ?>>
                        <?php if ($internshipId) ViewStage::render($internshipId); ?>
                    </div>
                </section>

                <div>
                    <button id="toggleViewBtn" title="Basculer la vue" <?= $btnDisabled ?>>
                        <i class="material-icons" id="toggleIcon"><?= $btnIcon ?></i>
                    </button>
                    <section id="map"></section>
                </div>
            </div>
        </main>
        <script>
            console.log('<?= json_encode($_SESSION)  ?>');
            window.TEACHER_ID = '<?php echo $_SESSION['identifier']; ?>'
            window.JS_COLUMNS = <?= json_encode($jsColumns) ?>;
            window.TEACHER_ADDRESS = <?= json_encode(!empty($_SESSION['address'][0]['address']) ? $_SESSION['address'][0]['address'] : null) ?>;
        </script>
        <?php
        unset($_SESSION['selected_student']);
    }
}
