<?php

namespace Blog\Models;

use Includes\Database;
use PDO;
use PDOException;

class Dashboard{
    private Database $db;

    /**
     * Constructeur de la classe Dashboard (modèle)
     * @param Database $db Instance de la base de données
     */
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

    public function exportToCsvByDepartment(string $tableName, array $headers): void {
        $db = $this->db;
        $department = $_SESSION['Role_department'];  //in_array('Admin_dep', $_SESSION['role'])
        $filePath = "/path/to/exports/{$tableName}_export.csv";
        $fileHandle = fopen($filePath, "w");

        fputcsv($fileHandle, $headers);

        $query = "SELECT " . implode(',',$headers) . " FROM $tableName";

        //condition filtrant par département
        switch ($tableName) {
            case 'student':
                $query .= "JOIN study_at ON student.student_number = study_at.student_number WHERE study_at.department_name = :department";
                break;
            case 'teacher':
                $query .= "JOIN teaches ON teacher.id_teacher = teaches.id_teacher WHERE teaches.department_name 
                = :department";
                break;
            case 'internship':
                $query .= "JOIN study_at ON internship.student_number = study_at.student_number WHERE 
                study_at.department_name = :department";
                break;
            case 'teaches':
            case 'department':
            case 'study_at':
                $query .= "WHERE department_name = :department";
                break;
            case 'is_requested':
                $query .= "JOIN study_at ON is_requested.student_number = study_at.student_number WHERE 
                study_at.department_name = :department";
                break;
            case 'is_taught':
                $query .= "JOIN teaches ON is_taught.id_teacher = teaches.id_teacher WHERE teaches.department_name 
                = :department";
                break;
            case 'discipline':
                $query .= "JOIN is_taught ON discipline.discipline_name = is_taught.discipline JOIN teaches 
                ON is_taught.id_teacher = teaches.id_teacher WHERE teaches.department_name = :department";
                break;
            case 'is_responsible':
                $query .= "JOIN study_at ON is_responsible.student_number = study_at.student_number WHERE 
                study_at.department_name = :department";
                break;
            case 'has_address':
                $query .= "JOIN teaches ON has_address.id_teacher = study_at.id_teacher WHERE 
                teaches.department_name = :department";
                break;
            case 'address_type':
                $query .= "JOIN has_address ON address_type.adr_type = has_address.adr_type JOIN teaches 
                ON has_address.id_teacher = study_at.id_teacher WHERE teaches.department_name = :department";
                break;
            case 'addr_name':
                $query .= "JOIN has_address ON addr_name.address = has_address.address JOIN teaches 
                ON has_address.id_teacher = study_at.id_teacher WHERE teaches.department_name = :department";
                break;

                //suite case partie backup

            default:
                echo "Table non reconnue";
                fclose($fileHandle);
                return;
        }

        //préparation et exécution de la requête
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':department', $department);
        $stmt->execute();

        //écriture des données récupérées
        while ($row =$stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($fileHandle, $row);
        }

        fclose($fileHandle);
        echo "Les données ont été exportées vers : {$filePath}";
    }
}