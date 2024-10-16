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

    public function getAdresseEnseignant(string $id): bool|array {
        $query = 'SELECT adresse FROM enseignant WHERE id_enseignant = :id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch($this->db->getConn()::FETCH_ASSOC);
        if (!$result) return false;
        return $result['adresse'];
    }

    public function getStageEleve(string $id): bool|array {
        $query = 'SELECT DISTINCT nom_entreprise, adresse_entreprise, sujet_stage
                    FROM stage
                    WHERE num_eleve = :id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch($this->db->getConn()::FETCH_ASSOC);
    }

    public function getEleves(int $nb, string $enseignant): bool|array {
        $query = 'SELECT num_eleve, nom_eleve, prenom_eleve
                        FROM eleve
                        LIMIT ' . $nb;
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


        /*$tmp = $this->scoreDiscipSujet($nb, $enseignant);
        $result = array();

        foreach($tmp as $ranking) {
            $query = 'SELECT num_eleve, nom_eleve, prenom_eleve
                        FROM eleve
                        WHERE num_eleve = :num
                        LIMIT ' . $nb;
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':num', $ranking['num_eleve']);
            $stmt->execute();
            $tmpResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = array_merge($result, $tmpResult);
        }
        */

        return $result;
    }

    public function scoreDiscipSujet(int $nb, string $id): array {
        $query1 = 'SELECT nom_discipline FROM est_enseigne WHERE id_enseignant = :id';
        $stmt1 = $this->db->getConn()->prepare($query1);
        $stmt1->bindParam(':id', $id);
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
                    ORDER BY rank DESC
                    LIMIT " . $nb;

        $stmt2 = $pdo->getConn()->prepare($query2);
        $stmt2->bindValue(':searchTerm', $tsQuery);
        $stmt2->execute();

        return $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculScore(int $duree, int $coeffDuree, int $asso, int $coeffAsso, float $scorePert, int $coeffPert, int $nbAssoEleve): float {
        $scoreDuree = $coeffDuree/(1+0.02*$duree);
        $scorePert *= $coeffPert;
        if ($asso === 0) $scoreAsso = 0;
        else $scoreAsso = $asso*$coeffAsso/$nbAssoEleve;

        $score = $scoreDuree + $scoreAsso + $scorePert;
        if($score === 0.0) return $score;
        $scoreSur1 = $score / ($coeffDuree + $coeffAsso + $coeffPert);

        return round($scoreSur1 * 5, 2);
    }
}