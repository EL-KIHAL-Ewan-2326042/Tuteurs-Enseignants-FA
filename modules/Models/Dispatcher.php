<?php

namespace Blog\Models;

use Includes;
use PDO;
use PDOException;

class Dispatcher{
    private Includes\Database $db;
    private \Blog\Models\GlobalModel $globalModel;

    public function __construct(Includes\Database $db, \Blog\Models\GlobalModel $globalModel){
        $this->db = $db;
        $this->globalModel = $globalModel;
    }

    /**
     * Renvoie les criteres de tri associe a un utilisateur
     * @return false|array
     */
    public function getCriteria(): false|array
    {
        $db = $this->db;

        $query = 'SELECT * FROM Backup where user_id = :user_id';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Renvoie pour un professeur un couple avec tous les etudiants de son departement
     * @param $identifier l'identifiant du professeur
     * @param $dictCoef array cle->nom_critere et valeur->coef
     * @return array|array[] array contenant id_prof, id_eleve et le Score associe
     */
    public function calculateRelevanceTeacherStudents($identifier, array $dictCoef): array
    {
        $studentsList = array();
        // On recupere la liste des departement de l'eleve
        $departments = $this->globalModel->getDepTeacher($identifier);
        foreach($departments as $listDepTeacher) {
            foreach($listDepTeacher as $department) {
                // Pour chaque departement, on recupere les eleve
                $newList = $this->globalModel->getStudentsPerDepartment($department);
                if ($newList)  {
                    // Les eleves sont rajoutes dans la liste finale
                    $studentsList = array_merge($studentsList, $newList);
                }
            }
        }

        $result = array();

        // Pour chaque relation tuteur-etudiant, on calcul leur Score qu'on met dans un array final
        foreach($studentsList as $student) {
            $distanceMin = $this->globalModel->getDistance($student['student_number'], $identifier);
            $relevance= $this->globalModel->ScoreDiscipSubject($student['student_number'], $identifier);

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
                            $ScoreDuration = $coef / (1 + 0.02 * $value);
                            $totalScore += $ScoreDuration;
                            break;

                        case 'A été responsable':
                            $ScoreInternship = ($value > 0) ? $coef : 0;
                            $totalScore += $ScoreInternship;
                            break;

                        case 'Cohérence':
                            $ScoreRelevance = $value * $coef;
                            $totalScore += $ScoreRelevance;
                            break;

                        default:
                            $totalScore += $value * $coef;
                            break;
                    }
                    $totalCoef += $coef;
                }
            }
            // Score normalise sur 5
            $ScoreFinal = ($totalScore * 5) / $totalCoef;

            $newList = ["id_teacher" => $identifier, "student_number" => $student['student_number'], "score" => round($ScoreFinal, 2), "type" => $student['type']];

            if (!empty($newList)) {
                $result[] = $newList;
            }
        }

        if (!empty($result)) {
            return $result;
        }
        return [[]];
    }

    /**
     * S'occupe de trouver la meilleure combinaison possible tuteur-stage et le renvoie sous forme de tableau
     * @param array $dicoCoef dictionnaire cle->nom_critere et valeur->coef
     * @return array|array[] resultat final sous forme de matrixe
     */
    public function dispatcher(array $dicoCoef): array
    {
        $db = $this->db;

        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher, 
                  MAX(maxi_number_trainees) AS Max_trainees, 
                  COUNT(internship.Student_number) AS Current_count
                  FROM Teacher
                  JOIN has_role ON Teacher.Id_teacher = Has_role.user_id
                  LEFT JOIN internship ON Teacher.Id_teacher = internship.Id_teacher
                  WHERE department_name IN ($placeholders)
                  GROUP BY Teacher.Id_teacher";

        $stmt = $db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);

        $teacherData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $listTeacherMax = [];
        $listTeacherIntership = [];
        foreach ($teacherData as $teacher) {
            $listTeacherMax[$teacher['id_teacher']] = $teacher['max_trainees'];
            $listTeacherIntership[$teacher['id_teacher']] = $teacher['current_count'] ?: 0;
        }

        $listFinal = [];
        $listStart = [];
        $listEleveFinal = [];

        foreach ($listTeacherIntership as $teacherId => $currentCount) {
            foreach ($this->calculateRelevanceTeacherStudents($teacherId, $dicoCoef) as $association) {
               $listStart[] = $association;
            }
        }

        if (empty($listStart)) {
            return [[], []];
        }

        $assignedCounts = $listTeacherIntership;

        while (!empty($listStart)) {
            usort($listStart, fn($a, $b) => $b['score'] <=> $a['score']);
            $topCandidate = $listStart[0];

            if ($assignedCounts[$topCandidate['id_teacher']] < $listTeacherMax[$topCandidate['id_teacher']] &&
                !in_array($topCandidate['student_number'], $listEleveFinal)) {
                $listFinal[] = $topCandidate;
                $listEleveFinal[] = $topCandidate['student_number'];
                $assignedCounts[$topCandidate['id_teacher']] += ($topCandidate['type'] === 'Internship') ? 2 : 1;
            }
            array_shift($listStart);
        }

        return [$listFinal, $assignedCounts];
    }


    /**
     * <#> à faire : verif dans la fonction algo2 que la valeur défaut ne casse pas tout
     *  retourne un array composé de la liste des professeurs inscrit dans le departement de l'admin de la BD, à défaut false
     * @return array|false
     */
    public function createListTeacher() {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher FROM Teacher JOIN Has_role ON Teacher.Id_Teacher = Has_role.User_id
                    where Department_name IN ($placeholders)";

        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     *  <#> à faire : verif dans la fonction algo2 que la valeur défaut ne casse pas tout
     * retourne un array composé de la liste des eleves inscrit dans le departement de l'admin de la BD, à défaut false
     * @return array|false
     */

    public function createListInternship() {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Internship.Internship_identifier FROM Internship JOIN Study_at ON Internship.Student_number = Study_at.Student_number
                    where Department_name IN ($placeholders)";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * <#> à faire : verif dans la fonction algo2 que la valeur défaut ne casse pas tout
     * retourne un array composé de la liste des eleves et professeur inscrit etudiant, dans le departement dont l'admin est responsable, dans la relation Is_responsible de la BD, à défaut false
     * @return array|false
     */
    public function createListAssociate() {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT internship.Id_teacher, internship.Internship_identifier FROM internship JOIN Study_at ON internship.Student_number = Study_at.Student_number
                    WHERE Department_name IN ($placeholders) AND internship.Id_teacher IS NOT NULL";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Fonction recupérant les valeurs du post de la viewDispacher sans fonctionnement de l'algorithme de repartition
     * @return string Valeur confirmant l'insertion
     */
    public function insertResponsible() {
        $query = 'UPDATE internship SET Id_teacher = :Id_teacher WHERE Internship_identifier = :Internship_identifier';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Internship_identifier', $_POST['Internship_identifier']);
        $stmt->bindParam(':Id_teacher', $_POST['Id_teacher']);
        $stmt->execute();
        return "Association " . $_POST['Id_teacher'] . " et " . $_POST['Internship_identifier'] . " enregistrée.";
    }

    /**
     * Fonction recupérant les valeurs du post de la viewDispacher servant à la suite de l'algorithme de repartition final
     * @return void
     */
    public function insertIs_responsible() {
        for ($i = 0; $i<count($_POST['id_eleve']); $i++ ) {
            $query = 'SELECT Start_date_internship, End_date_internship FROM Internship
                    where Student_number = :Student_number';
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':Student_number', $_POST['Student_number'][$i]);
            $stmt->execute();
            $DateIntership = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $query = 'INSERT INTO internship (Id_teacher, Student_number, Relevance_Score, responsible_start_date, responsible_end_date) VALUES (:Id_teacher, :Student_number, :Score, :Start_date, :End_date)';
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':Student_number', $_POST['Student_number'][$i]);
            $stmt->bindParam(':Id_teacher', $_POST['Id_teacher'][$i]);
            $stmt->bindParam(':Score', $_POST['Score'][$i]);
            $stmt->bindParam(':Start_date', $DateIntership[0]['start_date_internship']);
            $stmt->bindParam(':End_date', $DateIntership[0]['end_date_internship']);
            $stmt->execute();
        }
    }
}