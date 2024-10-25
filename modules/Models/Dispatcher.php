<?php

namespace Blog\Models;

use Database;
use PDO;
use PDOException;

class Dispatcher{
    private Database $db;
    private \Blog\Models\GlobalModel $globalModel;

    public function __construct(Database $db, \Blog\Models\GlobalModel $globalModel){
        $this->db = $db;
        $this->globalModel = $globalModel;
    }
    public function getCriteria(): false|array
    {
        $db = $this->db;

        $query = 'SELECT * FROM Backup where user_id = :user_id';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function  getCoef($identifier): array {
        $dictCoef = [];

        $pdo = $this->db;

        $query = "SELECT Name_criteria, Coef FROM Backup
              WHERE user_id = :user_id";

        $stmt2 = $pdo->getConn()->prepare($query);
        $stmt2->bindValue(':user_id', $identifier);
        $stmt2->execute();

        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $dictCoef[$row['name_criteria']] = $row['coef'];
        }

        return $dictCoef;
    }
    public function getDepTeacher($identifier): false|array {
        $query = 'SELECT department_name
                    FROM teaches
                    WHERE  id_teacher = :teacher';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $identifier);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStudentsPerDepartment(string $department): false|array {
        $query = 'SELECT *
                    FROM student
                    JOIN study_at
                    ON student.student_number = study_at.student_number
                    JOIN internship
                    ON student.student_number = internship.student_number
                    WHERE department_name = :dep
                    AND internship.start_date_internship > CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':dep', $department);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getInternships(string $student): false|array {
        $query = 'SELECT id_teacher, student_number, responsible_start_date, responsible_end_date
                    FROM is_responsible
                    WHERE student_number = :student
                    AND responsible_end_date < CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function scoreDiscipSubject(string $studentId, string $identifier): float {
        $query1 = 'SELECT discipline_name FROM is_taught WHERE id_teacher = :id';
        $stmt1 = $this->db->getConn()->prepare($query1);
        $stmt1->bindParam(':id', $identifier);
        $stmt1->execute();
        $result = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $searchTerm = "";

        for($i = 0; $i < count($result); $i++) {
            $searchTerm .= $result[$i]['discipline_name'];
            if($i < count($result) - 1) $searchTerm .= '_';
        }

        $pdo = $this->db;
        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' | ', explode('_', $searchTerm));

        $query2 = "SELECT student_number, keywords, ts_rank_cd(to_tsvector('french', keywords), to_tsquery('french', :searchTerm), 32) AS rank
                    FROM internship
                    WHERE to_tsquery('french', :searchTerm) @@ to_tsvector('french', keywords)
                    AND student_number = :studentId
                    AND start_date_internship > CURRENT_DATE";

        $stmt2 = $pdo->getConn()->prepare($query2);
        $stmt2->bindValue(':searchTerm', $tsQuery);
        $stmt2->bindValue(':studentId', $studentId);
        $stmt2->execute();

        $result = $stmt2->fetch(PDO::FETCH_ASSOC);

        if(!$result) return 0;
        return $result["rank"]*5;
    }

    public function calculateRelevanceTeacherStudents($identifier, $dictCoef): array
    {
        $studentsList = array();
        $departments = $this->globalModel->getDepTeacher($identifier);
        foreach($departments as $listDepTeacher) {
            foreach($listDepTeacher as $department) {
                $newList = $this->globalModel->getStudentsPerDepartment($department);
                if ($newList)  {
                    $studentsList = array_merge($studentsList, $newList);
                }
            }
        }

        $result = array();

        foreach($studentsList as $student) {
            $distanceMin = $this->globalModel->getDistance($student['student_number'], $identifier);
            $relevance= $this->globalModel->scoreDiscipSubject($student['student_number'], $identifier);

            $dictValues = array(
                "A été responsable" => $this->globalModel->getInternships($student['student_number']),
                "Distance" => $distanceMin,
                "Cohérence" => round($relevance, 2));

            $totalScore = 0;
            $totalCoef = 0;
            foreach ($dictValues as $criteria => $value) {
                if (isset($dictCoef[$criteria])) {
                    $coef = $dictCoef[$criteria];

                    switch ($criteria) {
                        case 'Distance':
                            $scoreDuration = $coef / (1 + 0.02 * $value);
                            $totalScore += $scoreDuration;
                            break;

                        case 'A été responsable':
                            $scoreInternship = ($value > 0) ? $coef : 0;
                            $totalScore += $scoreInternship;
                            break;

                        case 'Cohérence':
                            $scoreRelevance = $value * $coef;
                            $totalScore += $scoreRelevance;
                            break;

                        default:
                            $totalScore += $value * $coef;
                            break;
                    }
                    $totalCoef += $coef;
                }
            }
            $scoreFinal = ($totalScore * 5) / $totalCoef;

            $newList = ["id_prof" => $identifier, "id_eleve" => $student['student_number'], "score" => round($scoreFinal, 2), "type" => $student['type']];

            if (!empty($newList)) {
                $result[] = $newList;
            }
        }

        if (!empty($result)) {
            return $result;
        }
        return [[]];
    }

    public function dispatcher(array $dicoCoef): array
    {
        $db = $this->db;

        $query = 'SELECT Teacher.Id_teacher, 
                      MAX(maxi_number_trainees) AS Max_trainees, 
                      COUNT(Is_responsible.Student_number) AS Current_count
                FROM Teacher
                JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                LEFT JOIN Is_responsible ON Teacher.Id_Teacher = Is_responsible.Id_Teacher
                WHERE Department_name = :Role_departement
                GROUP BY Teacher.Id_teacher';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_department']);
        $stmt->execute();

        $teacherData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $listTeacherMax = [];
        $listteacherIntership = [];
        foreach ($teacherData as $teacher) {
            $listTeacherMax[$teacher['id_teacher']] = $teacher['max_trainees'];
            $listteacherIntership[$teacher['id_teacher']] = $teacher['current_count'] ?: 0;
        }

        $query = 'SELECT Is_responsible.Student_number FROM Is_responsible JOIN Study_at ON Study_at.Student_number = Is_responsible.Student_number 
                    WHERE Department_name = :Role_departement';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_department']);
        $stmt->execute();
        $responsibleStudents = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $listFinal = [];
        $listStart = [];
        $listEleveFinal = [];

        foreach ($listteacherIntership as $teacherId => $currentCount) {
            foreach ($this->calculateRelevanceTeacherStudents($teacherId, $dicoCoef) as $association) {
                if (!in_array($association['id_eleve'], $responsibleStudents)) {
                    $listStart[] = $association;
                }
                else {
                    $listEleveFinal[] = $association['id_eleve'];
                }
            }
        }

        if (empty($listStart)) {
            return [[], []];
        }

        $assignedCounts = $listteacherIntership;

        while (!empty($listStart)) {
            usort($listStart, fn($a, $b) => $b['score'] <=> $a['score']);
            $topCandidate = $listStart[0];

            if ($assignedCounts[$topCandidate['id_prof']] < $listTeacherMax[$topCandidate['id_prof']] && !in_array($topCandidate['id_eleve'], $listEleveFinal)) {
                $listFinal[] = $topCandidate;
                $listEleveFinal[] = $topCandidate['id_eleve'];
                $assignedCounts[$topCandidate['id_prof']] += ($topCandidate['type'] === 'Intership') ? 2 : 1;

            }
            array_shift($listStart);


        }

        return [$listFinal, $assignedCounts];
    }

    public function createListTeacher() {
        $query = 'SELECT Teacher.Id_teacher FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                    where Department_name = :Role_department';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Role_department', $_SESSION['role_department']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createListStudent() {
        $query = 'SELECT Student.Student_number FROM Student JOIN Study_at ON Student.Student_number = Study_at.Student_number
                    where Department_name = :Role_department';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Role_department', $_SESSION['role_department']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createListAssociate() {
        $query = 'SELECT Is_responsible.Student_number, Is_responsible.Id_teacher FROM Is_responsible JOIN Study_at ON Is_responsible.Student_number = Study_at.Student_number
                    where Department_name = :Role_department';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Role_department', $_SESSION['role_department']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function insertResponsible() {
        $query = 'INSERT INTO Is_responsible (Id_teacher, Student_number, responsible_start_date, responsible_end_date) VALUES (:Id_teacher, :Student_number, :Start_date, :End_date)';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Student_number', $_POST['Student_number']);
        $stmt->bindParam(':Id_teacher', $_POST['Id_teacher']);
        $stmt->bindParam(':Start_date', $_POST['Start_date']);
        $stmt->bindParam(':End_date', $_POST['End_date']);
        $stmt->execute();
        return "Association " . $_POST['Id_teacher'] . " et " . $_POST['Student_number'] . " enregistré.";
    }

    public function insertIs_responsible() {
        for ($i = 0; $i<count($_POST['id_eleve']); $i++ ) {
            $query = 'SELECT Start_date_internship, End_date_internship FROM Internship
                    where Student_number = :Student_number';
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':Student_number', $_POST['id_eleve'][$i]);
            $stmt->execute();
            $DateIntership = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $query = 'INSERT INTO Is_responsible (Id_teacher, Student_number, Relevance_score, responsible_start_date, responsible_end_date) VALUES (:Id_teacher, :Student_number, :score, :Start_date, :End_date)';
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':Student_number', $_POST['id_eleve'][$i]);
            $stmt->bindParam(':Id_teacher', $_POST['id_prof'][$i]);
            $stmt->bindParam(':score', $_POST['score'][$i]);
            $stmt->bindParam(':Start_date', $DateIntership[0]['start_date_internship']);
            $stmt->bindParam(':End_date', $DateIntership[0]['end_date_internship']);
            $stmt->execute();
        }
    }
}