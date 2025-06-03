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

        // Ajout de l'attribut title pour la tooltip
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

        // Récupérer les coefficients de session
        $dictCoef = $_SESSION['last_dict_coef'] ?? [];
        if (empty($dictCoef)) {
            echo json_encode(['data' => [], 'total' => 0]);
            exit();
        }

        // Vérifier s'il existe déjà une association pour ce stage
        $existingAssociationQuery = "SELECT id_teacher, relevance_score FROM internship WHERE internship_identifier = :internship_id AND id_teacher IS NOT NULL";
        $existingStmt = $db->getConn()->prepare($existingAssociationQuery);
        $existingStmt->bindValue(':internship_id', $internshipId);
        $existingStmt->execute();
        $existingAssociation = $existingStmt->fetch(PDO::FETCH_ASSOC);

        // Récupérer les données du stage
        $internshipQuery = "SELECT * FROM internship WHERE internship_identifier = :internship_id LIMIT 1";
        $internshipStmt = $db->getConn()->prepare($internshipQuery);
        $internshipStmt->bindValue(':internship_id', $internshipId);
        $internshipStmt->execute();
        $internship = $internshipStmt->fetch(PDO::FETCH_ASSOC);

        if (!$internship) {
            echo json_encode(['data' => [], 'total' => 0]);
            exit();
        }

        // Calculer les nouveaux scores avec l'algorithme de dispatch pour ce stage spécifique
        $resultDispatchList = $model->dispatcherEnMieux($dictCoef);

        // Filtrer les résultats pour ce stage spécifique
        $stageMatches = array_filter($resultDispatchList, function($item) use ($internshipId) {
            return $item['internship_identifier'] === $internshipId;
        });

        // Trier par score décroissant
        usort($stageMatches, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Prendre les 10 meilleurs
        $top10Matches = array_slice($stageMatches, 0, 10);

        // Si une association existante n'est pas dans le top 10, l'ajouter
        if ($existingAssociation) {
            $existingTeacherId = $existingAssociation['id_teacher'];
            $existingInTop10 = false;

            foreach ($top10Matches as $match) {
                if ($match['id_teacher'] == $existingTeacherId) {
                    $existingInTop10 = true;
                    break;
                }
            }

            // Si l'association existante n'est pas dans le top 10, récupérer ses données
            if (!$existingInTop10) {
                // Utiliser paginateStage pour récupérer les données de l'association existante
                $existingData = $internshipModel->paginateStage($internshipId, 0, 100);

                // Trouver l'enseignant associé dans les données
                $existingTeacherData = null;
                foreach ($existingData['data'] as $row) {
                    // Extraire l'ID du professeur à partir du nom (vous devrez adapter cette logique)
                    $teacherFullName = $row['prof'];
                    $teacherId = $this->getTeacherIdByFullName($teacherFullName);

                    if ($teacherId == $existingTeacherId) {
                        $existingTeacherData = $row;
                        break;
                    }
                }

                if ($existingTeacherData) {
                    // Remplacer le 10ème élément par l'association existante
                    if (count($top10Matches) >= 10) {
                        array_pop($top10Matches); // Supprimer le 10ème
                    }

                    // Ajouter l'association existante avec un marqueur
                    $top10Matches[] = [
                        'id_teacher' => $existingTeacherId,
                        'score' => $existingAssociation['relevance_score'],
                        'company_name' => $internship['company_name'],
                        'internship_subject' => $internship['internship_subject'],
                        'address' => $internship['address'],
                        'student_number' => $internship['student_number'],
                        'internship_identifier' => $internshipId,
                        'is_existing_association' => true
                    ];
                }
            }
        }

        // Formater les données pour la DataTable
        $data = [];
        foreach ($top10Matches as $match) {
            $teacherId = $match['id_teacher'];
            $score = $match['score'];
            $isExisting = isset($match['is_existing_association']) && $match['is_existing_association'];

            // Récupérer les informations détaillées du professeur via paginateStage
            if (!$isExisting) {
                // Pour les nouveaux matches, utiliser les données du dispatch
                $teacherFullName = $teacherModel->getFullName($teacherId);
                $teacherAddress = $teacherModel->getTeacherAddress($teacherId);

                // Récupérer les autres informations via une requête spécifique
                $teacherDetailsQuery = "
                WITH cte_histo AS (
                    SELECT id_teacher, array_agg(start_date_internship ORDER BY id_teacher) AS history
                    FROM internship
                    WHERE end_date_internship < NOW()
                    GROUP BY id_teacher
                )
                SELECT 
                    h.history,
                    d.distance AS distance,
                    it.discipline_name AS discipline,
                    ha.address AS teacher_address
                FROM teacher t
                LEFT JOIN LATERAL (
                    SELECT address
                    FROM has_address ha
                    WHERE t.id_teacher = ha.id_teacher
                    LIMIT 1
                ) ha ON TRUE
                LEFT JOIN cte_histo h ON t.id_teacher = h.id_teacher
                LEFT JOIN is_taught it ON t.id_teacher = it.id_teacher
                LEFT JOIN LATERAL (
                    SELECT distance
                    FROM distance d2
                    WHERE d2.id_teacher = t.id_teacher AND d2.internship_identifier = :internship_id
                    ORDER BY distance ASC LIMIT 1
                ) d ON TRUE
                WHERE t.id_teacher = :teacher_id
                LIMIT 1";

                $detailsStmt = $db->getConn()->prepare($teacherDetailsQuery);
                $detailsStmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
                $detailsStmt->bindValue(':internship_id', $internshipId);
                $detailsStmt->execute();
                $teacherDetails = $detailsStmt->fetch(PDO::FETCH_ASSOC);

                $data[] = [
                    'prof' => $teacherFullName,
                    'teacher_address' => $teacherDetails['teacher_address'] ?? $teacherAddress,
                    'distance' => isset($teacherDetails['distance']) ? $teacherDetails['distance'] . ' min' : 'N/A',
                    'discipline' => $teacherDetails['discipline'] ?? 'N/A',
                    'score' => $this->renderStars($score),
                    'entreprise' => $match['company_name'],
                    'history' => $this->formatHistory($teacherDetails['history'] ?? ''),
                    'is_existing' => false
                ];
            } else {
                // Pour l'association existante, récupérer via paginateStage
                $existingData = $internshipModel->paginateStage($internshipId, 0, 100);

                foreach ($existingData['data'] as $row) {
                    $teacherFullName = $row['prof'];
                    $currentTeacherId = $this->getTeacherIdByFullName($teacherFullName);

                    if ($currentTeacherId == $teacherId) {
                        $data[] = [
                            'prof' => $row['prof'],
                            'teacher_address' => $row['teacher_address'] ?? '',
                            'distance' => isset($row['distance']) ? $row['distance'] . ' min' : 'N/A',
                            'discipline' => $row['discipline'] ?? 'N/A',
                            'score' => $this->renderStars($score), // Utiliser le score existant
                            'entreprise' => $row['entreprise'],
                            'history' => $row['history'],
                            'is_existing' => true
                        ];
                        break;
                    }
                }
            }
        }

        echo json_encode([
            'data' => $data,
            'total' => count($data),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data)
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

    private function getTeacherIdByFullName(string $fullName): ?int
    {
        $db = Database::getInstance();
        $query = "SELECT id_teacher FROM teacher WHERE CONCAT(teacher_firstname, ' ', teacher_name) = :full_name LIMIT 1";
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':full_name', $fullName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['id_teacher'] : null;
    }
    private function formatHistory($history): string
    {
        if (empty($history)) {
            return '<i class="material-icons red-text tooltipped" data-tooltip="Aucune date">close</i>';
        }

        if (is_array($history)) {
            $cleanDates = array_map(function ($date) {
                return str_replace(['{', '}'], '', htmlspecialchars($date));
            }, $history);
            return implode('<br>', $cleanDates);
        }

        return str_replace(['{', '}'], '', htmlspecialchars($history));
    }



}