<?php
namespace Blog\Models;

use Includes\Database;
use PDO;
use PDOException;

class Homepage {
    private Database $db;
    private \Blog\Models\GlobalModel $globalModel;

    public function __construct(Database $db, \Blog\Models\GlobalModel $globalModel){
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
        $searchType = $_POST['searchType'] ?? 'numeroEtudiant';
        $pdo = $this->db;

        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' & ', explode(' ', $searchTerm));
        $query = '';

        if ($searchType === 'studentNumber') {
            $query = "
            SELECT student_number, student_name, student_firstname,
            ts_rank_cd(to_tsvector('french', student_number), to_tsquery('french', :searchTerm), 32) AS rank
            FROM student
            WHERE student_number ILIKE :searchTerm
            ORDER BY student_number
            LIMIT 5
        ";
            $searchTerm = "$searchTerm%";
        } elseif ($searchType === 'name') {
            $query = "
            SELECT student_number, student_name, student_firstname,
            ts_rank_cd(to_tsvector('french', student_name || ' ' || student_firstname), to_tsquery('french', :searchTerm), 32) AS rank
            FROM student
            WHERE student_name ILIKE :searchTerm OR student_firstname ILIKE :searchTerm
            ORDER BY rank DESC
            LIMIT 5
            ";
            $searchTerm = "%$searchTerm%";
        } elseif ($searchType === 'company') {
            $query = "
            SELECT student.student_number, student_name, student_firstname, company_name,
            ts_rank_cd(to_tsvector('french', internship.company_name), to_tsquery('french', :searchTerm), 32) AS rank
            FROM student JOIN internship ON student.student_number = internship.student_number
            WHERE company_name ILIKE :searchTerm
            ORDER BY rank DESC
            LIMIT 5
            ";
            $searchTerm = "$searchTerm%";
        }

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * renvoie l'adresse de l'entreprise de l'etudiant.
     * @param string $studentId le numero de l'etudiant
     * @return string|false l'addresse de l'etudiant, false si ce n'est pas le même étudiant
     */
    public function getStudentAddress(string $studentId): string|false {
        if ($studentId !== $_POST['student_id']) {
            return false;
        }

        $pdo = $this->db;

        $query = 'SELECT address FROM internship 
                  WHERE internship.student_number = :student_number';

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':student_number', $studentId);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Renvoie le tableau passé en paramètre trié
     * @param array $table tableau à trier
     * @param int $mode mode de tri
     * - 0 : choix de l'enseignant, par défaut
     * - 1 : score
     * - 2 : nom et prénom des élèves
     * - 3 : sujet de stage
     * @param bool $decreasing true si c'est décroissant, false sinon
     * @return array tableau trié
     */
    public function sortRows(array $table, int $mode = 0, bool $decreasing = false): array {
        if($mode === 1) {
            usort($table, function ($a, $b) use ($decreasing) {
                $rank = $b['score'] <=> $a['score'];
                if ($rank === 0) {
                    $requested = $b['requested'] <=> $a['requested'];
                    if($requested === 0) {
                        $lastName = $a['student_name'] <=> $b['student_name'];
                        if ($lastName === 0) {
                            return $a['student_firstname'] <=> $b['student_firstname'];
                        }
                        return $lastName;
                    }
                    return $requested;
                }
                return $decreasing ? $rank*-1 : $rank;
            });
        } elseif($mode === 2) {
            usort($table, function ($a, $b) use ($decreasing) {
                $lastName = $a['student_name'] <=> $b['student_name'];
                if ($lastName === 0) {
                    $firstName = $a['student_firstname'] <=> $b['student_firstname'];
                    if ($firstName === 0) {
                        $requested = $b['requested'] <=> $a['requested'];
                        if($requested === 0) {
                            return $b['score'] <=> $a['score'];
                        }
                        return $requested;
                    }
                    return $decreasing ? $firstName*-1 : $firstName;
                }
                return $decreasing ? $lastName*-1 : $lastName;
            });
        } elseif($mode === 3) {
            usort($table, function ($a, $b) use ($decreasing) {
                $subject = $a['internship_subject'] <=> $b['internship_subject'];
                if($subject === 0) {
                    $requested = $b['requested'] <=> $a['requested'];
                    if ($requested === 0) {
                        $rank = $b['score'] <=> $a['score'];
                        if ($rank === 0) {
                            $lastName = $a['student_name'] <=> $b['student_name'];
                            if ($lastName === 0) {
                                return $a['student_firstname'] <=> $b['student_firstname'];
                            }
                            return $lastName;
                        }
                        return $rank;
                    }
                    return $requested;
                }
                return $decreasing ? $subject*-1 : $subject;
            });
        } else {
            usort($table, function ($a, $b) use ($decreasing) {
                $requested = $a['requested'] <=> $b['requested'];
                if($requested === 0) {
                    $rank = $b['score'] <=> $a['score'];
                    if ($rank === 0) {
                        $lastName = $a['student_name'] <=> $b['student_name'];
                        if ($lastName === 0) {
                            return $a['student_firstname'] <=> $b['student_firstname'];
                        }
                        return $lastName;
                    }
                    return $rank;
                }
                return $decreasing ? $requested : $requested*-1;
            });
        }
        return $table;
    }

    /**
     * Renvoie un tableau trié selon la note, le nom et le prénom de l'élève contenant tous les stages et leurs informations
     * Les stages sélectionnés sont uniquement ceux des élèves faisant partie d'au moins un des départements passés en paramètre
     * Les stages n'ont pas encore débuté et n'ont aucun tuteur attribué
     * @param array $departments liste des départements dont on veut récupérer les stages des élèves
     * @return array tableau contenant les informations relatives à chaque stage, le nombre fois où l'enseignant connecté a été le tuteur de l'élève ainsi qu'une note représentant la pertinence du stage pour l'enseignant
     */
    public function getStudentsList(array $departments, string $identifier): array {
        $studentsList = array();
        foreach($departments as $department) {
            $newList = $this->globalModel->getStudentsPerDepartment($department);
            if($newList) $studentsList = array_merge($studentsList, $newList);
        }

        $studentsList = array_unique($studentsList, 0);

        $requests = $this->getRequests();
        if(!$requests) $requests = array();

        $toDelete = array();

        foreach($studentsList as $key => &$row) {
            $internships = $this->globalModel->getInternships($row['student_number']);
            if(!$internships) {
                $row['internshipTeacher'] = 0;
            } else {
                foreach($internships as $internshipInfo) {
                    if($row['start_date_internship'] === $internshipInfo['responsible_start_date'] && $row['end_date_internship'] === $internshipInfo['responsible_end_date']) {
                        array_push($toDelete, $key);
                        break;
                    }
                }
                if(!isset($row)) continue;
                $row['internshipTeacher'] = $this->getInternshipTeacher($internships);
            }
            $row['requested'] = in_array($row['student_number'], $requests);
            $row['duration'] = $this->globalModel->getDistance($row['student_number'], $identifier);
            $row['score'] = $this->calculateScore(array('Distance' => $row['duration'],
                                                        'A été responsable' => $row['internshipTeacher'] > 0 ? $row['internshipTeacher']/count($internships) : 0,
                                                        'Cohérence' => $this->globalModel->scoreDiscipSubject($row['student_number'], $identifier)));
        }

        for($i = count($toDelete)-1; $i >= 0; --$i){
            array_splice($studentsList, $toDelete[$i], 1);
        }

        return $studentsList;
    }

    /**
     * Renvoie le nombre de fois où l'enseignant connecté a été tuteur dans le tableau passé en paramètre
     * @param array $internshipStudent tableau renvoyé par la méthode 'getInternships()'
     * @return int nombre de fois où l'enseignant connecté a été tuteur dans le tablau passé en paramètre
     */
    public function getInternshipTeacher(array $internshipStudent): int {
        $internshipTeacher = 0;
        foreach($internshipStudent as $row) {
            if($row['id_teacher'] == $_SESSION['identifier']) ++$internshipTeacher;
        }
        return $internshipTeacher;
    }

    public function getCoef($identifier): array {
        $dictCoef = [];

        $pdo = $this->db;

        $query = "SELECT Name_criteria, Coef FROM Backup
              WHERE user_id = :user_id";

        $stmt2 = $pdo->getConn()->prepare($query);
        $stmt2->bindValue(':user_id', $identifier);
        $stmt2->execute();

        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            foreach (['Distance', 'A été responsable', 'Cohérence'] as $criteria) {
                $dictCoef[$criteria] = 1;
            }
            return $dictCoef;
        }

        foreach ($rows as $row) {
            $dictCoef[$row['name_criteria']] = $row['coef'];
        }

        return $dictCoef;
    }

    /**
     * Version PHP de l'algo JavaScript, inutilisé
     * @param $dictValues
     * @return float
     */
    public function calculateScore($dictValues): float {
        $dictCoef = $this->getCoef($_SESSION['identifier']);

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

        return ($totalScore * 5) / $totalCoef;
    }



    /**
     * Renvoie tous les stages que l'enseignant connecté a demandé à tutorer
     * @return false|array tableau contenant le numéro d'étudiant de l'élève du stage dont l'enseignant connecté a fait la demande, false sinon
     */
    public function getRequests(): false|array {
        $query = 'SELECT student_number
                    FROM is_requested
                    WHERE  id_teacher = :teacher';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $_SESSION['identifier']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Met à jour la table is_requested en fonction des stages demandés par l'enseignant connecté
     * @param array $requests tableau contenant les numéro d'étudiant que l'enseignant souhaite tutorer
     * @return true|string renvoie true si les insert et delete ont fonctionné, sinon l'erreur dans un string
     */
    public function updateRequests(array $requests): bool|string {
        $current_requests = $this->getRequests();
        if(!$current_requests) $current_requests = array();

        $to_add = array_diff($requests, $current_requests);
        $to_delete = array_diff($current_requests, $requests);

        foreach($to_add as $request) {
            $query = 'INSERT INTO is_requested(id_teacher, student_number)
                        VALUES(:teacher, :student)';
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':teacher', $_SESSION['identifier']);
            $stmt->bindParam(':student', $request);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                return $e->getMessage();
            }
        }

        foreach($to_delete as $request) {
            $query = 'DELETE FROM is_requested
                        WHERE  id_teacher = :teacher
                        AND student_number = :student';
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':teacher', $_SESSION['identifier']);
            $stmt->bindParam(':student', $request);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                return $e->getMessage();
            }
        }
        return true;
    }
}