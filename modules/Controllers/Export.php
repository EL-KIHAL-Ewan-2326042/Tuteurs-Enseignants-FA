<?php
namespace Blog\Controllers;

use Blog\Models\Model;
use Blog\Views\layout\Layout;
use Exception;

class Export
{
    private Layout $_layout;
    private Model $_model;

    public function __construct(Layout $layout, Model $model)
    {
        $this->_layout = $layout;
        $this->_model = $model;
    }

    public function processExport(array $post): void
    {
        // Gestion de l'exportation des fichiers CSV
        if (isset($post['export_list'])) {
            $tableName = $post['export_list'];
            if ($this->_model->isValidTable($tableName)) {
                try {
                    $headers = $this->_model->getTableColumn($tableName);
                    if ($tableName == 'teacher') {
                        $headers = array_merge(
                            $headers, ['address$type'], ['discipline_name']
                        );
                    }
                    $this->_model->exportToCsvByDepartment($tableName, $headers);
                } catch (Exception $e) {
                    $dashboardController = new Dashboard($this->_layout);
                    echo "Erreur lors de l'exportation : " .
                        $dashboardController->handleExceptionMessage($e);
                }
            } else {
                echo "Table inconnue pour l'export";
            }
        }
        // Gestion de l'exportation des modèles en CSV
        elseif (isset($post['export_model'])) {
            $tableName = $post['export_model'];
            if ($this->_model->isValidTable($tableName)) {
                try {
                    $this->_model->exportModel($tableName);
                } catch (Exception $e) {
                    $dashboardController = new Dashboard($this->_layout);
                    echo "Erreur lors de l'exportation : " .
                        $dashboardController->handleExceptionMessage($e);
                }
            }
        }
    }

    public function show(string $category = ''): void
    {
        $title = "Gestion des données - Export";
        $cssFilePath = '_assets/styles/gestionDonnees.css';
        $jsFilePath = '_assets/scripts/gestionDonnees.js';
        $view = new \Blog\Views\dashboard\Export($category);

        $this->_layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->_layout->renderBottom($jsFilePath);
    }
}