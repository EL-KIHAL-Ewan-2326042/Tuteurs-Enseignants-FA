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
     * Recherche des termes correspondants dans la base de données en fonction des paramètres fournis dans le POST.
     *
     * @return array -Tableau associatif contenant les résultats de la recherche.
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


    /**
     * Calcule la pertinence des stages pour un professeur et des stages en fonction de plusieurs critères de pondération.
     *
     * @param String $identifier Identifiant du professeur
     * @param array $dictCoef Tableau associatif des critères de calcul et leurs coefficients
     * @return array|array[] Tableau d'associations ('id_teacher', 'internship_identifier', 'score' et type')
     */
    /**
     * @param array $teacher
     * @param array $dictCoef
     * @return array|array[]
     */
    public function RelevanceTeacher(array $teacher, array $dictCoef): array
    {
        $identifier = $teacher['id_teacher'];

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
            $result[] = $this->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship);
        }

        if (!empty($result)) {
            return $result;
        }
        return [[]];
    }

    public function RelevanceInternship(string $internship, array $dictCoef): array
    {
        $db = $this->db;

        $query = "SELECT Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname, Teacher.maxi_number_trainees, 
                    SUM(CASE 
                    WHEN internship.type = 'alternance' THEN 2 
                    WHEN internship.type = 'Internship' THEN 1 
                    ELSE 0
                    END) AS Current_count FROM Teacher 
                  JOIN has_role ON Teacher.Id_teacher = has_role.user_id
                  JOIN Study_at ON Study_at.department_name = has_role.department_name
                  JOIN Student ON Student.student_number = Study_at.student_number
                  JOIN INTERNSHIP ON Internship.student_number = Student.student_number
                  WHERE has_role.department_name IN (SELECT department_name 
                                            FROM Study_at 
                                            JOIN Student ON Study_at.student_number = Student.student_number
                                            JOIN Internship ON Internship.student_number = Internship.student_number
                                            WHERE Internship.internship_identifier = :internship
                                            GROUP BY department_name) AND Internship.internship_identifier = :internship
                  GROUP BY Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname, Teacher.maxi_number_trainees
                    HAVING Teacher.maxi_number_trainees > SUM(
                    CASE 
                        WHEN internship.type = 'alternance' THEN 2
                        WHEN internship.type = 'Internship' THEN 1
                        ELSE 0
                    END) 
                  ";

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $teacherList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT Internship.Internship_identifier, Internship.Company_name, Internship.Internship_subject, Internship.Address, Internship.Student_number, Internship.Type, Student.Student_name, Student.Student_firstname, Student.Formation, Student.Class_group
                    FROM Internship JOIN Student ON Internship.Student_number = Student.Student_number WHERE Internship.internship_identifier = :internship";

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $internship = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $result = array();

        foreach($teacherList as $teacher) {
            $result[] = $this->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship[0]);
        }

        if (!empty($result)) {
            return $result;
        }
        return [[]];
    }

    public function calculateRelevanceTeacherStudentsAssociate(array $teacher, array $dictCoef, array $internship): array{
        $identifier = $teacher['id_teacher'];
        $dictValues = array();

            // Calculer les valeurs uniquement si elles sont nécessaires
            if (isset($dictCoef['Distance'])) {
                $dictValues["Distance"] = $this->globalModel->getDistance($internship['internship_identifier'], $identifier, isset($internship['id_teacher']));
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

        $newList = ["id_teacher" => $identifier, "teacher_name" => $teacher["teacher_name"], "teacher_firstname" => $teacher["teacher_firstname"], "student_number" => $internship["student_number"], "student_name" => $internship["student_name"], "student_firstname" => $internship["student_firstname"], "internship_identifier" => $internship['internship_identifier'], "internship_subject" => $internship["internship_subject"], "address" => $internship["address"], "company_name" => $internship["company_name"], "formation" => $internship["formation"], "class_group" => $internship["class_group"], "score" => round($ScoreFinal, 2), "type" => $internship['type']];

        if (!empty($newList)) {
            return $newList;
        }

        return [[]];
    }

    /**
     * Permet de trouver la meilleure combinaison possible tuteur-stage et le renvoie sous forme de tableau
     * @param array $dicoCoef dictionnaire cle->nom_critere et valeur->coef
     * @return array|array[] resultat fina  l sous forme de matrice
     */
    public function dispatcher(array $dicoCoef): array
    {
        $db = $this->db;

        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher, Teacher.teacher_name, Teacher.teacher_firstname,
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

        foreach ($teacherData as $teacher) {
            foreach ($this->RelevanceTeacher($teacher, $dicoCoef) as $association) {
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
                if ($topCandidate['type'] === 'Internship' && $listTeacherMax[$topCandidate['id_teacher']] - $assignedCounts[$topCandidate['id_teacher']] > 1)  {
                    $listFinal[] = $topCandidate;
                    $listEleveFinal[] = $topCandidate['internship_identifier'];
                    $assignedCounts[$topCandidate['id_teacher']] += 1;
                }
                elseif ($topCandidate['type'] === 'alternance' && $listTeacherMax[$topCandidate['id_teacher']] - $assignedCounts[$topCandidate['id_teacher']] > 2){
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
     * Récupère une liste des identifiants des enseignants associés aux départements du rôle de l'admin.
     *
     * @return array|false Tableau contenant les identifiants des enseignants ou `false` en cas d'erreur si aucun enseignant n'est trouvé pour les départements spécifiés.
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
     * Récupère une liste des identifiants de stages des étudiants inscrits dans les départements associés au rôle de l'admin.
     *
     * @return array|false Un tableau contenant les identifiants des stages ou `false` en cas d'erreur ou si aucun stage n'est trouvé pour les départements spécifiés.
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
     * Récupère une liste des élèves et professeurs associés inscrits dans les départements dont l'admin est responsable.
     *
     * @return array|false Un tableau contenant les paires (id_teacher, internship_identifier) pour chaque étudiant dans les départements concernés, ou `false` en cas d'erreur ou si aucun résultat n'est trouvé.
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
     * Fonction permettant d'associer un enseignant à un stage.
     *
     * @return string Un message confirmant l'enregistrement de l'association entre l'enseignant et le stage.
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
     * Cette fonction effectue la mise à jour des informations dans la base de données en associant un enseignant et un score de pertinence à un stage.
     *
     * **Paramètres :**
     * - `String $id_prof` : L'identifiant de l'enseignant à associer au stage.
     * - `String $Internship_id` : L'identifiant du stage auquel l'enseignant est affecté.
     * - `float $Score` : Le score de pertinence attribué à cette association (représente la qualité de la répartition).
     *
     * @return string Message confirmant l'enregistrement de l'association entre l'enseignant et le stage.."
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
     * Cette fonction permet de récupérer la liste des sauvegardes disponibles dans la base de données.
     *
     * @return array|null Renvoie un tableau associatif contenant les identifiants des sauvegardes disponibles, ou `null` en cas d'échec.
     */
    public function showCoefficients(): ?array {
        try {
            $query = "SELECT DISTINCT id_backup FROM id_backup ORDER BY id_backup ASC";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cette fonction permet de charger les coefficients d'un utilisateur pour une sauvegarde donnée.
     *
     * @param string $user_id L'identifiant de l'utilisateur pour lequel les coefficients sont chargés.
     * @param int $id_backup L'identifiant de la sauvegarde pour laquelle les coefficients sont récupérés.
     * @return array|false Retourne un tableau associatif des coefficients si la requête réussit, ou `false` en cas d'erreur ou de données non trouvées.
     */
    public function loadCoefficients(string $user_id, int $id_backup): array|false {
        try {
            $query = "SELECT name_criteria, coef, is_checked FROM backup WHERE user_id = :user_id AND id_backup = :id_backup ORDER BY name_criteria ASC";
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
     * Permet de sauvegarder les coefficients dans la base de données.
     *
     * @param array $data Tableau associatif contenant les informations sur les critères à mettre à jour ('name_criteria' (string), 'coef' et 'is_checked'` (int))
     * @param string $user_id Identifiant de l'utilisateur pour lequel les coefficients doivent être mis à jour
     * @param int $id_backup Identifiant de la sauvegarde pour laquelle les coefficients doivent être mis à jour
     * @return bool Retourne True si la mise à jour a réussi, False sinon.
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

    /**
     * Récupère les critères de distribution et leur associe des valeurs par défaut 'coef' = 1 et 'is_checked' = true.
     *
     * @return array Tableau associatif contenant la liste des critères, associé au valeur par défaut
     **/

    public function getDefaultCoef(): array
    {
        $query = "SELECT name_criteria FROM distribution_criteria ORDER BY name_criteria ASC";
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