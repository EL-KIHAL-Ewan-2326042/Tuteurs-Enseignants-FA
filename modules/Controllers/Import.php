<?php
namespace Blog\Controllers;

use Blog\Models\Model;
use Blog\Views\layout\Layout;
use Exception;

class Import
{
    private Layout $_layout;
    private Model $_model;

    public function __construct(Layout $layout, Model $model)
    {
        $this->_layout = $layout;
        $this->_model = $model;
    }

    public function processImport(array $post, array $files): array
    {
        $message = '';
        $errorMessage = '';
        $tableName = $post['table_name'] ?? null;

        if ($tableName && $this->_model->isValidTable($tableName)) {
            try {
                $csvFile = null;

                // Détection du fichier importé
                if (isset($files['student'])) {
                    $csvFile = $files['student']['tmp_name'];
                    $importType = 'student';
                } elseif (isset($files['teacher'])) {
                    $csvFile = $files['teacher']['tmp_name'];
                    $importType = 'teacher';
                } elseif (isset($files['internship'])) {
                    $csvFile = $files['internship']['tmp_name'];
                    $importType = 'internship';
                } else {
                    $errorMessage = "Aucun fichier CSV valide détecté";
                    return [$message, $errorMessage];
                }

                if ($csvFile) {
                    // Validation du fichier et correspondances des en-têtes
                    $csvHeaders = $this->_model->getCsvHeaders($csvFile);

                    // Accept more CSV MIME types
                    $mimeType = mime_content_type($csvFile);
                    $validCsvTypes = ['text/plain', 'text/csv', 'application/csv', 'text/comma-separated-values', 'application/vnd.ms-excel'];

                    if (!in_array($mimeType, $validCsvTypes)) {
                        $errorMessage = "Le fichier uploadé n'est pas un CSV valide. Type détecté: $mimeType";
                    } elseif (!$this->_model->validateHeaders($csvHeaders, $tableName)) {
                        $errorMessage = "Les en-têtes du fichier CSV ne correspondent pas à la structure de la table $tableName.";
                    } else {
                        // Use the processCsv method which now uses our specialized import functions
                        if ($this->_model->processCsv($csvFile, $tableName)) {
                            $message = "L'importation du fichier CSV pour la table $tableName a été réalisée avec succès!";
                        } else {
                            $errorMessage = "Une erreur est survenue lors de l'importation pour la table $tableName.";
                        }
                    }
                }
            } catch (Exception $e) {
                $dashboardController = new Dashboard($this->_layout);
                $errorMessage = "Erreur lors de l'importation : " .
                    $dashboardController->handleExceptionMessage($e);
            }
        } else {
            $errorMessage = "Table non valide ou non reconnue.";
        }

        return [$message, $errorMessage];
    }

    public function show(string $category = ''): void
    {
        $title = "Gestion des données - Import";
        $cssFilePath = '_assets/styles/gestionDonnees.css';
        $jsFilePath = '_assets/scripts/gestionDonnees.js';
        $view = new \Blog\Views\dashboard\Import($category);

        $this->_layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->_layout->renderBottom($jsFilePath);
    }
}