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
use Blog\Models\Student;
use Blog\Models\Teacher;
use includes\Database;
use JetBrains\PhpStorm\NoReturn;
use PDO;

class AjaxController
{

    public function getDispatchList(int $start, int $length, string $search = '', array $order = []): void
    {
        header('Content-Type: application/json');

        $db = Database::getInstance();
        $teacherModel = new Teacher($db);
        $studentModel = new Student($db);
        $model = new Model($db);
        $dictCoef = $_SESSION['last_dict_coef'] ?? [];
        if (empty($dictCoef)) {
            echo json_encode(['data' => [], 'total' => 0]);
            exit();
        }

        $columns = [
            'id_teacher',
            'student_number',
            'internship_identifier',
            'subject',
            'address',
            'teacher_address',
            'score',
            'internship_identifier'
        ];

        $countQuery = "SELECT COUNT(*) as total FROM internship WHERE id_teacher IS NOT NULL AND end_date_internship > NOW()";

        if (!empty($search)) {
            $countQuery .= ' AND (company_name ILIKE :search OR internship_subject ILIKE :search OR address ILIKE :search)';
        }

        $countStmt = $db->getConn()->prepare($countQuery);
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $countStmt->bindValue(':search', $searchParam);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $data = [];
        $scores = [];
        $resultDispatchList = $model->dispatcherEnMieux($dictCoef);

        $dataQuery = "SELECT * FROM internship WHERE id_teacher IS NOT NULL AND end_date_internship > NOW()";

        if (!empty($search)) {
            $dataQuery .= ' AND (company_name ILIKE :search OR internship_subject ILIKE :search OR address ILIKE :search)';
        }

        if (!empty($order) && isset($order['column']) && isset($columns[$order['column']]) && $columns[$order['column']] !== null) {
            $dataQuery .= ' ORDER BY ' . $columns[$order['column']] . ' ' . (strtoupper($order['dir']) === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $dataQuery .= ' ORDER BY company_name ASC';
        }

        $dataQuery .= ' LIMIT :limit OFFSET :offset';

        $dataStmt = $db->getConn()->prepare($dataQuery);
        if (!empty($search)) {
            $dataStmt->bindValue(':search', $searchParam);
        }
        $dataStmt->bindValue(':limit', $length, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $start, PDO::PARAM_INT);
        $dataStmt->execute();
        $existingAssociations = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($existingAssociations as $item) {
            $checkboxValue = $item['id_teacher'] . '$' . $item['internship_identifier'] . '$' . $item['relevance_score'];
            $companyName = $item['company_name'];

            $studentFullName = $studentModel->getFullName($item['student_number']);

            $teacherAddress = $teacherModel->getTeacherAddress($item['id_teacher']);
            $teacherFullName = $teacherModel->getFullName($item['id_teacher']);
            $data[] = [
                'teacher' => $teacherFullName,
                'student' => $studentFullName,
                'internship' => $companyName,
                'subject' => $item['internship_subject'],
                'address' => $item['address'],
                'teacher_address' => $teacherAddress,
                'score' => $this->renderStars($item['relevance_score']),
                'internship_identifier' => $item['internship_identifier'],
                'associate' => '<input type="checkbox" class="dispatch-checkbox" name="listTupleAssociate[]" value="' . htmlspecialchars($checkboxValue) . '" checked>'
            ];

            $scores[] = $item['relevance_score'];
        }

        foreach ($resultDispatchList as $item) {
            $checkboxValue = $item['id_teacher'] . '$' . $item['internship_identifier'] . '$' . $item['score'];
            $companyName = $item['company_name'];

            $studentFullName = $studentModel->getFullName($item['student_number']);

            $teacherAddress = $teacherModel->getTeacherAddress($item['id_teacher']);
            $teacherFullName = $teacherModel->getFullName($item['id_teacher']);
            $data[] = [
                'teacher' => $teacherFullName,
                'student' => $studentFullName,
                'internship' => $companyName,
                'subject' => $item['internship_subject'],
                'address' => $item['address'],
                'teacher_address' => $teacherAddress,
                'score' => $this->renderStars($item['score']),
                'internship_identifier' => $item['internship_identifier'],
                'associate' => '<input type="checkbox" class="dispatch-checkbox" name="listTupleAssociate[]" value="' . htmlspecialchars($checkboxValue) . '">'
            ];

            $scores[] = $item['score'];
        }

        $_SESSION['dispatch_scores'] = $scores;
        usort($data, function ($a, $b) {
            return strcmp($a['student'], $b['student']);
        });
        echo json_encode(['data' => $data, 'total' => (int)$total]);
    }



    function renderStars(float | null $score): string | null
    {
        if ($score === null) {
            return null;
        }
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

        return '<div class="star-rating" title="Score : ' . number_format($score, 1) . ' / 5" style="cursor: default; z-index: 10000000000;">' . $stars . '</div>';
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
                    $row['associate'] = '<input type="checkbox" class="dispatch-checkbox" name="listTupleAssociate[]" value="' . htmlspecialchars($row['id_teacher'] . '$' . $row['internship_identifier']) . '">';
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
                    if (isset($row['score'])) {
                        $row['score'] = $this->renderStars((float)$row['score']);
                    }
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
        $teacherModel = new Teacher($db);
        $model = new Model($db);
        $dictCoef = $_SESSION['last_dict_coef'] ?? [];

        // Récupérer tous les enseignants
        $teachers = $teacherModel->getAllTeachers();
        $internshipList = $internshipModel->getInternshipById($internshipId);
        if (empty($internshipList)) {
            echo json_encode(['error' => 'Stage introuvable']);
            return;
        }
        $internship = $internshipList[0];

        $assignedTeacherId = $internship['id_teacher'] ?? null;

        $scores = [];

        foreach ($teachers as $teacher) {
            $scoreData = $model->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship);

            $isAssocie = ($teacher['id_teacher'] == $assignedTeacherId);

            $teacherAddress = $teacherModel->getTeacherAddress($teacher['id_teacher']);
            $history = $scoreData['A été responsable'] ?? null;
            if (empty($history)) {
                $history = '<i class="material-icons red-text tooltipped" data-tooltip="Aucune date">close</i>';
            } else {
                if (is_array($history)) {
                    $cleanDates = array_map(function ($date) {
                        return str_replace(['{', '}'], '', htmlspecialchars($date));
                    }, $history);
                    $history = implode('<br>', $cleanDates);
                } else {
                    $history = str_replace(['{', '}'], '', htmlspecialchars($history));
                }
            }
            $scores[] = [
                'associate' => '<input type="checkbox" class="dispatch-checkbox" name="listTupleAssociate[]" value="' . $teacher['id_teacher'] . '" ' . ($isAssocie ? 'checked' : '') . '>',
                'prof' => $teacher['teacher_firstname'] . ' ' . $teacher['teacher_name'],
                'distance' => $scoreData['Distance'] ?? null . " min",
                'discipline' => $teacher['discipline_name'] ?? null,
                'score' => $this->renderStars($scoreData['score'] ?? 0),
                'entreprise' => $internship['company_name'] ?? '',
                'history' => $history,
                'associe' => $isAssocie,
                'id_teacher' => $teacher['id_teacher'],
                'teacher_address' => $teacherAddress,

            ];
        }


        // Tri décroissant sur le score
        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

        $top = array_slice($scores, 0, 10);

        // Si enseignant assigné pas dans le top, on l'ajoute
        if ($assignedTeacherId !== null && !in_array($assignedTeacherId, array_column($top, 'id_teacher'))) {
            foreach ($scores as $entry) {
                if ($entry['id_teacher'] == $assignedTeacherId) {
                    $entry['associe'] = true;
                    $top[] = $entry;
                    break;
                }
            }
            usort($top, fn($a, $b) => $b['score'] <=> $a['score']);
        }

        echo json_encode([
            'data' => $top
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