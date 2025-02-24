<?php
/**
 * Fichier contenant le contrôleur de la page 'Gestion des données'
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Controllers;

use Blog\Models\Model;
use Blog\Views\layout\Layout;
use Exception;
use includes\Database;
require_once 'includes/Database.php';

/**
 * Classe gérant les échanges de données entre
 * le modèle et la vue de la page 'Gestion des données'
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Dashboard
{
    private Layout $_layout;

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Layout $layout Instance de la classe Layout
     *                       servant de vue pour la mise en page
     */
    public function __construct(Layout $layout)
    {
        $this->_layout = $layout;
    }

    /**
     * Gère les exceptions en simplifiant les
     * messages pour les utilisateurs non techniques
     *
     * @param Exception $e L'exception levée
     *
     * @return string Un message compréhensible pour l'utilisateur final
     */
    public function handleExceptionMessage(Exception $e): string
    {
        // Correspondances entre mots-clés et message simplifiés
        $simplifyMessages = [
            'SQLSTATE' => "Une erreur de base de données est survenue. "
            . "Une donnée que vous souhaitez insérer existe peut-être déjà.",
            'permission denied' => "Vous n'avez pas les droits nécessaires "
             . "pour effectuer cette action.",
            'file not found' => "Le fichier demandé est introuvable. "
             . "Veuillez vérifier votre saisie.",
            'Fatal' => "Erreur de taille mémoire, veuillez contacter "
             . "l'administrateur du serveur.",
            'guide utilisateur' => "Erreur lors du traitement du fichier CSV "
            . "(merci de vérifier que vous respectez bien le guide utilisateur)."
        ];

        // Parcours des mots-clés pour personnaliser le message
        foreach ($simplifyMessages as $key => $simplifyMessage) {
            if (str_contains($e->getMessage(), $key)) {
                return $simplifyMessage;
            }
        }

        // Message générique si aucun mot-clé ne correspond
        return "Une erreur inattendue est survenue. "
        . "Veuillez contacter l'administrateur.".$this->handleExceptionMessage($e);
    }

    /**
     * Contrôleur de la page 'Gestion des données'
     *
     * @return void
     */
    public function show(): void
    {
        // Récupération de l'instance de la base de données et des classes associées
        $db = Database::getInstance();
        $model = new Model($db);

        // Initialisation du message à afficher
        $message = '';
        $errorMessage = '';

        // Vérification du rôle de l'utilisateur
        if (isset($_SESSION['role_name'])
            && ((is_array($_SESSION['role_name'])
            && in_array('Admin_dep', $_SESSION['role_name']))
            || ($_SESSION['role_name'] === 'Admin_dep'))
        ) {

            // Traitement des requêtes POST
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Gestion de l'importation de fichiers CSV spécifiques
                if (isset($_FILES['student'])
                    || isset($_FILES['teacher'])
                    || isset($_FILES['internship'])
                ) {
                    $tableName = $_POST['table_name'] ?? null;

                    if ($tableName && $model->isValidTable($tableName)) {
                        try {
                            $csvFile = null;

                            // Détection du fichier importé
                            if (isset($_FILES['student'])) {
                                $csvFile = $_FILES['student']['tmp_name'];
                            } elseif (isset($_FILES['teacher'])) {
                                $csvFile = $_FILES['teacher']['tmp_name'];
                            } elseif (isset($_FILES['internship'])) {
                                $csvFile = $_FILES['internship']['tmp_name'];
                            } else {
                                $errorMessage = "Aucun fichier CSV valide détecté";
                            }

                            if ($csvFile) {
                                // Validation du fichier et
                                // correspondances des en-têtes
                                $csvHeaders = $model->getCsvHeaders($csvFile);

                                if (mime_content_type($csvFile) !== 'text/plain') {
                                    $errorMessage = "Le fichier uploadé "
                                    . "n'est pas un CSV valide.";
                                    return;

                                } elseif (!$model->validateHeaders(
                                    $csvHeaders, $tableName
                                )
                                ) {
                                    $errorMessage = "Les en-têtes du fichier CSV ne "
                                    . "correspondent pas à la structure de "
                                    . "la table $tableName.";
                                    return;

                                    // Importation des données dans la table
                                } elseif ($model->processCsv($csvFile, $tableName)) {
                                    $message .= "L'importation du fichier CSV pour "
                                    . "la table $tableName a été réalisée avec "
                                    . "succès! <br>";
                                } else {
                                    $errorMessage .= "Une erreur est survenue lors "
                                    . "de l'importation pour la table $tableName. "
                                    . "<br>";
                                }
                            }
                        } catch (Exception $e) {
                            $errorMessage .= "Erreur lors de l'importation : "
                            . $this->handleExceptionMessage($e);
                        }
                    } else {
                        $errorMessage = "Table non valide ou non reconnue.";
                    }

                    // Gestion de l'exportation des fichiers CSV
                } elseif (isset($_POST['export_list'])) {
                    $tableName = $_POST['export_list'];
                    if ($model->isValidTable($tableName)) {
                        try {
                            $headers = $model->getTableColumn($tableName);
                            if ($tableName == 'teacher') {
                                $headers = array_merge(
                                    $headers, ['address$type'], ['discipline_name']
                                );
                            }
                            $model->exportToCsvByDepartment($tableName, $headers);
                        } catch (Exception $e) {
                            echo "Erreur lors de l'exportation : "
                            . $this->handleExceptionMessage($e);
                        }
                    } else {
                        echo "Table inconnue pour l'export";
                    }

                    // Gestion de l'exportation des modèles en CSV
                } elseif (isset($_POST['export_model'])) {
                    $tableName = $_POST['export_model'];
                    if ($model->isValidTable($tableName)) {
                        try{
                            $model->exportModel($tableName);
                        } catch (Exception $e) {
                            echo "Erreur lors de l'exportation : "
                            . $this->handleExceptionMessage($e);
                        }
                    }
                } else {
                    echo "Aucun fichier CSV n'est reconnu.";
                }
            }

            // Définition de variables
            $title = "Gestion des données";
            $cssFilePath = '_assets/styles/gestionDonnees.css';
            $jsFilePath = '_assets/scripts/gestionDonnees.js';
            $view = new \Blog\Views\dashboard\Dashboard($message, $errorMessage);

            // Affichage de la vue dashboard
            $this->_layout->renderTop($title, $cssFilePath);
            $view->showView();
            $this->_layout->renderBottom($jsFilePath);

            // Redirection de l'utilisateur si il n'a pas les autorisations
        } else {
            header('Location: /homepage');
        }
    }
}