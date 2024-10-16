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

}