<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;
use Blog\Views\Dashboard as DashboardView;
use Blog\Models\Dashboard as DashboardModel;
use Exception;

class Dashboard {
    private Layout $layout;

    /**
     * Constructeur de la classe Dashboard
     * @param Layout $layout Instance de la classe Layout
     */
    public function __construct(Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * Contrôleur de la Dashboard
     * @return void
     */
    public function show(): void {
        $title = "Dashboard";
        $cssFilePath = '_assets/styles/dashboard.css';
        $jsFilePath = '_assets/scripts/dashboard.js';
        $db = \Includes\Database::getInstance();
        $model = new \Blog\Models\Dashboard($db);
        $view = new \Blog\Views\Dashboard();


        if (isset($_SESSION['role_name']) && (
                (is_array($_SESSION['role_name']) && in_array('Admin_dep', $_SESSION['role_name'])) ||
                ($_SESSION['role_name'] === 'Admin_dep'))) {

            if($_SERVER["REQUEST_METHOD"] == "POST") {
                if (isset($_FILES['csv_file_student'])) {
                    $csvFile = $_FILES['csv_file_student']['tmp_name'];
                    $tableName = $_POST['table_name'];

                    if ($tableName && $model->isValidTable($tableName)) {
                        try {
                            $csvHeaders = $model->getCsvHeaders($csvFile);
                            if(!$model->validateHeaders($csvHeaders,$tableName)) {
                                echo "Les en-têtes du fichier CSV ne correspondent pas à la structure de la table $tableName.";
                                return;
                            }

                            if (!$model->uploadCsv($csvFile,$tableName)) {
                                echo "Échec de l'importation du fichier CSV pour les étudiants.";
                            }
                        } catch (Exception $e) {
                            echo "Erreur lors de l'importation : " . $e->getMessage();
                        }
                    } else {
                        echo "Table non valide ou non reconnue.";
                    }
                } elseif (isset($_POST['export_list'])) {
                    $tableName = $_POST['export_list'];

                    if ($model->isValidTable($tableName)) {
                        try {
                            $headers = $model->getTableColumn($tableName);
                            $model->exportToCsvByDepartment($tableName, $headers);
                        } catch (Exception $e) {
                            echo "Erreur lors de l'exportation : " . $e->getMessage();
                        }
                    } else {
                        echo "Table inconnue pour l'export";
                    }
                } else {
                    echo "Aucun fichier CSV n'est reconnu.";
                }
            }

            $this->layout->renderTop($title, $cssFilePath);
            $view->showView();
            $this->layout->renderBottom($jsFilePath);
        }

        else {
            header('Location: /homepage');
        }
    }
}