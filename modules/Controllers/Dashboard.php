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

    private function handleExceptionMessage(Exception $e):string {
        $simplifyMessages = [
            'SQLSTATE' => "Une erreur de base de données est survenue. Une donnée que vous souhaitez insérer existe peut-être déjà.",
            'permission denied' => "Vous n'avez pas les droits nécessaires pour effectuer cette action.",
            'file not found' => "Le fichier demandé est introuvable. Veuillez vérifier votre saisie."
        ];

        // Parcours des mots-clés pour personnaliser le message
        foreach ($simplifyMessages as $key => $simplifyMessage) {
            if(str_contains($e->getMessage(), $key)) {
                return $simplifyMessage;
            }
        }

        // Message générique
        return "Une erreur inattendue est survenue. Veuillez contacter l'administrateur.";
    }

    /**
     * Contrôleur de la Dashboard
     * @return void
     */
    public function show(): void {
        //récupération de l'instance de la base de données et des classes associées
        $message = '';
        $db = \Includes\Database::getInstance();
        $model = new \Blog\Models\Dashboard($db);

        //vérification du rôle de l'utilisateur
        if (isset($_SESSION['role_name']) && (
                (is_array($_SESSION['role_name']) && in_array('Admin_dep', $_SESSION['role_name'])) ||
                ($_SESSION['role_name'] === 'Admin_dep'))) {

            //traitement des requêtes POST
            if($_SERVER["REQUEST_METHOD"] == "POST") {
                //gestion de l'importation de fichiers CSV spécifiques
                if (isset($_FILES['student']) || isset($_FILES['teacher']) || isset($_FILES['internship'])) {
                    $tableName = $_POST['table_name'] ?? null;

                    if ($tableName && $model->isValidTable($tableName)) {
                        try {
                            $csvFile = null;
                            if (isset($_FILES['student'])) {
                                $csvFile = $_FILES['student']['tmp_name'];
                            } elseif (isset($_FILES['teacher'])) {
                                $csvFile = $_FILES['teacher']['tmp_name'];
                            } elseif (isset($_FILES['internship'])) {
                                $csvFile = $_FILES['internship']['tmp_name'];
                            } else {
                                $message = "Aucun fichier CSV valide détecté";
                            }

                            if ($csvFile) {
                                //validation du fichier et correspondances des en-têtes
                                $csvHeaders = $model->getCsvHeaders($csvFile);

                                if (mime_content_type($csvFile) !== 'text/plain') {
                                    $message = "Le fichier uploadé n'est pas un CSV valide.";
                                    return;
                                }

                                elseif (!$model->validateHeaders($csvHeaders, $tableName)) {
                                    $message = "Les en-têtes du fichier CSV ne correspondent pas à la structure de la table $tableName.";
                                    return;
                                }

                                //importation des données dans la table
                                elseif ($model->processCsv($csvFile, $tableName)) {
                                    $message .= "L'importation du fichier CSV pour la table $tableName a été réalisée avec succès! <br>";
                                } else {
                                    $message .= "Une erreur est survenue lors de l'importation pour la table $tableName. <br>";
                                }
                            }
                        } catch (Exception $e) {
                            $message .= "Erreur lors de l'importation : <br>" . $this->handleExceptionMessage($e);
                        }
                    } else {
                        $message = "Table non valide ou non reconnue.";
                    }

                //gestion de l'exportation des fichiers CSV
                } elseif (isset($_POST['export_list'])) {
                    $tableName = $_POST['export_list'];

                    if ($model->isValidTable($tableName)) {
                        try {
                            $headers = $model->getTableColumn($tableName);
                            $model->exportToCsvByDepartment($tableName, $headers);
                        } catch (Exception $e) {
                            echo "Erreur lors de l'exportation : " . $this->handleExceptionMessage($e);
                        }
                    } else {
                        echo "Table inconnue pour l'export";
                    }
                } else {
                    echo "Aucun fichier CSV n'est reconnu.";
                }
            }
            //définition de variables
            $title = "Dashboard";
            $cssFilePath = '_assets/styles/dashboard.css';
            $jsFilePath = '_assets/scripts/dashboard.js';
            $view = new \Blog\Views\Dashboard($message);

            //affichage de la vue Dashboard
            $this->layout->renderTop($title, $cssFilePath);
            $view->showView();
            $this->layout->renderBottom($jsFilePath);
        }

        //redirection de l'utilisateur si il n'a pas les autorisations
        else {
            header('Location: /homepage');
        }
    }
}