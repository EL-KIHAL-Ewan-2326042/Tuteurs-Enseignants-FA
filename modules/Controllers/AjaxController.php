<?php
/**
 * Fichier contenant le contrôleur pour les requêtes AJAX (pseudo-API))
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 */

namespace Blog\Controllers;

use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Teacher;
use includes\Database;
use JetBrains\PhpStorm\NoReturn;

class AjaxController
{
    /**
     * Traite les requêtes AJAX pour les tableaux DataTables
     *
     * @return void
     */
    public function handleDataTable(): void
    {
        // Vérifier l'authentification
        if (!isset($_SESSION['identifier'])) {
            $this->sendJsonResponse(['error' => 'Non autorisé'], 401);
            return;
        }

        $db = Database::getInstance();
        $teacherModel = new Teacher($db);
        $internshipModel = new Internship($db);

        // Récupérer les paramètres de DataTables
        $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $search = $_POST['search']['value'] ?? '';

        // Gestion du tri
        $order = [];
        if (isset($_POST['order'][0])) {
            $order = [
                'column' => intval($_POST['order'][0]['column']),
                'dir' => $_POST['order'][0]['dir']
            ];
        }

        // Vérifier que des départements sont sélectionnés
        if (empty($_SESSION['selecDep'])) {
            $this->sendJsonResponse([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
            return;
        }

        // Obtenir les données paginées
        $data = $teacherModel->paginate(
            $_SESSION['selecDep'],
            $_SESSION['identifier'],
            $start,
            $length,
            $search,
            $order
        );

        // Compter les enregistrements
        $recordsTotal = $teacherModel->countAll($_SESSION['selecDep']);
        $recordsFiltered = $teacherModel->countFiltered($_SESSION['selecDep'], $search);

        // Formater les données pour DataTables
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'student' => $row['student_name'] . ' ' . $row['student_firstname'],
                'formation' => str_replace('_', ' ', $row['formation']),
                'group' => str_replace('_', ' ', $row['class_group']),
                'history' => $row['internshipTeacher'] > 0 ? $row['year'] : '❌',
                'company' => str_replace('_', ' ', $row['company_name']),
                'subject' => str_replace('_', ' ', $row['internship_subject']),
                'address' => str_replace('_', "'", $row['address']),
                'duration' => '~' . $row['duration'] . ' minute' . ($row['duration'] > 1 ? 's' : ''),
                'choice' => '<label class="center"><input type="checkbox" name="selecInternship[]" class="center-align filled-in" value="' . $row['internship_identifier'] . '" ' . ($row['requested'] ? 'checked="checked"' : '') . '/><span>Cocher</span></label>',
                'DT_RowData' => [
                    'address' => str_replace('_', "'", $row['address']),
                    'company' => str_replace('_', ' ', $row['company_name'])
                ]
            ];
        }

        $this->sendJsonResponse([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData
        ]);
    }

    /**
     * Envoie une réponse JSON avec les en-têtes appropriés
     *
     * @param array $data    Les données à envoyer
     * @param int   $status  Le code de statut HTTP
     *
     * @return void
     */
    private function sendJsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: max-age=3600, public'); // 1 heure de cache
        header('Expires: ' . gmdate('D\, d M Y H\:i\:s', time() + 3600) . ' GMT');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }
}