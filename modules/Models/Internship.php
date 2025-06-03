<?php
/**
 * Fichier contenant le modèle associé aux informations des stages/alternances
 *
 * PHP version 8.3
 *
 * @category Model
 * @package  TutorMap/modules/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Models;

use includes\Database;
use PDO;
use PDOException;

class Internship extends Model
{
    private Database $_db;
    private $cache = [];

    public function __construct(Database $_db)
    {
        parent::__construct($_db);
        $this->_db = $_db;
    }

    public function getInternships(string $student): false|array
    {
        if (isset($this->cache['getInternships'][$student])) {
            return $this->cache['getInternships'][$student];
        }

        $query = 'SELECT id_teacher, student_number, Start_date_internship, End_date_internship FROM internship WHERE student_number = :student AND end_date_internship < NOW() AND id_teacher IS NOT NULL';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->cache['getInternships'][$student] = $result;
        return $result;
    }

    public function getInternshipTeacher(array $internshipStudent, string $teacher, string &$year): int
    {
        $internshipTeacher = 0;
        foreach ($internshipStudent as $row) {
            if ($row['id_teacher'] == $teacher) {
                ++$internshipTeacher;
                if ($internshipTeacher == 1) {
                    $year = substr($row['start_date_internship'], 0, 4);
                }
            }
        }
        return $internshipTeacher;
    }

    public function getInterns(string $teacher): array
    {
        if (isset($this->cache['getInterns'][$teacher])) {
            return $this->cache['getInterns'][$teacher];
        }

        $query = 'SELECT 
                i.company_name, i.internship_subject, i.address, s.student_name, s.student_firstname,
                i.type, s.formation, s.class_group, i.student_number, i.internship_identifier, i.id_teacher
              FROM internship i
              JOIN student s ON i.student_number = s.student_number
              WHERE i.id_teacher = :teacher AND i.end_date_internship > CURRENT_DATE';

        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$studentsList) {
            return [];
        }

        // Récupérer tous les internships des étudiants concernés en une seule requête
        $studentNumbers = array_column($studentsList, 'student_number');
        $inQuery = implode(',', array_fill(0, count($studentNumbers), '?'));

        $query2 = "SELECT id_teacher, student_number, start_date_internship, end_date_internship
               FROM internship
               WHERE student_number IN ($inQuery) AND end_date_internship < NOW() AND id_teacher IS NOT NULL";

        $stmt2 = $this->_db->getConn()->prepare($query2);
        $stmt2->execute($studentNumbers);
        $allInternships = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Indexer les internships par student_number
        $internshipsByStudent = [];
        foreach ($allInternships as $internship) {
            $internshipsByStudent[$internship['student_number']][] = $internship;
        }

        foreach ($studentsList as &$row) {
            $internships = $internshipsByStudent[$row['student_number']] ?? [];
            $row['year'] = "";
            $row['internshipTeacher'] = $this->getInternshipTeacher($internships, $teacher, $row['year']);
            $row['duration'] = $this->getDistance($row['internship_identifier'], $teacher, isset($row['id_teacher']));
        }

        $this->cache['getInterns'][$teacher] = $studentsList;
        return $studentsList;
    }


    public function getDistance(string $internship_identifier, string $id_teacher, bool $bound = false): int
    {
        if (isset($this->cache['getDistance'][$internship_identifier][$id_teacher])) {
            return $this->cache['getDistance'][$internship_identifier][$id_teacher];
        }

        $conn = $this->_db->getConn();

        // Vérifier si distance déjà en base
        $stmt = $conn->prepare('SELECT distance FROM Distance WHERE internship_identifier = :internship AND id_teacher = :teacher');
        $stmt->execute([':internship' => $internship_identifier, ':teacher' => $id_teacher]);
        $distanceDb = $stmt->fetchColumn();

        if ($distanceDb !== false) {
            $distance = (int)$distanceDb;
            $this->cache['getDistance'][$internship_identifier][$id_teacher] = $distance;
            return $distance;
        }

        // Récupérer l'adresse du stage
        $stmt = $conn->prepare('SELECT address FROM Internship WHERE internship_identifier = :internship');
        $stmt->execute([':internship' => $internship_identifier]);
        $internshipAddress = $stmt->fetchColumn();

        if (!$internshipAddress) {
            return 60; // Valeur par défaut si pas d'adresse
        }

        // Récupérer les adresses de l'enseignant
        $stmt = $conn->prepare('SELECT address FROM Has_address WHERE id_teacher = :teacher');
        $stmt->execute([':teacher' => $id_teacher]);
        $teacherAddresses = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($teacherAddresses)) {
            return 60; // Valeur par défaut si pas d'adresse enseignant
        }

        $latLngInternship = $this->geocodeAddress($internshipAddress);
        if (!$latLngInternship) {
            return 60; // Valeur par défaut si géocodage échoue
        }

        $minDuration = PHP_INT_MAX;

        foreach ($teacherAddresses as $address) {
            $latLngTeacher = $this->geocodeAddress($address);
            if (!$latLngTeacher) {
                continue;
            }

            $duration = $this->calculateDuration($latLngInternship, $latLngTeacher);
            if (!is_numeric($duration)) {
                continue;
            }

            $this->cache['getDistance'][$internship_identifier][$id_teacher] = $duration;

            return $duration;
        }


        if ($minDuration === PHP_INT_MAX || $minDuration > 999999 || $minDuration < 0) {
            $minDuration = 60;
        }

        $this->cache['getDistance'][$internship_identifier][$id_teacher] = $minDuration;

        return $minDuration;
    }



    public function getCountInternsPerType(array $trainees, int &$internship, int &$alternance): void
    {
        $internship = 0;
        $alternance = 0;
        if (empty($trainees)) {
            return;
        }

        foreach ($trainees as $trainee) {
            if (strtolower($trainee['type']) == 'internship') {
                ++$internship;
            }
            if (strtolower($trainee['type']) == 'alternance') {
                ++$alternance;
            }
        }
    }

    public function relevanceTeacher(Department $departmentModel, Teacher $teacherModel, array $teacher, array $dictCoef): array
    {
        $identifier = $teacher['id_teacher'];

        $internshipList = [];
        $departments = $teacherModel->getDepTeacher($identifier);
        foreach ($departments as $listDepTeacher) {
            foreach ($listDepTeacher as $department) {
                $newList = $departmentModel->getInternshipsPerDepartment($department);
                if ($newList) {
                    $internshipList = array_merge($internshipList, $newList);
                }
            }
        }

        $result = [];
        foreach ($internshipList as $internship) {
            $result[] = $this->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship);
        }

        return $result;
    }

    public function relevanceInternship(string $internship, array $dictCoef): array
    {
        $internshipKey = $internship;

        if (isset($this->cache['relevanceInternship'][$internshipKey])) {
            return $this->cache['relevanceInternship'][$internshipKey];
        }

        $_db = $this->_db;

        $query = "SELECT Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname,
                SUM(CASE WHEN internship.type = 'alternance' THEN 1 ELSE 0 END) AS current_count_apprentice,
                SUM(CASE WHEN internship.type = 'internship' THEN 1 ELSE 0 END) AS current_count_intern
                FROM Teacher
                JOIN (SELECT DISTINCT user_id, department_name FROM has_role) AS has_role
                ON Teacher.Id_teacher = has_role.user_id
                JOIN Study_at ON Study_at.department_name = has_role.department_name
                JOIN Student ON Student.student_number = Study_at.student_number
                JOIN INTERNSHIP ON Internship.student_number = Student.student_number
                WHERE has_role.department_name IN (
                    SELECT department_name
                    FROM Study_at
                    JOIN Student ON Study_at.student_number = Student.student_number
                    JOIN Internship ON Internship.student_number = Internship.student_number
                    WHERE Internship.internship_identifier = :internship
                    GROUP BY department_name
                )
                AND Internship.internship_identifier = :internship
                GROUP BY Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname
                HAVING Teacher.Maxi_number_intern > SUM(CASE WHEN internship.type = 'internship' THEN 1 ELSE 0 END)
                AND Teacher.Maxi_number_apprentice > SUM(CASE WHEN internship.type = 'alternance' THEN 1 ELSE 0 END)";

        $stmt = $_db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $teacherList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT Internship.Internship_identifier, Internship.Company_name, Internship.Internship_subject,
                Internship.Address, Internship.Student_number, Internship.Type,
                Student.Student_name, Student.Student_firstname, Student.Formation, Student.Class_group
                FROM Internship
                JOIN Student ON Internship.Student_number = Student.Student_number
                WHERE Internship.internship_identifier = :internship";

        $stmt = $_db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $internshipDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($teacherList as $teacher) {
            $result[] = $this->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internshipDetails[0]);
        }

        if (!empty($result)) {
            usort($result, fn($a, $b) => $b['score'] <=> $a['score']);
            $this->cache['relevanceInternship'][$internshipKey] = $result;
            return $result;
        }
        return [[]];
    }



    public function scoreDiscipSubject(string $internshipId, string $identifier): float
    {
        if (isset($this->cache['scoreDiscipSubject'][$internshipId][$identifier])) {
            return $this->cache['scoreDiscipSubject'][$internshipId][$identifier];
        }

        $conn = $this->_db->getConn();

        // Récupérer les mots-clés du stage
        $stmt = $conn->prepare("SELECT keywords FROM internship WHERE internship_identifier = :internshipId");
        $stmt->bindParam(':internshipId', $internshipId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $keywords = $result['keywords'] ?? '';
        $internshipTokens = $keywords ? preg_split('/[\s_]+/', trim($keywords)) : [];
        if (empty($internshipTokens)) {
            return 0.0;
        }

        // Récupérer les disciplines de l’enseignant
        $stmt = $conn->prepare("SELECT discipline_name FROM is_taught WHERE id_teacher = :id");
        $stmt->bindParam(':id', $identifier);
        $stmt->execute();
        $disciplines = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($disciplines)) {
            return 0.0;
        }

        $teacherTokens = preg_split('/[\s_]+/', implode(' ', $disciplines));
        if (empty($teacherTokens)) {
            return 0.0;
        }

        // Recherche rapide avec array_flip
        $teacherTokensFlipped = array_flip($teacherTokens);

        $score = 0.0;
        $internshipCount = count($internshipTokens);

        foreach ($internshipTokens as $token) {
            if (isset($teacherTokensFlipped[$token])) {
                $score += 1 / $internshipCount;
                if ($score >= 1.0) {
                    $score = 1.0;
                    break;
                }
            }
        }

        $this->cache['scoreDiscipSubject'][$internshipId][$identifier] = $score;

        return $score;
    }




    public function isRequested(string $internship_identifier, string $id_teacher): bool
    {
        if (isset($this->cache['isRequested'][$internship_identifier][$id_teacher])) {
            return $this->cache['isRequested'][$internship_identifier][$id_teacher];
        }

        $query = "SELECT * FROM is_requested WHERE internship_identifier = :internship_identifier AND id_teacher = :id_teacher";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':internship_identifier', $internship_identifier);
        $stmt->bindParam(':id_teacher', $id_teacher);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $isRequested = !$result ? false : true;
        $this->cache['isRequested'][$internship_identifier][$id_teacher] = $isRequested;
        return $isRequested;
    }

    public function dispatcher(Department $departmentModel, Teacher $teacherModel, array $dicoCoef): array
    {
        $_db = $this->_db;

        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname, Maxi_number_intern AS max_intern, Maxi_number_apprentice AS max_apprentice, SUM(CASE WHEN internship.type = 'alternance' THEN 1 ELSE 0 END) AS current_count_apprentice, SUM(CASE WHEN internship.type = 'internship' THEN 1 ELSE 0 END) AS current_count_intern FROM Teacher JOIN (SELECT DISTINCT user_id, department_name FROM has_role) AS has_role ON Teacher.Id_teacher = has_role.user_id LEFT JOIN internship ON Teacher.Id_teacher = internship.Id_teacher WHERE department_name IN ($placeholders) GROUP BY Teacher.Id_teacher";

        $stmt = $_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        $teacherData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $listTeacherMaxIntern = [];
        $listTeacherMaxApprentice = [];
        $listTeacherIntern = [];
        $listTeacherApprentice = [];

        foreach ($teacherData as $teacher) {
            $listTeacherMaxIntern[$teacher['id_teacher']] = $teacher['max_intern'];
            $listTeacherMaxApprentice[$teacher['id_teacher']] = $teacher['max_apprentice'];
            $listTeacherIntern[$teacher['id_teacher']] = $teacher['current_count_intern'];
            $listTeacherApprentice[$teacher['id_teacher']] = $teacher['current_count_apprentice'];
        }

        $listFinal = [];
        $listStart = [];
        $listEleveFinal = []; // Array pour compatibilité
        $setEleveFinal = []; // Set pour performance O(1)

        foreach ($teacherData as $teacher) {
            foreach ($this->relevanceTeacher($departmentModel, $teacherModel, $teacher, $dicoCoef) as $association) {
                // Filtrer les données invalides dès le début
                if (isset($association['id_teacher']) &&
                    isset($association['internship_identifier']) &&
                    isset($association['type']) &&
                    in_array(strtolower(trim($association['type'])), ['internship', 'alternance'])) {
                    $listStart[] = $association;
                }
            }
        }

        if (empty($listStart)) {
            return [[], $listTeacherIntern, $listTeacherApprentice];
        }

        $assignedCountsIntern = $listTeacherIntern;
        $assignedCountsApprentice = $listTeacherApprentice;

        // Trier une seule fois au début
        usort($listStart, fn($a, $b) => $b['score'] <=> $a['score']);

        $processedCount = 0;
        $maxToProcess = count($listStart);

        for ($i = 0; $i < count($listStart) && $processedCount < $maxToProcess; $i++) {
            $candidate = $listStart[$i];

            if (!$candidate) continue; // Skip si déjà traité

            $teacherId = $candidate['id_teacher'];
            $internshipId = $candidate['internship_identifier'];
            $type = strtolower(trim($candidate['type']));

            // Vérifier si déjà assigné (utilisation du set pour O(1))
            if (isset($setEleveFinal[$internshipId])) {
                continue;
            }

            $assignedTopIntern = $assignedCountsIntern[$teacherId] ?? 0;
            $assignedTopApprentice = $assignedCountsApprentice[$teacherId] ?? 0;
            $listTopIntern = $listTeacherMaxIntern[$teacherId] ?? 0;
            $listTopApprentice = $listTeacherMaxApprentice[$teacherId] ?? 0;

            $assigned = false;

            if ($type === 'internship' && $assignedTopIntern < $listTopIntern) {
                $listFinal[] = $candidate;
                $listEleveFinal[] = $internshipId;
                $setEleveFinal[$internshipId] = true;
                ++$assignedCountsIntern[$teacherId];
                $assigned = true;

            } elseif ($type === 'alternance' && $assignedTopApprentice < $listTopApprentice) {
                $listFinal[] = $candidate;
                $listEleveFinal[] = $internshipId;
                $setEleveFinal[$internshipId] = true;
                ++$assignedCountsApprentice[$teacherId];
                $assigned = true;
            }

            if ($assigned) {
                $processedCount++;
            }
        }

        return [$listFinal, $assignedCountsIntern, $assignedCountsApprentice];
    }

    public function createListInternship(): false|array
    {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Internship.Internship_identifier FROM Internship JOIN Study_at ON Internship.Student_number = Study_at.Student_number WHERE Department_name IN ($placeholders)";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createListAssociate(): false|array
    {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT internship.Id_teacher, internship.Internship_identifier FROM internship JOIN Study_at ON internship.Student_number = Study_at.Student_number WHERE Department_name IN ($placeholders) AND internship.Id_teacher IS NOT NULL";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    public function insertResponsible(): string
    {
        $query = 'UPDATE internship SET Id_teacher = :Id_teacher WHERE Internship_identifier = :Internship_identifier';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':Internship_identifier', $_POST['searchInternship']);
        $stmt->bindParam(':Id_teacher', $_POST['searchTeacher']);
        $stmt->execute();
        return "Association " . $_POST['searchTeacher'] . " et " . $_POST['searchInternship'] . " enregistrée.";
    }

    public function insertIsResponsible(String $id_prof, String $Internship_id, float $Score): string
    {
        $query = 'UPDATE internship SET Id_teacher = :id_prof, Relevance_score = :Score WHERE Internship_identifier = :Internship_id';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':id_prof', $id_prof);
        $stmt->bindParam(':Score', $Score);
        $stmt->bindParam(':Internship_id', $Internship_id);
        $stmt->execute();
        return "Association " . $id_prof . " et " . $Internship_id . " enregistrée. <br>";
    }

    public function getStudentHistory(string $student_number): mixed
    {
        $query = "SELECT End_date_internship FROM Internship WHERE Student_number = :student_number AND Start_date_internship < CURRENT_DATE";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getInternshipStudent(string $student): false|array
    {
        $query = 'SELECT internship_identifier, company_name, internship_subject, address, internship.id_teacher, teacher_name, teacher_firstname, formation, class_group FROM internship JOIN student ON internship.student_number = student.student_number LEFT JOIN teacher ON internship.id_teacher = teacher.id_teacher WHERE internship.student_number = :student AND end_date_internship > CURRENT_DATE ORDER BY end_date_internship ASC LIMIT 1';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getInternshipAddress(string $studentId): string|false
    {
        if ($studentId !== $_POST['student_id']) {
            return false;
        }

        $pdo = $this->_db;

        $query = 'SELECT address FROM internship WHERE internship.student_number = :student_number';

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':student_number', $studentId);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function updateSearchedStudentInternship(bool $add, string $teacher, string $internship): true|string
    {
        $current_requests = $this->getRequests($teacher);
        if ($add) {
            if (!in_array($internship, $current_requests)) {
                $query = 'INSERT INTO is_requested(id_teacher, internship_identifier) VALUES(:teacher, :internship)';
                $stmt = $this->_db->getConn()->prepare($query);
                $stmt->bindParam(':teacher', $teacher);
                $stmt->bindParam(':internship', $internship);
            } else {
                return true;
            }
        } else {
            if (in_array($internship, $current_requests)) {
                $query = 'DELETE FROM is_requested WHERE id_teacher = :teacher AND internship_identifier = :internship';
                $stmt = $this->_db->getConn()->prepare($query);
                $stmt->bindParam(':teacher', $teacher);
                $stmt->bindParam(':internship', $internship);
            } else {
                return true;
            }
        }
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function updateRequests(array $requests, string $teacher): bool|string
    {
        $current_requests = $this->getRequests($teacher);
        if (!$current_requests) {
            $current_requests = [];
        }

        $to_add = array_diff($requests, $current_requests);
        $to_delete = array_diff($current_requests, $requests);

        foreach ($to_add as $request) {
            $query = 'INSERT INTO is_requested(id_teacher, internship_identifier) VALUES(:teacher, :internship)';
            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':teacher', $teacher);
            $stmt->bindParam(':internship', $request);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                return $e->getMessage();
            }
        }

        foreach ($to_delete as $request) {
            $query = 'DELETE FROM is_requested WHERE id_teacher = :teacher AND internship_identifier = :internship';
            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':teacher', $teacher);
            $stmt->bindParam(':internship', $request);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                return $e->getMessage();
            }
        }
        return true;
    }

    public function getRequests(string $teacher): false|array
    {
        if (isset($this->cache['getRequests'][$teacher])) {
            return $this->cache['getRequests'][$teacher];
        }

        $query = 'SELECT internship_identifier FROM is_requested WHERE id_teacher = :teacher';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->cache['getRequests'][$teacher] = $result;
        return $result;
    }

    public function internshipExists(string $idTeacher, string $studentNumber): bool
    {
        $query = "SELECT COUNT(*) FROM internship WHERE id_teacher = :id_teacher AND student_number = :student_number";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindValue(':id_teacher', $idTeacher);
        $stmt->bindValue(':student_number', $studentNumber);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function paginateStage(string $identifier, int $start, int $length, string $search = '', array $order = []): array
    {
        $columns = [
            'prof',
            'history',
            'distance',
            'discipline',
            'score',
            'entreprise',
        ];

        // Build the ORDER BY clause if $order is provided
        $orderClause = '';
        if (!empty($order) && isset($columns[$order[0]['column']])) {
            $column = $columns[$order[0]['column']];
            $dir = strtoupper($order[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
            $orderClause = " ORDER BY $column $dir";
        }

        // --- COUNT QUERY ---
        $countQuery = "
        WITH cte_histo AS (
            SELECT id_teacher, array_agg(start_date_internship ORDER BY id_teacher) AS history
            FROM internship
            WHERE end_date_internship < NOW()
            GROUP BY id_teacher
        )
        SELECT COUNT(DISTINCT i.internship_identifier) AS total
        FROM internship i
        JOIN teacher t ON TRUE
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
            WHERE d2.id_teacher = t.id_teacher AND d2.internship_identifier = i.internship_identifier
            ORDER BY distance ASC LIMIT 1
        ) d ON TRUE
        WHERE i.internship_identifier = :identifier ";

        if (!empty($search)) {
            $countQuery .= " AND (
            CONCAT(t.teacher_firstname, ' ', t.teacher_name) ILIKE :search OR
            it.discipline_name ILIKE :search OR
            i.company_name ILIKE :search
        )";
        }

        $countStmt = $this->_db->getConn()->prepare($countQuery);
        $countStmt->bindValue(':identifier', $identifier);
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $countStmt->bindValue(':search', $searchParam);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // --- DATA QUERY ---
        $dataQuery = "
        WITH cte_histo AS (
            SELECT id_teacher, array_agg(start_date_internship ORDER BY id_teacher) AS history
            FROM internship
            WHERE end_date_internship < NOW()
            GROUP BY id_teacher
        )
        SELECT DISTINCT
            CONCAT(t.teacher_firstname, ' ', t.teacher_name) AS prof,
            h.history,
            d.distance AS distance,
            it.discipline_name AS discipline,
            i.relevance_score AS score,
            i.company_name AS entreprise,
            i.student_number,
            i.internship_identifier,
            ha.address AS teacher_address
        FROM internship i
        JOIN teacher t ON TRUE
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
            WHERE d2.id_teacher = t.id_teacher AND d2.internship_identifier = i.internship_identifier
            ORDER BY distance ASC LIMIT 1
        ) d ON TRUE
        WHERE i.internship_identifier = :identifier 
        order by i.relevance_score ASC";

        if (!empty($search)) {
            $dataQuery .= " AND (
            CONCAT(t.teacher_firstname, ' ', t.teacher_name) ILIKE :search OR
            it.discipline_name ILIKE :search OR
            i.company_name ILIKE :search
        )";
        }

        $dataQuery .= $orderClause . " LIMIT :limit OFFSET :offset";

        $dataStmt = $this->_db->getConn()->prepare($dataQuery);
        $dataStmt->bindValue(':identifier', $identifier);
        if (!empty($search)) {
            $dataStmt->bindValue(':search', $searchParam);
        }
        $dataStmt->bindValue(':limit', $length, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $start, PDO::PARAM_INT);
        $dataStmt->execute();

        $results = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $results,
            'total' => (int) $total
        ];
    }

    public function getInternshipById(string $internshipId)
    {
        $stmt = $this->_db->getConn()->prepare("SELECT * FROM internship where internship_identifier = :internshipId ");
        $stmt->bindValue(':internshipId', $internshipId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
