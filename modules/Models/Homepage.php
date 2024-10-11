<?php

namespace Blog\Models;

use Database;
use PDOException;

class Homepage{
    private Database $db;

    public function __construct(Database $db){
        $this->db = $db;
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * @param string $csvFilePath Chemin vers le fichier CSV
     * @return bool True si l'importation a réussi, sinon false
     */
    public function uploadCsv(string $csvFilePath): bool {
        $db = $this->db;
        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // prepare insertion
                    $query = 'INSERT INTO nom_de_la_table (colonne1,colonne2,colonne3) VALUES (:colonne1, :colonne3,:colonne3)';
                    $stmt = $db->getConn()->prepare($query);
                    $stmt->bindParam(':colonne1', $data[0]);
                    $stmt->bindParam(':colonne2', $data[1]);
                    $stmt->bindParam(':colonne3', $data[2]);

                    // execute insertion
                    $stmt->execute();
                }
                fclose($handle);
                return true;
            } catch (PDOException $e) {
                echo "Erreur lors de l'importation : ", $e->getMessage();
                return false;
            }
        }
        return false;
    }
}