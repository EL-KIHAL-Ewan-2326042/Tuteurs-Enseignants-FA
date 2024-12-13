<?php

namespace Blog\Models;

use Includes;
use PDO;

class Dispatcher{
    private Includes\Database $db;
    private \Blog\Models\GlobalModel $globalModel;

    public function __construct(Includes\Database $db, \Blog\Models\GlobalModel $globalModel){
        $this->db = $db;
        $this->globalModel = $globalModel;
    }

    /**
     * Renvoie pour un professeur un couple avec tous les stages de son departement
     * @param $identifier l'identifiant du professeur
     * @param $dictCoef array cle->nom_critere et valeur->coef
     * @return array|array[] array contenant id_prof, id_eleve et le Score associe
     */
    public function calculateRelevanceTeacherStudents($identifier, array $dictCoef): array
    {
        $internshipList = array();
        // On recupere la liste des departement de l'eleve
        $departments = $this->globalModel->getDepTeacher($identifier);
        foreach($departments as $listDepTeacher) {
            foreach($listDepTeacher as $department) {
                // Pour chaque departement, on recupere les eleve
                $newList = $this->globalModel->getInternshipsPerDepartment($department);
                if ($newList)  {
                    // Les eleves sont rajoutes dans la liste finale
                    $internshipList = array_merge($internshipList, $newList);
                }
            }
        }

        $result = array();

        // Pour chaque relation tuteur-etudiant, on calcul leur Score qu'on met dans un array final
        foreach($internshipList as $internship) {
            $distanceMin = $this->globalModel->getDistance($internship['internship_identifier'], $identifier);
            $relevance= $this->globalModel->scoreDiscipSubject($internship['internship_identifier'], $identifier);

            $dictValues = array(
                "A été responsable" => $this->globalModel->getInternships($internship['internship_identifier']),
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

            $newList = ["id_teacher" => $identifier, "internship_identifier" => $internship['internship_identifier'], "score" => round($ScoreFinal, 2), "type" => $internship['type']];

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
                !in_array($topCandidate['internship_identifier'], $listEleveFinal)) {
                $listFinal[] = $topCandidate;
                $listEleveFinal[] = $topCandidate['internship_identifier'];
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
     * @return string Valeur confirmant l'insertion
     */
    public function insertIs_responsible(String $id_prof, String $Internship_id, float $Score) {
        $query = 'UPDATE internship SET Id_teacher = :id_prof, Relevance_score = :Score WHERE Internship_identifier = :Internship_id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':id_prof', $id_prof);
        $stmt->bindParam(':Score', $Score);
        $stmt->bindParam(':Internship_id', $Internship_id);
        $stmt->execute();
        return "Association " . $id_prof . " et " . $Internship_id . " enregistrée. <br>";
    }

    /**
     * Montrer la liste des sauvegardes
     * @param string $user_id
     * @return array|null Renvoie les coefficients ou les sauvegardes disponibles.
     */
    public function showCoefficients(string $user_id): ?array {
        try {
            $query = "SELECT DISTINCT id_backup FROM backup WHERE user_id = :user_id ORDER BY id_backup DESC";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function loadCoefficients(string $user_id, int $id_backup): array|false {
        try {
            $query = "SELECT name_criteria, coef FROM backup WHERE user_id = :user_id AND id_backup = :id_backup";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':id_backup', $id_backup);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sauvegarder les coefficients dans la base de donnée
     * @param array $coefficients
     * @param string $user_id
     * @return bool
     */
    public function saveCoefficients(array $coefficients, string $user_id): bool {
        try {
            $query = "SELECT MAX(id_backup) FROM backup WHERE user_id = :user_id";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $id_backup = $stmt->fetch(PDO::FETCH_COLUMN);

            $id_backup += 1;
            foreach ($coefficients as $nameCriteria => $coef) {
                $query = "INSERT INTO backup (user_id, name_criteria, id_backup, coef) VALUES (:user_id, :name_criteria, :id_backup, :coef)";
                $stmt = $this->db->getConn()->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':name_criteria', $nameCriteria);
                $stmt->bindParam(':id_backup', $id_backup);
                $stmt->bindParam(':coef', $coef);
                $stmt->execute();
            }

            return true;
            // TODO Si l'exception correspond au max d'id_backup
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getDefaultCoef() {
        $query = "SELECT name_criteria FROM distribution_criteria";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute();
        $defaultCriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($defaultCriteria as &$criteria) {
            $criteria['coef'] = 1;
        }

        return $defaultCriteria;
    }
}