<?php
namespace Blog\Models;
use Database;
use PDO;

class Homepage {

    private Database $db;

    public function __construct(Database $db) {
        $this->db = new Database();
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
                    WHERE est_stagiaire.num_eleve = :id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch($this->db->getConn()::FETCH_ASSOC);
    }

    public function getEleves(int $nb, string $enseignant): bool|array {
        $tmp = $this->scoreDiscipSujet($enseignant);
        foreach($tmp as $test) echo $test['num_eleve'];
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

        return $result;
    }

    public function scoreDiscipSujet(string $id): array {
        $query1 = 'SELECT nom_discipline FROM est_enseigne WHERE id_enseignant = :id';
        $stmt1 = $this->db->getConn()->prepare($query1);
        $stmt1->bindParam(':id', $id);
        $stmt1->execute();
        $searchTerm = $stmt1->fetchAll($this->db->getConn()::FETCH_ASSOC);

        $pdo = $this->db;

        $searchTerm = trim(implode('', $searchTerm));
        $tsQuery = implode(' | ', explode('_', $searchTerm));

        $query2 = "SELECT num_eleve, mots_cles, ts_rank_cd(to_tsvector('french', mots_cles), to_tsquery('french', :searchTerm), 32) AS rank
                    FROM stage
                    WHERE to_tsquery('french', :searchTerm) @@ to_tsvector('french', mots_cles)
                    ORDER BY rank DESC
                    LIMIT 5";

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
        $scoreSur1 = $score / ($coeffDuree + $coeffAsso + $coeffPert);

        return round($scoreSur1 * 5, 2);
    }
}