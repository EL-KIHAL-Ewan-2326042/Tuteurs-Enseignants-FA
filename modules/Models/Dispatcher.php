<?php

namespace Blog\Models;

use Database;
use PDOException;

class Dispatcher{
    private Database $db;

    public function __construct(Database $db){
        $this->db = $db;
    }

    /**
     * @return array|false
     */
    public function getCriteria()
    {
        $db = $this->db;

        $query = 'SELECT * FROM Backup where user_id = :user_id';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetchAll();
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
                    print_r($data);
                    $query = 'INSERT INTO eleve (num_eleve,nom_eleve,prenom_eleve,formation,groupe) VALUES (:colonne1, :colonne2,:colonne3,:colonne4,:colonne5)';
                    $stmt = $db->getConn()->prepare($query);
                    $stmt->bindParam(':colonne1', $data[0]);
                    $stmt->bindParam(':colonne2', $data[1]);
                    $stmt->bindParam(':colonne3', $data[2]);
                    $stmt->bindParam(':colonne4', $data[3]);
                    $stmt->bindParam(':colonne5', $data[4]);

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