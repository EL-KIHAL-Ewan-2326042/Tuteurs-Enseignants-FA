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
     * @param string $csvFilePath
     * @return bool
     */
    public function uploadCsvStudent(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'student', ['student_number','student_name','student_firstname','formation','class_group']);
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Teacher)
     * @param string $csvFilePath
     * @return bool
     */
    public function uploadCsvTeacher(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'teacher', ['id_teacher','teacher_name','teacher_firstname','maxi_number_trainees']);
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Internship)
     * @param string $csvFilePath
     * @return bool
     */
    public function uploadCsvInternship(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'internship', ['internship_identifier','company_name','keywords','start_date_internship','type','end_date_internship','internship_subject','address','student_number']);
    }

    /**
     * Importation des données depuis un fichiers CSV vers la base de données
     * pour une table donnée
     * @param string $csvFilePath
     * @param string $tableName
     * @param array $expectedHeaders
     * @return bool
     */
    private function uploadCsv(string $csvFilePath, string $tableName, array $expectedHeaders): bool {
        $db = $this->db;

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");

            if (!$this->validateHeaders($headers, $expectedHeaders)) {
                echo "Les colonnes du fichier CSV ne correspondent pas aux colonnes attendues par la base de données.";
                fclose($handle);
                return false;
            }

            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) !== count($expectedHeaders)) {
                        echo "Le nombre de colonnes dans le fichier CSV ne correspond pas aux attentes.";
                        continue; // Passer à la ligne suivante
                    }

                    $query = "INSERT INTO $tableName (" . implode(',', $expectedHeaders) . ") VALUES (" . implode(',', array_map(fn($i) => ":column$i", range(1, count($expectedHeaders)))) . ")";
                    $stmt = $db->getConn()->prepare($query);
                    foreach ($data as $index => $value) {
                        $stmt->bindValue(":column" . ($index + 1), $value);
                    }
                    $stmt->execute();
                }
                fclose($handle);
                return true;
            } catch (PDOException $e) {
                error_log("Erreur lors de l'importation : " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Vérifie que les colonnes du fichier CSV correspondent aux colonnes attendues
     * dans la base de données
     * @param array $headers
     * @param array $expectedHeaders
     * @return bool
     */
    private function validateHeaders(array $headers, array $expectedHeaders): bool {
        return $headers === $expectedHeaders;
    }
}