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
use Blog\Models\Model;
use Blog\Models\Teacher;
use includes\Database;
use JetBrains\PhpStorm\NoReturn;

class AjaxController
{

    public function getDispatchList(): void
    {
        header('Content-Type: application/json');

        $db = Database::getInstance();
        $teacherModel = new Teacher($db);
        $internshipModel = new Internship($db);
        $departmentModel = new Department($db);
        $dictCoef = $_SESSION['last_dict_coef'] ?? [];
        if (empty($dictCoef)) {
            echo json_encode(['data' => []]);
            exit();
        }

        $resultDispatchList = $internshipModel
            ->dispatcher(
                $departmentModel,
                $teacherModel,
                $dictCoef
            )[0];

        $data = [];
        $scores = []; // Tableau pour stocker les scores
        foreach ($resultDispatchList as $item) {
            $checkboxValue = $item['id_teacher'] . '$' . $item['internship_identifier'] . '$' . $item['score'];
            $companyName = $item['company_name'];

            $data[] = [
                'teacher' => $item['teacher_firstname'] . ' ' . $item['teacher_name'] . ' (' . $item['id_teacher'] . ')',
                'student' => $item['student_firstname'] . ' ' . $item['student_name'] . ' (' . $item['student_number'] . ')',
                'internship' => $companyName . ' (' . $item['internship_identifier'] . ')',
                'formation' => $item['formation'],
                'group' => $item['class_group'],
                'subject' => $item['internship_subject'],
                'address' => $item['address'],
                'score' => $item['score'],
                'associate' => '<input type="checkbox" class="dispatch-checkbox" name="listTupleAssociate[]" value="' . htmlspecialchars($checkboxValue) . '">',
                'internship_identifier' => $item['internship_identifier']
            ];

            $scores[] = $item['score'];
        }

        $_SESSION['dispatch_scores'] = $scores;

        echo json_encode(['data' => $data]);
    }


    function renderStars(float $score): string
    {
        $fullStars = floor($score);

        $decimalPart = $score - $fullStars;

        $halfStars = ($decimalPart > 0 && $decimalPart < 1) ? 1 : 0;

        $emptyStars = 5 - $fullStars - $halfStars;

        $stars = '';

        for ($i = 0; $i < $fullStars; $i++) {
            $stars .= '<span class="filled"></span>';
        }

        if ($halfStars) {
            $stars .= '<span class="half"></span>';
        }

        for ($i = 0; $i < $emptyStars; $i++) {
            $stars .= '<span class="empty"></span>';
        }

        return $stars;
    }
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
        $departmentModel = new Department($db);


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
                foreach ($result['data'] as &$row) {
                    // $row['score'] est un float, on appelle renderStars pour générer les étoiles
                    $row['score'] = $this->renderStars((float)$row['score']);
                }
                break;





        }

        $this->sendJsonResponse([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['total'],
            'data' => $result['data']
        ]);
    }
    public function getViewStage(string $internshipId): void
    {
        header('Content-Type: application/json');

        $db = Database::getInstance();
        $internshipModel = new Internship($db);

        // Récupérer les données de viewStage
        $viewStageData = $internshipModel->paginateStage($internshipId, 0, 10);

        // Inclure les scores de dispatcher-list
        $viewStageData['dispatch_scores'] = $_SESSION['dispatch_scores'] ?? [];

        echo json_encode($viewStageData);
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