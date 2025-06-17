<?php
/**
 * Fichier contenant le modèle associé aux enseignants
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

class Teacher extends Model
{
    private Database $_db;
    private $cache = [];

    public function __construct(Database $db)
    {
        parent::__construct($db);
        $this->_db = $db;
    }

    public function getFullName(string $idTeacher): string
    {
        $stmt = $this->_db->getConn()->prepare("SELECT CONCAT(teacher_firstname, ' ', teacher_name) FROM teacher where id_teacher = :idTeacher ");
        $stmt->bindValue(':idTeacher', $idTeacher);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getAddress(string $id_teacher): false|array
    {
        if (isset($this->cache['getAddress'][$id_teacher])) {
            return $this->cache['getAddress'][$id_teacher];
        }

        $db = $this->_db;
        $query ='SELECT ha.address, at.type_complet 
              FROM has_address ha
              JOIN address_type at ON ha.type = at.type
              WHERE ha.id_teacher = :id_teacher
              ORDER BY CASE ha.type
                  WHEN \'Domicile_1\' THEN 1
                  WHEN \'Domicile_2\' THEN 2
                  WHEN \'Travail_1\' THEN 3
                  WHEN \'Travail_2\' THEN 4
                  WHEN \'Batiment\' THEN 5
                  ELSE 6
              END';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_teacher', $id_teacher);
        $stmt->execute();

        $result = $stmt->fetchAll($db->getConn()::FETCH_ASSOC);
        $this->cache['getAddress'][$id_teacher] = $result;
        return $result;
    }

    public function createListTeacher(): false|array
    {
        if (isset($this->cache['createListTeacher'])) {
            return $this->cache['createListTeacher'];
        }

        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher FROM Teacher JOIN Has_role ON Teacher.Id_Teacher = Has_role.User_id WHERE Department_name IN ($placeholders)";

        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->cache['createListTeacher'] = $result;
        return $result;
    }

    public function correspondTermsTeacher(): array|false
    {
        $searchTerm = $_POST['search'] ?? '';
        $pdo = $this->_db;

        $searchTerm = trim($searchTerm);

        $query = "SELECT id_teacher, teacher_name, teacher_firstname FROM teacher WHERE teacher_name ILIKE :searchTerm OR teacher_firstname ILIKE :searchTerm ORDER BY id_teacher ASC";

        $searchTerm = "$searchTerm%";

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMaxNumberTrainees(string $teacher): false|array
    {
        if (isset($this->cache['getMaxNumberTrainees'][$teacher])) {
            return $this->cache['getMaxNumberTrainees'][$teacher];
        }

        $query = 'SELECT maxi_number_intern AS intern, maxi_number_apprentice AS apprentice FROM teacher WHERE id_teacher = :teacher';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        $result = $stmt->fetch();

        $this->cache['getMaxNumberTrainees'][$teacher] = $result;
        return $result;
    }

    public function updateMaxiNumberTrainees(string $teacher, int $intern, int $apprentice): bool|string
    {
        if (!($intern > 0 || $apprentice > 0)) {
            return false;
        }

        $query = 'UPDATE teacher SET ';
        if ($intern > 0) {
            $query .= 'maxi_number_intern = :intern';
            if ($apprentice > 0) {
                $query .= ', maxi_number_apprentice = :apprentice';
            }
        } else {
            $query .= 'maxi_number_apprentice = :apprentice';
        }
        $query .= ' WHERE id_teacher = :teacher';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        if ($intern > 0) {
            $stmt->bindParam(':intern', $intern);
        }
        if ($apprentice > 0) {
            $stmt->bindParam(':apprentice', $apprentice);
        }

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function getDisciplines(string $id_teacher): false|array
    {
        if (isset($this->cache['getDisciplines'][$id_teacher])) {
            return $this->cache['getDisciplines'][$id_teacher];
        }

        $pdo = $this->_db;

        $query = "SELECT discipline_name FROM is_taught WHERE id_teacher = :id";
        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindParam(':id', $id_teacher);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->cache['getDisciplines'][$id_teacher] = $result;
        return $result;
    }

    public function getStudentsList(array $departments, string $identifier, Internship $internshipModel, Department $departmentModel): array
    {
        $studentsList = [];
        foreach ($departments as $department) {
            $newList = $departmentModel->getInternshipsPerDepartment($department);
            if ($newList) {
                $studentsList = array_merge($studentsList, $newList);
            }
        }

        $studentsList = array_unique($studentsList, 0);

        $requests = $internshipModel->getRequests($identifier);
        if (!$requests) {
            $requests = [];
        }

        foreach ($studentsList as &$row) {
            $internships = $internshipModel->getInternships($row['student_number']);
            $row['year'] = "";
            $row['internshipTeacher'] = $internships ? $internshipModel->getInternshipTeacher($internships, $identifier, $row['year']) : 0;
            $row['requested'] = in_array($row['internship_identifier'], $requests);
            $row['duration'] = $internshipModel->getDistance($row['internship_identifier'], $identifier, isset($row['id_teacher']));
        }

        return $studentsList;
    }

    public function getDepTeacher(string $teacher_id): false|array
    {
        if (isset($this->cache['getDepTeacher'][$teacher_id])) {
            return $this->cache['getDepTeacher'][$teacher_id];
        }

        $query = 'SELECT DISTINCT department_name FROM has_role WHERE user_id = :teacher_id';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->cache['getDepTeacher'][$teacher_id] = $result;
        return $result;
    }
    public function getTeacherAddress(string $teacher_id): ?string
    {
        $query = 'SELECT address FROM has_address WHERE id_teacher = :teacher_id AND type = \'Domicile_1\'';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si le résultat n'est pas vide et si l'adresse existe
        if ($result && isset($result['address'])) {
            return $result['address'];
        }

        // Fallback
        $query = 'SELECT address FROM has_address WHERE id_teacher = :teacher_id ORDER BY 
              CASE type 
                  WHEN \'Domicile_1\' THEN 1
                  WHEN \'Domicile_2\' THEN 2
                  WHEN \'Travail_1\' THEN 3
                  WHEN \'Travail_2\' THEN 4
                  WHEN \'Batiment\' THEN 5
                  ELSE 6
              END LIMIT 1';

        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && isset($result['address']) ? $result['address'] : null;    }

    public function paginateAsk(string $identifier, int $start, int $length, string $search = '', array $order = []): array
    {
        $columns = [
            'CONCAT(s.student_firstname, \' \', s.student_name)', // student
            's.formation',
            's.class_group',
            'h.history',
            'i.company_name',
            'i.internship_subject',
            'i.address',
            'd.distance',
            'i.id_teacher'
        ];
        $countQuery = "WITH cte_histo AS (SELECT id_teacher FROM internship WHERE end_date_internship < NOW() GROUP BY id_teacher), cte_teacher_dep as (select distinct hr.department_name from has_role hr where user_id = :id_teacher) SELECT COUNT(*) as total FROM student s JOIN internship i ON s.student_number = i.student_number LEFT JOIN cte_histo h ON i.id_teacher = h.id_teacher LEFT JOIN distance d ON :id_teacher = d.id_teacher AND i.internship_identifier = d.internship_identifier left join has_role hr on s.student_number = hr.user_id WHERE i.end_date_internship > NOW() and i.id_teacher is null and i.internship_identifier not in (
select internship_identifier from is_requested)";

        if (!empty($search)) {
            $countQuery .= ' AND (s.student_name ILIKE :search OR s.student_firstname ILIKE :search OR s.formation ILIKE :search OR s.class_group ILIKE :search OR i.company_name ILIKE :search OR i.internship_subject ILIKE :search OR i.address ILIKE :search)';
        }

        $countStmt = $this->_db->getConn()->prepare($countQuery);
        $countStmt->bindValue(':id_teacher', $identifier);
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $countStmt->bindValue(':search', $searchParam);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $dataQuery = "WITH cte_histo AS (SELECT id_teacher, array_agg(start_date_internship ORDER BY id_teacher) AS history FROM internship WHERE end_date_internship < NOW() GROUP BY id_teacher), cte_teacher_dep as (select distinct hr.department_name from has_role hr where user_id = :id_teacher2) SELECT CONCAT(s.student_firstname, ' ', s.student_name) AS student, s.formation, s.class_group AS \"group\", h.history, i.company_name AS company, i.id_teacher, i.internship_subject AS subject, i.address, d.distance, i.internship_identifier FROM student s JOIN internship i ON s.student_number = i.student_number LEFT JOIN cte_histo h ON i.id_teacher = h.id_teacher LEFT JOIN distance d ON :id_teacher2 = d.id_teacher AND i.internship_identifier = d.internship_identifier left join has_role hr on s.student_number = hr.user_id WHERE i.end_date_internship > NOW() and i.id_teacher is null and i.internship_identifier not in (
select internship_identifier from is_requested)";

        if (!empty($search)) {
            $dataQuery .= ' AND (s.student_name ILIKE :search OR s.student_firstname ILIKE :search OR s.formation ILIKE :search OR s.class_group ILIKE :search OR i.company_name ILIKE :search OR i.internship_subject ILIKE :search OR i.address ILIKE :search)';
        }

        if (!empty($order) && isset($order['column']) && isset($columns[$order['column']])) {
            $columnExpr = $columns[$order['column']];
            $dataQuery .= ' ORDER BY ' . $columnExpr . ' ' . (strtoupper($order['dir']) === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $dataQuery .= ' ORDER BY s.student_name ASC';
        }


        $dataQuery .= ' LIMIT :limit OFFSET :offset';

        $dataStmt = $this->_db->getConn()->prepare($dataQuery);
        $dataStmt->bindValue(':id_teacher2', $identifier);
        if (!empty($search)) {
            $dataStmt->bindValue(':search', $searchParam);
        }
        $dataStmt->bindValue(':limit', $length, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $start, PDO::PARAM_INT);
        $dataStmt->execute();

        $results = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $results,
            'total' => (int)$total
        ];
    }



    public function paginateAccount(string $identifier, int $start, int $length, string $search = '', array $order = []): array
    {
        $columns = [
            'student',
            'formation',
            'group',
            'company',
            'subject',
            'end_date',
            'address',
            'distance'
        ];
        $countQuery = "SELECT COUNT(*) as total FROM student s JOIN internship i ON s.student_number = i.student_number LEFT JOIN distance d ON i.id_teacher = d.id_teacher AND i.internship_identifier = d.internship_identifier WHERE i.end_date_internship > NOW() AND i.id_teacher = :identifier";

        if (!empty($search)) {
            $countQuery .= ' AND (s.student_name ILIKE :search OR s.student_firstname ILIKE :search OR s.formation ILIKE :search OR s.class_group ILIKE :search OR i.company_name ILIKE :search OR i.internship_subject ILIKE :search OR i.address ILIKE :search)';
        }

        $countStmt = $this->_db->getConn()->prepare($countQuery);
        $countStmt->bindValue(':identifier', $identifier);
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $countStmt->bindValue(':search', $searchParam);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $dataQuery = "SELECT CONCAT(s.student_firstname, ' ', s.student_name) AS student, s.formation, s.class_group AS group, i.company_name AS company, i.internship_subject AS subject, i.end_date_internship AS end_date, i.address, d.distance FROM student s JOIN internship i ON s.student_number = i.student_number LEFT JOIN distance d ON i.id_teacher = d.id_teacher AND i.internship_identifier = d.internship_identifier WHERE i.end_date_internship > NOW() AND i.id_teacher = :identifier";

        if (!empty($search)) {
            $dataQuery .= ' AND (s.student_name ILIKE :search OR s.student_firstname ILIKE :search OR s.formation ILIKE :search OR s.class_group ILIKE :search OR i.company_name ILIKE :search OR i.internship_subject ILIKE :search OR i.address ILIKE :search)';
        }
        if (!empty($order) && isset($order['column']) && isset($columns[$order['column']]) && $columns[$order['column']] !== null) {
            $dataQuery .= ' ORDER BY ' . $columns[$order['column']] . ' ' . (strtoupper($order['dir']) === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $dataQuery .= ' ORDER BY s.student_name ASC';
        }

        $dataQuery .= ' LIMIT :limit OFFSET :offset';

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
            'total' => (int)$total
        ];
    }
    public function updateCapacities(string $idTeacher, ?int $maxStage, ?int $maxAlternance): bool|string
    {
        try {
            $query = "UPDATE teacher SET 
                    maxi_number_intern = COALESCE(:maxStage, maxi_number_intern),
                    maxi_number_apprentice = COALESCE(:maxAlternance, maxi_number_apprentice)
                  WHERE id_teacher = :idTeacher";

            $stmt =  $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':idTeacher', $idTeacher);
            $stmt->bindParam(':maxStage', $maxStage);
            $stmt->bindParam(':maxAlternance', $maxAlternance);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function getAllTeachers()
    {
        $stmt = $this->_db->getConn()->prepare("SELECT 
    t.*, 
    it.discipline_name 
FROM 
    teacher t
LEFT JOIN LATERAL (
    SELECT 
        it.discipline_name 
    FROM 
        is_taught it 
    WHERE 
        it.id_teacher = t.id_teacher
    LIMIT 1
) it ON true;
");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
