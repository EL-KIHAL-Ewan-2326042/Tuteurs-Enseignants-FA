<?php

namespace Blog\Models;

use Includes\Database;
use PDOException;

class Dashboard{
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
                    print_r($data);
                    $query = 'INSERT INTO student (student_number,student_name,student_surname,formation,class_groupe) VALUES (:column1, :column2,:column3,:column4,:column5)';
                    $stmt = $db->getConn()->prepare($query);
                    $stmt->bindParam(':column1', $data[0]);
                    $stmt->bindParam(':column2', $data[1]);
                    $stmt->bindParam(':column3', $data[2]);
                    $stmt->bindParam(':column4', $data[3]);
                    $stmt->bindParam(':column5', $data[4]);
                    
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