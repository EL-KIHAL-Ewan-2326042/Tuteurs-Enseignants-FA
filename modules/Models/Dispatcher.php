<?php

namespace Blog\Models;

use Database;
use PDO;

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

        $query = 'SELECT Student_number FROM Is_responsible';
        $stmt = $db->getConn()->prepare($query);
        $stmt->execute();
        $responsibleStudents = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $listFinal = [];
        $listStart = [];

        foreach ($listteacherIntership as $teacherId => $currentCount) {
            foreach ($this->calculateRelevanceTeacherStudents($teacherId, $dicoCoef) as $association) {
                if (!in_array($association['id_eleve'], $responsibleStudents)) {
                    $listStart[] = $association;
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

            if ($assignedCounts[$topCandidate['id_prof']] < $listTeacherMax[$topCandidate['id_prof']]) {
                $listFinal[] = $topCandidate;

                $assignedCounts[$topCandidate['id_prof']] += ($topCandidate['type'] === 'Intership') ? 2 : 1;

                array_shift($listStart);
            } else {
                array_shift($listStart);
            }
        }

        return [$listFinal, $assignedCounts];
    }
}