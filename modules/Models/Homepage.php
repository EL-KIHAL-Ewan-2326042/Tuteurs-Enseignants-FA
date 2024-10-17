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
        $searchType = $_POST['searchType'] ?? 'numeroEtudiant';
        $pdo = $this->db;

        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' & ', explode(' ', $searchTerm));
        $query = '';

        if ($searchType === 'numeroEtudiant') {
            $query = "
            SELECT num_eleve, nom_eleve, prenom_eleve,
            ts_rank_cd(to_tsvector('french', num_eleve), to_tsquery('french', :searchTerm), 32) AS rank
            FROM eleve
            WHERE num_eleve ILIKE :searchTerm
            ORDER BY num_eleve
            LIMIT 5
        ";
            $searchTerm = "$searchTerm%";
        } elseif ($searchType === 'nomEtPrenom') {
            $query = "
            SELECT num_eleve, nom_eleve, prenom_eleve,
            ts_rank_cd(to_tsvector('french', nom_eleve || ' ' || prenom_eleve), to_tsquery('french', :searchTerm), 32) AS rank
            FROM eleve
            WHERE nom_eleve ILIKE :searchTerm OR prenom_eleve ILIKE :searchTerm
            ORDER BY rank DESC
            LIMIT 5
            ";
            $searchTerm = "%$searchTerm%";
        } elseif ($searchType === 'company') {
            $query = "
            SELECT eleve.num_eleve, nom_eleve, prenom_eleve, nom_entreprise,
            ts_rank_cd(to_tsvector('french', stage.nom_entreprise), to_tsquery('french', :searchTerm), 32) AS rank
            FROM eleve JOIN stage ON eleve.num_eleve = stage.num_eleve
            WHERE nom_entreprise ILIKE :searchTerm
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