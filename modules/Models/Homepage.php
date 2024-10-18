<?php
namespace Blog\Models;
use Database;
use PDO;

class Homepage {

    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function correspondTerms(): array
    {
        $searchTerm = $_POST['search'] ?? '';
        $pdo = $this->db;

        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' & ', explode(' ', $searchTerm));

        $query = "
        SELECT num_eleve, nom_eleve, prenom_eleve,
        ts_rank_cd(to_tsvector('french', nom_eleve || ' ' || prenom_eleve), to_tsquery('french', :searchTerm), 32) AS rank
        FROM eleve
        WHERE to_tsquery('french', :searchTerm) @@ to_tsvector('french', nom_eleve || ' ' || prenom_eleve)
        ORDER BY rank DESC
        LIMIT 5
        ";

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $tsQuery);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentAddress(string $studentId): string {
        if ($studentId !== $_POST['student_id']) {
            return false;
        }

        $pdo = $this->db;

        $query = 'SELECT adresse_entreprise FROM stage 
                  WHERE stage.num_eleve = :num_eleve';

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':num_eleve', $studentId);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

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
            $row['relevance'] = $this->scoreDiscipSujet($row['num_eleve']);
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

    public function getStudentsPerDepartment(string $department): bool|array {
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

    public function getDepEnseignant(): bool|array {
        $query = 'SELECT nom_departement
                    FROM enseigne_a
                    WHERE  id_enseignant = :enseignant';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':enseignant', $_SESSION['identifier']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInternships(string $student): bool|array {
        $query = 'SELECT id_enseignant, num_eleve, date_debut_resp, date_fin_resp
                    FROM est_responsable
                    WHERE num_eleve = :student
                    AND date_fin_resp < CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInternshipTeacher(array $internshipStudent): string {
        $internshipTeacher = 0;
        foreach($internshipStudent as $row) {
            if($row['id_enseignant'] == $_SESSION['identifier']) ++$internshipTeacher;
        }
        return $internshipTeacher;
    }

    public function scoreDiscipSujet(string $studentId): string {
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

        if(!$result) return "0";
        return $result["rank"]*5;
    }

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