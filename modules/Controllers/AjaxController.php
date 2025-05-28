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
    #[NoReturn] public function handleDataTable(string $type, ?string $id = null): void
    {
        // Vérifier l'authentification
        if (!isset($_SESSION['identifier'])) {
            $this->sendJsonResponse(['error' => 'Non autorisé'], 401);
            return;
        }

        $db = Database::getInstance();
        $teacherModel = new Teacher($db);
        $internshipModel = new Internship($db);

        $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $search = $_POST['search']['value'] ?? '';
        $order = [];
        if (isset($_POST['order'][0])) {
            $order = [
                'column' => intval($_POST['order'][0]['column']),
                'dir' => $_POST['order'][0]['dir']
            ];
        }
        switch ($type) {
            case 'ask':
                $result = $teacherModel->paginateAsk(
                    $_SESSION['identifier'],
                    $start,
                    $length,
                    $search,
                    $order
                );
                foreach ($result['data'] as &$row) {
                    if (isset($row['distance'])) {
                        $row['distance'] .= ' min';
                    }
                    if (empty($row['history'])) {
                        $row['history'] = '<i class="material-icons red-text tooltipped" data-tooltip="Aucune date">close</i>';
                    } else {
                        if (is_array($row['history'])) {
                            $cleanDates = array_map(function ($date) {
                                return str_replace(['{', '}'], '', htmlspecialchars($date));
                            }, $row['history']);
                            $row['history'] = implode('<br>', $cleanDates);
                        } else {
                            $row['history'] = str_replace(['{', '}'], '', htmlspecialchars($row['history']));
                        }
                    }
                }


                break;
            case 'account':
                $result = $teacherModel->paginateAccount(
                    $_SESSION['identifier'],
                    $start,
                    $length,
                    $search,
                    $order
                );
                break;
            case 'stage':
                $result = $internshipModel->paginateStage(
                    $id,
                    $start,
                    $length,
                    $search,
                    $order
                );

        }

        $this->sendJsonResponse([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['total'],
            'data' => $result['data']
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
    #[NoReturn] private function sendJsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: max-age=3600, public'); // 1 heure de cache
        header('Expires: ' . gmdate('D\, d M Y H\:i\:s', time() + 3600) . ' GMT');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }


}