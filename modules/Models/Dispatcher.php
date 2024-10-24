<?php

namespace Blog\Models;

use Database;
use PDO;
use PDOException;

class Dispatcher{
    private Database $db;

    public function __construct(Database $db){
        $this->db = $db;
    }

    /**
     * @return array|false
     */
    public function getCriteria()
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
        $departments = $this->getDepTeacher($identifier);
        foreach($departments as $listDepTeacher) {
            foreach($listDepTeacher as $department) {
                $newList = $this->getStudentsPerDepartment($department);
                if ($newList)  {
                    $studentsList = array_merge($studentsList, $newList);
                }
            }
        }

        $result = array();

        foreach($studentsList as $student) {
            $distanceMin = 50;
            $relevance= $this->scoreDiscipSubject($student['student_number'], $identifier);

            $dictValues = array(
                "A été responsable" => $this->getInternships($student['student_number']),
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

            $newList = ["id_prof" => $identifier, "id_eleve" => $student['student_number'], "score" => $scoreFinal, "type" => $student['type']];

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
        $query = 'SELECT Teacher.Id_teacher, Maxi_number_trainees FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                    where Department_name = :Role_departement';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_department']);
        $stmt->execute();
        $listTeacherMax = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $query = 'SELECT Teacher.Id_teacher, 0 FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                     WHERE Department_name = :Role_departement';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_department']);
        $stmt->execute();
        $listteacherIntership = $stmt->fetchAll();

        $query = 'SELECT Teacher.Id_teacher, COUNT(Student_number) FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                     JOIN Is_responsible ON Teacher.Id_Teacher = Is_responsible.Id_Teacher
                     WHERE Department_name = :Role_departement
                     GROUP BY Teacher.Id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_department']);
        $stmt->execute();
        $tmp_count_student = $stmt->fetchAll();

        foreach ($tmp_count_student as $count_student) {
            $listteacherIntership[$count_student[0]] = $count_student[1];
        }

        $query = 'SELECT Student_number FROM Is_responsible';
        $stmt = $db->getConn()->prepare($query);
        $stmt->execute();
        $tmp_student = $stmt->fetchAll();

        $listFinal = [];
        $listStart = [];

        foreach ($listteacherIntership as $teacher) {
            if (gettype($teacher) === 'integer') {
                continue;
            }
            foreach ($this->calculateRelevanceTeacherStudents($teacher[0], $dicoCoef) as $associate){
                $listStart[] = $associate;
            }
        }

        foreach ($listStart as $key => $tuplestart) {
            if (in_array($tuplestart['id_eleve'], $tmp_student)) {
                unset($listStart[$key]);
            }
        }

        if (empty($listStart)) {
            return[[],[]];
        }

        while (count($listStart) > 0){
            $tab_max_table = $listStart[1];
            foreach ($listStart as $association){
                if ($association['score'] > $tab_max_table['score']) {
                    $tab_max_table = $association;
                }
            }
            if ($listTeacherMax[$tab_max_table['id_prof']] === $listteacherIntership[$tab_max_table['id_prof']]){
                unset($listStart[array_search($tab_max_table['id_prof'], $listStart)]);
            }
            else {
                $listFinal[] = $tab_max_table;
                unset($listStart[array_search($tab_max_table, $listStart)]);
                if ($tab_max_table['type'] === 'Intership') {
                    $listteacherIntership[$tab_max_table['id_prof']] = $listteacherIntership[$tab_max_table['id_prof']] + 2;
                } else {
                    $listteacherIntership[$tab_max_table['id_prof']] = $listteacherIntership[$tab_max_table['id_prof']] + 1;
                }
            }
        }
        return [$listFinal, $listteacherIntership];
    }
}