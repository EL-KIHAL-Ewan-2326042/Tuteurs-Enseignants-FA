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

        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['csv_file'])) {
            $csvFile = $_FILES['csv_file']['tmp_name'];
            $db = \Includes\Database::getInstance();
            $model = new \Blog\Models\Dashboard($db);
            $model->uploadCsv($csvFile);
        }

        $this->layout->renderTop($title, $cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}