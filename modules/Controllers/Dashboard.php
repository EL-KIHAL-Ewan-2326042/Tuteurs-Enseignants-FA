<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Blog\Views\Dashboard as DashboardView;

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
     * Contrôleur de la dashboard
     * @return void
     */
    public function show(): void {
        $title = "Dashboard";
        $cssFilePath = '_assets/styles/dashboard.css';
        $jsFilePath = '';

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $db = \Includes\Database::getInstance();
            $model = new \Blog\Models\Dashboard($db);

            if (isset($_FILES['csv_file_student'])){
                $csvFile = $_FILES['csv_file_student']['tmp_name'];
                $model->uploadCsvStudent($csvFile);
            } elseif (isset($_FILES['csv_file_teacher'])){
                $csvFile = $_FILES['csv_file_teacher']['tmp_name'];
                $model->uploadCsvTeacher($csvFile);
            } elseif (isset($_FILES['csv_file_internship'])){
                $csvFile = $_FILES['csv_file_internship']['tmp_name'];
                $model->uploadCsvInternship($csvFile);
            } else {
                echo "Aucun fichier CSV n'est reconnu.";
            }
        }

        $this->layout->renderTop($title, $cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}