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
            'Fatal error: Allowed memory' =>
                "Erreur de taille mémoire, veuillez verifier" .
                "que les séparateur sont bien des ';' puis contacter "
             . "l'administrateur du serveur.",
            'guide utilisateur' => "Erreur lors du traitement du fichier CSV "
            . "(merci de vérifier que vous respectez bien le guide utilisateur).",
            'Les colonnes CSV ne correspondent pas ' .
            'à la table student ou aux valeurs demandées '  .
            'pour la table teacher.' => "Les colonnes CSV ne correspondent pas" .
                "à la table student ou aux valeurs demandées pour la table teacher."
        ];

        // Parcours des mots-clés pour personnaliser le message
        foreach ($simplifyMessages as $key => $simplifyMessage) {
            if (str_contains($e->getMessage(), $key)) {
                return $simplifyMessage;
            }
        }

        // Message générique si aucun mot-clé ne correspond
        return "Une erreur inattendue est survenue. "
        . "Veuillez contacter l'administrateur.";
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
            // Détermine s'il s'agit d'une action d'import ou d'export
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (isset($_FILES['student']) || isset($_FILES['teacher'])
                    || isset($_FILES['internship'])) {
                    // Rediriger vers le contrôleur Import
                    $importController = new Import($this->_layout, $model);
                    list($message, $errorMessage) = $importController->processImport($_POST, $_FILES);
                } elseif (isset($_POST['export_list']) || isset($_POST['export_model'])) {
                    // Rediriger vers le contrôleur Export
                    $exportController = new Export($this->_layout, $model);
                    $exportController->processExport($_POST);
                    return; // Export génère directement le fichier
                }
            }

            // Affichage de la vue dashboard
            $title = "Gestion des données";
            $cssFilePath = '_assets/styles/gestionDonnees.css';
            $jsFilePath = '_assets/scripts/gestionDonnees.js';
            $view = new \Blog\Views\dashboard\Dashboard($message, $errorMessage);

            $this->_layout->renderTop($title, $cssFilePath);
            $view->showView();
            $this->_layout->renderBottom($jsFilePath);
        } else {
            header('Location: /homepage');
        }
    }
}