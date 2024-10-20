<?php
namespace Blog\Models;

use Database;
use PDO;

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
     * Renvoie un tableau trié selon la note, le nom et le prénom de l'élève contenant tous les stages et leurs informations
     * Les stages sélectionnés sont uniquement ceux des élèves faisant partie d'au moins un des départements passés en paramètre
     * Les stages n'ont pas encore débuté et n'ont aucun tuteur attribué
     * @param array $departments liste des départements dont on veut récupérer les stages des élèves
     * @return array tableau contenant les informations relatives à chaque stage, le nombre fois où l'enseignant connecté a été le tuteur de l'élève ainsi qu'une note représentant la pertinence du stage pour l'enseignant
     */
    public function getStudentsList(array $departments): array {
        $studentsList = array();
        foreach($departments as $department) {
            $newList = $this->getStudentsPerDepartment($department);
            if($newList) $studentsList = array_merge($studentsList, $newList);
        }

        $studentsList = array_unique($studentsList, 0);

        foreach($studentsList as & $row) {
            $internshipsResp = $this->getInternships($row['num_eleve']);
            if(!$internshipsResp) {
                $row['internshipTeacher'] = 0;
                $row['countInternship'] = 0;
            } else {
                foreach($internshipsResp as $internshipInfo) {
                    if($row['date_debut'] === $internshipInfo['date_debut_resp'] && $row['date_fin'] === $internshipInfo['date_fin_resp']) {
                        unset($row);
                        break;
                    }
                }
                if(!isset($row)) continue;
                $row['internshipTeacher'] = $this->getInternshipTeacher($internshipsResp);
                $row['countInternship'] = count($internshipsResp);
            }
            $row['relevance'] = $this->scoreDiscipSubject($row['num_eleve']);
        }

        usort($studentsList, function ($a, $b) {
            $rank = $b['relevance'] <=> $a['relevance'];
            if ($rank === 0) {
                $lastName = $a['nom_eleve'] <=> $b['nom_eleve'];
                if ($lastName === 0) {
                    return $a['prenom_eleve'] <=> $b['prenom_eleve'];
                }
                return $lastName;
            }
            return $rank;
        });

        return $studentsList;
    }

    /**
     * Renvoie un tableau contenant les stages des élèves du département passé en paramètre et leurs informations, ou false
     * @param string $department le département duquel les élèves sélectionnés font partie
     * @return false|array tableau contenant le numéro, le nom et le prénom de l'élève, ainsi que le nom de l'entreprise dans lequel il va faire son stage, le sujet et les dates, false sinon
     */
    public function getStudentsPerDepartment(string $department): false|array {
        $query = 'SELECT eleve.num_eleve, nom_eleve, prenom_eleve, nom_entreprise, adresse_entreprise, sujet_stage, date_debut, date_fin
                    FROM eleve
                    JOIN etudie_a
                    ON eleve.num_eleve = etudie_a.num_eleve
                    JOIN stage
                    ON eleve.num_eleve = stage.num_eleve
                    WHERE nom_departement = :dep
                    AND stage.date_debut > CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':dep', $department);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie tous les départements dont l'enseignant connecté fait partie, ou false
     * @return false|array tableau contenant tous les départements dont l'enseignant connecté fait partie, false sinon
     */
    public function getDepTeacher(): false|array {
        $query = 'SELECT nom_departement
                    FROM enseigne_a
                    WHERE  id_enseignant = :enseignant';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':enseignant', $_SESSION['identifier']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un tableau contenant les informations de chaque tutorat de l'élève passé en paramètre, ou false
     * @param string $student le numéro de l'étudiant dont on récupère les informations
     * @return false|array tableau contenant, pour chaque tutorat, le numéro d'enseignant du tuteur, le numéro de l'élève et les dates, false sinon
     */
    public function getInternships(string $student): false|array {
        $query = 'SELECT id_enseignant, num_eleve, date_debut_resp, date_fin_resp
                    FROM est_responsable
                    WHERE num_eleve = :student
                    AND date_fin_resp < CURRENT_DATE';
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
            if($row['id_enseignant'] == $_SESSION['identifier']) ++$internshipTeacher;
        }
        return $internshipTeacher;
    }

    /**
     * Renvoie un score associé à la pertinence entre le sujet de stage de l'élève passé en paramètre et les disciplines enseignées par le professeur connecté
     * @param string $studentId numéro d'élève
     * @return float score associé à la pertinence entre le sujet de stage et les disciplines enseignées par le professeur connecté
     */
    public function scoreDiscipSubject(string $studentId): float {
        $query1 = 'SELECT nom_discipline FROM est_enseigne WHERE id_enseignant = :id';
        $stmt1 = $this->db->getConn()->prepare($query1);
        $stmt1->bindParam(':id', $_SESSION['identifier']);
        $stmt1->execute();
        $result = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $searchTerm = "";

        for($i = 0; $i < count($result); $i++) {
            $searchTerm .= $result[$i]['nom_discipline'];
            if($i < count($result) - 1) $searchTerm .= '_';
        }

        $pdo = $this->db;
        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' | ', explode('_', $searchTerm));

        $query2 = "SELECT num_eleve, mots_cles, ts_rank_cd(to_tsvector('french', mots_cles), to_tsquery('french', :searchTerm), 32) AS rank
                    FROM stage
                    WHERE to_tsquery('french', :searchTerm) @@ to_tsvector('french', mots_cles)
                    AND num_eleve = :studentId
                    AND date_debut > CURRENT_DATE";

        $stmt2 = $pdo->getConn()->prepare($query2);
        $stmt2->bindValue(':searchTerm', $tsQuery);
        $stmt2->bindValue(':studentId', $studentId);
        $stmt2->execute();

        $result = $stmt2->fetch(PDO::FETCH_ASSOC);

        if(!$result) return 0;
        return $result["rank"]*5;
    }

    /**
     * Renvoie le score final normalisé sur 5 représentant l'intérêt d'un enseignant pour un stage en prenant en compte des critères et leurs coefficients associés
     * Représente l'algorithme de calcul sur lequel le score final se base
     * @param int $duration temps de trajet en voiture séparant le professeur et l'adresse du stage
     * @param int $factorDuration coefficient associé au temps de trajet
     * @param int $internshipTeacher nombre de fois où l'enseignant a été le tuteur de l'élève
     * @param int $factorInternshipTeacher coefficient associé au nombre de fois où l'enseignant a été le tuteur de l'élève
     * @param float $scoreRelevance score de pertinence renvoyé par la méthode 'scoreDiscipSujet()'
     * @param int $factorRelevance coefficient associé au score de pertinence
     * @param int $countInternship nombre total de stages et alternances effectués par l'élève
     * @return float score final normalisé sur 5
     */
    public function calculateScore(int $duration, int $factorDuration, int $internshipTeacher, int $factorInternshipTeacher, float $scoreRelevance, int $factorRelevance, int $countInternship): float {
        $scoreDuration = $factorDuration/(1+0.02*$duration);
        $scoreRelevance *= $factorRelevance;
        if ($internshipTeacher === 0) $scoreInternship = 0;
        else $scoreInternship = $countInternship*$factorInternshipTeacher/$countInternship;

        $score = $scoreDuration + $scoreInternship + $scoreRelevance;
        if($score === 0.0) return 0.0;
        return ($score * 5) / ($factorDuration + $factorInternshipTeacher + $factorRelevance);
    }
}