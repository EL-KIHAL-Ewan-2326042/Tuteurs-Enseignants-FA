<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Blog\Views\Dashboard as DashboardView;
use Exception;

class Dashboard {
    private Layout $layout;
    private DashboardView $view;

    /**
     * Constructeur de la classe Dashboard
     * @param Layout $layout Instance de la classe Layout
     * @param DashboardView $view Instance de la classe DashboardView
     */
    public function __construct(Layout $layout, DashboardView $view) {
        $this->layout = $layout;
        $this->view = $view;
    }

    /**
     * Contrôleur de la Dashboard
     * @return void
     * @throws Exception
     */
    public function show(): void {
        $title = "Dashboard";
        $cssFilePath = '_assets/styles/dashboard.css';
        $jsFilePath = '';
        $db = \Includes\Database::getInstance();
        $model = new \Blog\Models\Dashboard($db);

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_FILES['csv_file_student'])){
                $csvFile = $_FILES['csv_file_student']['tmp_name'];
                $model->uploadCsvStudent($csvFile);
            } elseif (isset($_FILES['csv_file_teacher'])){
                $csvFile = $_FILES['csv_file_teacher']['tmp_name'];
                $model->uploadCsvTeacher($csvFile);
            } elseif (isset($_FILES['csv_file_internship'])){
                $csvFile = $_FILES['csv_file_internship']['tmp_name'];
                $model->uploadCsvInternship($csvFile);
            } elseif (isset($_POST['export_table'])) {
                $table = $_POST['export_table'];

                //en-têtes spécifiques de chaque table
                $headers = match ($table) {
                    'student' => ['student_number','student_name','student_firstname','formation','class_group'],
                    'teacher' => ['id_teacher','teacher_name','teacher_firstname','maxi_number_trainees'],
                    'internship' => ['internship_identifier','company_name','keywords','start_date_internship','type','end_date_internship','internship_subject','address','student_number'],
                    'teaches' => ['id_teacher','department_name'],
                    'department' => ['department_name','address'],
                    'study_at' => ['student_number','department_name'],
                    'is_requested' => ['id_teacher','student_number'],
                    'is_taught' => ['id_teacher','discipline_name'],
                    'is_responsible' => ['id_teacher','student_number','distance_minute','relevance_score','responsible_start_date','responsible_end_date'],
                    'discipline' => ['discipline_name'],
                    'address_type' => ['type'],
                    'addr_name' => ['address'],
                    'has_address' => ['id_teacher','address','type'],
                    'has_role' => ['user_id','role_name','role_department'],
                    'role' => ['role_name'],
                    'user_connect' => ['user_id','user_pass'],
                    'backup' => ['user_id','name_criteria','coef','num_backup'],
                    'distribution_criteria' => ['name_criteria'],
                    default => null
                };

                if($headers === null) {
                    echo "Table inconnue pour l'export.";
                    return;
                }
                $model->exportToCsvByDepartment($table, $headers);

            } else {
                echo "Aucun fichier CSV n'est reconnu.";
            }
        }

        $this->layout->renderTop($title, $cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}