<?php

namespace Blog\Models;

use Includes\Database;
use PDO;
use PDOException;

class Dispatcher{
    private Database $db;
    private \Blog\Models\GlobalModel $globalModel;

    public function __construct(Database $db, \Blog\Models\GlobalModel $globalModel){
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
     * @param $dictCoef dictionnaire cle->nom_critere et valeur->coef
     * @return array|array[] array contenant id_prof, id_eleve et le score associe
     */
    public function calculateRelevanceTeacherStudents($identifier, $dictCoef): array
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

        // Pour chaque relation tuteur-etudiant, on calcul leur score qu'on met dans un array final
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
            // Score normalise sur 5
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

    /**
     * S'occupe de trouver la meilleure combinaison possible tuteur-stage et le renvoie sous forme de tableau
     * @param array $dicoCoef dictionnaire cle->nom_critere et valeur->coef
     * @return array|array[] resultat final sous forme de matrixe
     */
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

    /**
     * <#> à faire : verif dans la fonction algo2 que la valeur défaut ne casse pas tout
     *  retourne un array composé de la liste des professeurs inscrit dans le departement de l'admin de la BD, à défaut false
     * @return array|false
     */
    public function createListTeacher() {
        $query = 'SELECT Teacher.Id_teacher FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                    where Department_name = :Role_department';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Role_department', $_SESSION['role_department']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     *  <#> à faire : verif dans la fonction algo2 que la valeur défaut ne casse pas tout
     * retourne un array composé de la liste des eleves inscrit dans le departement de l'admin de la BD, à défaut false
     * @return array|false
     */
    public function createListStudent() {
        $query = 'SELECT Student.Student_number FROM Student JOIN Study_at ON Student.Student_number = Study_at.Student_number
                    where Department_name = :Role_department';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Role_department', $_SESSION['role_department']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * <#> à faire : verif dans la fonction algo2 que la valeur défaut ne casse pas tout
     * retourne un array composé de la liste des eleves et professeur inscrit etudiant, dans le departement dont l'admin est responsable, dans la relation Is_responsible de la BD, à défaut false
     * @return array|false
     */
    public function createListAssociate() {
        $query = 'SELECT Is_responsible.Student_number, Is_responsible.Id_teacher FROM Is_responsible JOIN Study_at ON Is_responsible.Student_number = Study_at.Student_number
                    where Department_name = :Role_department';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':Role_department', $_SESSION['role_department']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Fonction recupérant les valeurs du post de la viewDispacher sans fonctionnement de l'algorithme de repartition
     * @return string Valeur confirmant l'insertion
     */
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

    /**
     * Fonction recupérant les valeurs du post de la viewDispacher servant à la suite de l'algorithme de repartition final
     * @return void
     */
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