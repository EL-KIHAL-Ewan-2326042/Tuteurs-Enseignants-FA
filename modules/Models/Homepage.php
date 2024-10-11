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
        $tsQuery = implode(' & ', explode(' ', $searchTerm)); // Prepare the tsquery

        $query = "
        SELECT num_eleve, nom_eleve, ts_rank_cd(to_tsvector('french', nom_eleve), to_tsquery('french', :searchTerm), 32) AS rank
        FROM eleve
        WHERE to_tsquery('french', :searchTerm) @@ to_tsvector('french', nom_eleve)
        ORDER BY rank DESC
        LIMIT 5
        ";

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $tsQuery);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$row) {
            $row['rank'] = min(max($row['rank'] / 32, 0), 1);
        }

        return $results;
    }

}
