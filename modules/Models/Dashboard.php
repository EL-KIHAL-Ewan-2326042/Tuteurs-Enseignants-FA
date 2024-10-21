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
     * (pour la table Student)
     * @param string $csvFilePath Chemin vers le fichier CSV
     * @return bool True si l'importation a réussi, sinon false
     */
    public function uploadCsvStudent(string $csvFilePath): bool {
        $db = $this->db;
        $expectedHeaders = ['student_number','student_name','student_firstname','formation','class_groupe'];

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle,1000,",");

            if($headers !== $expectedHeaders){
                echo "Les colonnes du fichieres CSV ne correpondent pas aux colonnes attendues par la base de données.";
                fclose($handle);
                return false;
            }

            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    print_r($data);
                    $query = 'INSERT INTO student (student_number,student_name,student_firstname,formation,class_groupe) VALUES (:column1, :column2,:column3,:column4,:column5)';
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

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Teacher)
     * @param string $csvFilePath Chemin vers le fichier CSV
     * @return bool True si l'importation a réussi, sinon false
     */
    public function uploadCsvTeacher(string $csvFilePath): bool {
        $db = $this->db;
        $expectedHeaders = ['id_teacher','teacher_name','teacher_firstname','maxi_number_trainees'];

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle,1000,",");

            if($headers !== $expectedHeaders){
                echo "Les colonnes du fichieres CSV ne correpondent pas aux colonnes attendues par la base de données.";
                fclose($handle);
                return false;
            }

            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    print_r($data);
                    $query = 'INSERT INTO teacher (id_teacher,teacher_name,teacher_firstname,maxi_number_trainees) VALUES (:column1, :column2,:column3,:column4)';
                    $stmt = $db->getConn()->prepare($query);
                    $stmt->bindParam(':column1', $data[0]);
                    $stmt->bindParam(':column2', $data[1]);
                    $stmt->bindParam(':column3', $data[2]);
                    $stmt->bindParam(':column4', $data[3]);

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

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Internship)
     * @param string $csvFilePath Chemin vers le fichier CSV
     * @return bool True si l'importation a réussi, sinon false
     */
    public function uploadCsvInternship(string $csvFilePath): bool {
        $db = $this->db;
        $expectedHeaders = ['internship_identifier','company_name','keywords','start_date_internship','type','end_date_internship','internship_subject','address','student_number'];

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle,1000,",");

            if($headers !== $expectedHeaders){
                echo "Les colonnes du fichieres CSV ne correpondent pas aux colonnes attendues par la base de données.";
                fclose($handle);
                return false;
            }

            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    print_r($data);
                    $query = 'INSERT INTO internship (internship_identifier,company_name,keywords,start_date_internship,type,end_date_internship,internship_subject,address,student_number) VALUES (:column1, :column2,:column3,:column4,:column5,:column6,:column7,:column8,:column9)';
                    $stmt = $db->getConn()->prepare($query);
                    $stmt->bindParam(':column1', $data[0]);
                    $stmt->bindParam(':column2', $data[1]);
                    $stmt->bindParam(':column3', $data[2]);
                    $stmt->bindParam(':column4', $data[3]);
                    $stmt->bindParam(':column5', $data[4]);
                    $stmt->bindParam(':column6', $data[5]);
                    $stmt->bindParam(':column7', $data[6]);
                    $stmt->bindParam(':column8', $data[7]);
                    $stmt->bindParam(':column9', $data[8]);

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