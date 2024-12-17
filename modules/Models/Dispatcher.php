<?php

namespace Blog\Models;

use Includes;
use mysql_xdevapi\Exception;
use PDO;

class Dispatcher{
    private Includes\Database $db;
    private \Blog\Models\GlobalModel $globalModel;

    public function __construct(Includes\Database $db, \Blog\Models\GlobalModel $globalModel){
        $this->db = $db;
        $this->globalModel = $globalModel;
    }

    /**
     * Trouve dans le DB les termes correspondant(LIKE)
     * On utilise le POST, avec search qui correspond à la recherche
     * et searchType au type de recherche (studentId, name, ...)
     * @return array tout les termes correspendants
     */
    public function correspondTerms(): array
    {
        $searchTerm = $_POST['search'] ?? '';
        $searchType = $_POST['searchType'] ?? '';
        $teacher_id = $_POST['identifier'] ?? '';
        $pdo = $this->db;

        $searchTerm = trim($searchTerm);

        if ($searchType === 'searchTeacher') {
            $query = "
            SELECT id_teacher, teacher_name, teacher_firstname
            FROM teacher
            WHERE id_teacher ILIKE :searchTerm
            ORDER BY id_teacher ASC
            LIMIT 5
        ";
            $searchTerm = "$searchTerm%";
        } elseif ($searchType === 'searchInternship') {
            $query = "
            SELECT student.student_number, student_name, student_firstname, company_name, internship_identifier
            FROM student
            JOIN internship ON student.student_number = internship.student_number
            WHERE internship_identifier ILIKE :searchTerm
            ORDER BY company_name ASC
            LIMIT 5
        ";
            $searchTerm = "$searchTerm%";
        } else {
            return [];
        }

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateRelevanceTeacherStudents($identifier, array $dictCoef): array
    {
        $internshipList = array();
        $departments = $this->globalModel->getDepTeacher($identifier);
        foreach($departments as $listDepTeacher) {
            foreach($listDepTeacher as $department) {
                $newList = $this->globalModel->getInternshipsPerDepartment($department);
                if ($newList)  {
                    $internshipList = array_merge($internshipList, $newList);
                }
            }
        }

        $result = array();

        foreach($internshipList as $internship) {
            $dictValues = array();

            // Calculer les valeurs uniquement si elles sont nécessaires
            if (isset($dictCoef['Distance'])) {
                $dictValues["Distance"] = $this->globalModel->getDistance($internship['internship_identifier'], $identifier);
            }

            if (isset($dictCoef['Cohérence'])) {
                $dictValues["Cohérence"] = round($this->globalModel->scoreDiscipSubject($internship['internship_identifier'], $identifier), 2);
            }

            if (isset($dictCoef['A été responsable'])) {
                $internshipListData = $this->globalModel->getInternships($internship['internship_identifier']);
                $dictValues["A été responsable"] = $internshipListData;
            }

            if (isset($dictCoef['Est demandé'])) {
                $dictValues["Est demandé"] = $this->globalModel->isRequested($internship['internship_identifier'], $identifier);
            }

            $totalScore = 0;
            $totalCoef = 0;

            // Pour chaque critère dans le dictionnaire de coefficients, calculer le score associé
            foreach ($dictCoef as $criteria => $coef) {
                if (isset($dictValues[$criteria])) {
                    $value = $dictValues[$criteria];

                    switch ($criteria) {
                        case 'Distance':
                            $ScoreDuration = $coef / (1 + 0.02 * $value);
                            $totalScore += $ScoreDuration;
                            break;

                        case 'A été responsable':
                            $numberOfInternships = count($value);
                            $baselineScore = 0.7 * $coef;

                            if ($numberOfInternships > 0) {
                                $ScoreInternship = $coef * min(1, log(1 + $numberOfInternships, 2));
                            } else {
                                $ScoreInternship = $baselineScore;
                            }

                            $totalScore += $ScoreInternship;
                            break;

                        case 'Est demandé':
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
                  SUM(CASE 
                  WHEN internship.type = 'alternance' THEN 2 
                  WHEN internship.type = 'Internship' THEN 1 
                  ELSE 0
                  END) AS Current_count
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
            $listTeacherIntership[$teacher['id_teacher']] = $teacher['current_count'];
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
                !in_array($topCandidate['internship_identifier'], $listEleveFinal) && $topCandidate['type'] === 'Internship') {
                if ($topCandidate['type'] = 'Internship' && $listTeacherMax[$topCandidate['id_teacher']] - $assignedCounts[$topCandidate['id_teacher']] > 1)  {
                    $listFinal[] = $topCandidate;
                    $listEleveFinal[] = $topCandidate['internship_identifier'];
                    $assignedCounts[$topCandidate['id_teacher']] += 1;
                }
                elseif ($topCandidate['type'] = 'alternance' && $listTeacherMax[$topCandidate['id_teacher']] - $assignedCounts[$topCandidate['id_teacher']] > 2){
                    $listFinal[] = $topCandidate;
                    $listEleveFinal[] = $topCandidate['internship_identifier'];
                    $assignedCounts[$topCandidate['id_teacher']] += 2;
                }
                else array_shift($listStart);
            }
            else
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
        $stmt->bindParam(':Internship_identifier', $_POST['searchInternship']);
        $stmt->bindParam(':Id_teacher', $_POST['searchTeacher']);
        $stmt->execute();
        return "Association " . $_POST['searchTeacher'] . " et " . $_POST['searchInternship'] . " enregistrée.";
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
     * @return array|null Renvoie les coefficients ou les sauvegardes disponibles.
     */
    public function showCoefficients(): ?array {
        try {
            $query = "SELECT DISTINCT id_backup FROM id_backup";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function loadCoefficients(string $user_id, int $id_backup): array|false {
        try {
            $query = "SELECT name_criteria, coef, is_checked FROM backup WHERE user_id = :user_id AND id_backup = :id_backup";
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
     * @param array $data
     * @param string $user_id
     * @param int $id_backup
     * @return bool
     */
    public function saveCoefficients(array $data, string $user_id, int $id_backup = 0): bool {
        try {
            $query = "UPDATE backup 
                  SET coef = :coef, is_checked = :is_checked 
                  WHERE user_id = :user_id AND id_backup = :id_backup AND name_criteria = :name_criteria";

            foreach ($data as $singleData) {
                $stmt = $this->db->getConn()->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':id_backup', $id_backup);
                $stmt->bindParam(':name_criteria', $singleData['name_criteria']);
                $stmt->bindParam(':coef', $singleData['coef']);
                $stmt->bindParam(':is_checked', $singleData['is_checked']);
                $stmt->execute();
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getDefaultCoef(): array
    {
        $query = "SELECT name_criteria FROM distribution_criteria";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute();
        $defaultCriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($defaultCriteria as &$criteria) {
            $criteria['coef'] = 1;
            $criteria['is_checked'] = true;
        }

        return $defaultCriteria;
    }
}