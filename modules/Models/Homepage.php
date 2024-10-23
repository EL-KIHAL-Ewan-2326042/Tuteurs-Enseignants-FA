<?php
namespace Blog\Models;

use Database;
use PDO;
use PDOException;

class Homepage {

    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
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
     * @return string l'addresse de l'etudiant
     */
    public function getStudentAddress(string $studentId): string {
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
     * @param bool $decreasing true si c'est croissant, false sinon
     * @return array tableau trié
     */
    public function sortRows(array $table, int $mode = 0, bool $decreasing = false): array {
        if($mode === 1) {
            usort($table, function ($a, $b) use ($decreasing) {
                if($decreasing) {
                    $rank = $a['relevance'] <=> $b['relevance'];
                } else {
                    $rank = $b['relevance'] <=> $a['relevance'];
                }
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
                return $rank;
            });
        } elseif($mode === 2) {
            usort($table, function ($a, $b) use ($decreasing) {
                if($decreasing) {
                    $lastName = $b['student_name'] <=> $a['student_name'];
                } else {
                    $lastName = $a['student_name'] <=> $b['student_name'];
                }
                if ($lastName === 0) {
                    $firstName = $a['student_firstname'] <=> $b['student_firstname'];
                    if ($firstName === 0) {
                        $requested = $b['requested'] <=> $a['requested'];
                        if($requested === 0) {
                            return $b['relevance'] <=> $a['relevance'];
                        }
                        return $requested;
                    }
                    return $firstName;
                }
                return $lastName;
            });
        } elseif($mode === 3) {
            usort($table, function ($a, $b) use ($decreasing) {
                if($decreasing) {
                    $subject = $a['internship_subject'] <=> $b['internship_subject'];
                } else {
                    $subject = $b['internship_subject'] <=> $a['internship_subject'];
                }
                if($subject === 0) {
                    $requested = $b['requested'] <=> $a['requested'];
                    if ($requested === 0) {
                        $rank = $b['relevance'] <=> $a['relevance'];
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
                return $subject;
            });
        } else {
            usort($table, function ($a, $b) use ($decreasing) {
                if($decreasing) {
                    $requested = $a['requested'] <=> $b['requested'];
                } else {
                    $requested = $b['requested'] <=> $a['requested'];
                }
                if($requested === 0) {
                    $rank = $b['relevance'] <=> $a['relevance'];
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
    public function getStudentsList(array $departments, int $mode = 0, bool $decreasing = false): array {
        $studentsList = array();
        foreach($departments as $department) {
            $newList = $this->getStudentsPerDepartment($department);
            if($newList) $studentsList = array_merge($studentsList, $newList);
        }

        $studentsList = array_unique($studentsList, 0);

        $requests = $this->getRequests();

        foreach($studentsList as & $row) {
            $internshipsResp = $this->getInternships($row['student_number']);
            if(!$internshipsResp) {
                $row['internshipTeacher'] = 0;
            } else {
                foreach($internshipsResp as $internshipInfo) {
                    if($row['start_date_internship'] === $internshipInfo['responsible_start_date'] && $row['end_date_internship'] === $internshipInfo['responsible_end_date']) {
                        unset($row);
                        break;
                    }
                }
                if(!isset($row)) continue;
                $row['internshipTeacher'] = $this->getInternshipTeacher($internshipsResp);
            }
            $row['relevance'] = $this->scoreDiscipSubject($row['student_number']);
            $row['requested'] = in_array($row['student_number'], $requests);
        }

        return $this->sortRows($studentsList, $mode, $decreasing);
    }

    /**
     * Renvoie un tableau contenant les stages des élèves du département passé en paramètre et leurs informations
     * @param string $department le département duquel les élèves sélectionnés font partie
     * @return false|array tableau contenant le numéro, le nom et le prénom de l'élève, ainsi que le nom de l'entreprise dans lequel il va faire son stage, le sujet et les dates, false sinon
     */
    public function getStudentsPerDepartment(string $department): false|array {
        $query = 'SELECT student.student_number, student_name, student_firstname, company_name, address, internship_subject, start_date_internship, end_date_internship
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

    /**
     * Renvoie les critères et leurs coefficients associés pour l'enseignant connecté
     * @return false|array tableau contenant chaque crtière et son coefficient, false sinon
     */
    public function getFactors(): false|array {
        $query = 'SELECT name_criteria, coef
                    FROM backup
                    WHERE  user_id = :user_id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['identifier']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie tous les départements dont l'enseignant connecté fait partie
     * @return false|array tableau contenant tous les départements dont l'enseignant connecté fait partie, false sinon
     */
    public function getDepTeacher(): false|array {
        $query = 'SELECT department_name
                    FROM teaches
                    WHERE  id_teacher = :teacher';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $_SESSION['identifier']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un tableau contenant les informations de chaque tutorat terminé de l'élève passé en paramètre
     * @param string $student le numéro de l'étudiant dont on récupère les informations
     * @return false|array tableau contenant, pour chaque tutorat, le numéro d'enseignant du tuteur, le numéro de l'élève et les dates, false sinon
     */
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

    /**
     * Renvoie un score associé à la pertinence entre le sujet de stage de l'élève passé en paramètre et les disciplines enseignées par le professeur connecté
     * @param string $studentId numéro d'élève
     * @return float score associé à la pertinence entre le sujet de stage et les disciplines enseignées par le professeur connecté
     */
    public function scoreDiscipSubject(string $studentId): float {
        $query1 = 'SELECT discipline_name FROM is_taught WHERE id_teacher = :id';
        $stmt1 = $this->db->getConn()->prepare($query1);
        $stmt1->bindParam(':id', $_SESSION['identifier']);
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

    public function getCoef($identifier): array {
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

    /**
     * Version PHP de l'algo JavaScript, inutilisé
     * @param $dictValues
     * @return float|int
     */
    public function calculateScore($dictValues) {
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
    public function updateRequests(array $requests): true|string {
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