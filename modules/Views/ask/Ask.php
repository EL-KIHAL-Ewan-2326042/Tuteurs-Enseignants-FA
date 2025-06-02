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
        private Student    $studentModel,
        private Teacher    $teacherModel,
        private Department $departmentModel
    ) { }

    public function showView(): void
    {
        /* ---------- table principale ---------- */

        $headers = ['Élève', 'Position','Formation', 'Entreprise', 'Groupe',
            'Historique', 'Sujet', 'Adresse'];

        $jsColumns = [
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


                    <!-- ---------- TABLE ---------- -->
                    <div id="tableContainer" <?= $internshipId ? 'style="display:none;"' : '' ?>>
                        <?php Table::render(
                            'homepage-table',
                            $headers,
                            $jsColumns,
                            '/api/datatable/ask'
                        ); ?>
                        <button class="waves-effect waves-light
            btn btn-annuler tooltip"
                                type="reset" id="resetForm" data-tooltip=
                                "Annuler les modifications"
                                data-position="top">Annuler</button>
                        <button class="waves-effect waves-light btn tooltip"
                                name="selecInternshipSubmitted" value="1"
                                type="submit" data-tooltip="Envoyer vos choix"
                                data-position="top">Valider</button>
                    </div>

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
            window.JS_COLUMNS = <?= json_encode($jsColumns) ?>;
            window.TEACHER_ADDRESS = <?= json_encode(!empty($_SESSION['address'][0]['address']) ? $_SESSION['address'][0]['address'] : null) ?>;
        </script>
        <?php
        unset($_SESSION['selected_student']);
    }
}
