<?php

namespace Blog\Models;

use Exception;
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
     * Récupère les colonnes d'une table donnée dans la base de données
     * @param string $tableName
     * @return array
     * @throws Exception
     */
    public function getTableColumn(string $tableName): array {
        try {
            $query = "
                SELECT column_name
                FROM information_schema.columns
                WHERE table_name = :table_name";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $columns ?: [];
        } catch (PDOException $e) {
            error_log("Erreur lors de l'importation : " . $e->getMessage());
            throw new Exception("Impossible de récupérer les colonnes pour la table $tableName.");
        }
    }

    /**
     * Vérifie que la table existe et qu'elle contient des colonnes
     * @param string $tableName
     * @return bool
     */
    public function isValidTable(string $tableName): bool {
        try {
            return !empty($this->getTableColumn($tableName));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupère les en-têtes d'un fichier CSV
     * @param string $csvFilePath
     * @return array
     * @throws Exception
     */
    public function getCsvHeaders(string $csvFilePath): array {
        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle,1000,",");
            fclose($handle);
            return $headers ?: [];
        }
        throw new Exception("Impossible de lire le fichier CSV");
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Student)
     * @param string $csvFilePath
     * @return bool
     * @throws Exception
     */
    public function uploadCsvStudent(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'student', ['student_number','student_name','student_firstname','formation','class_group']);
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Teacher)
     * @param string $csvFilePath
     * @return bool
     * @throws Exception
     */
    public function uploadCsvTeacher(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'teacher', ['id_teacher','teacher_name','teacher_firstname','maxi_number_trainees']);
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Internship)
     * @param string $csvFilePath
     * @return bool
     * @throws Exception
     */
    public function uploadCsvInternship(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'internship', ['internship_identifier','company_name','keywords','start_date_internship','type','end_date_internship','internship_subject','address','student_number']);
    }

    /**
     * Importation des données depuis un fichiers CSV vers la base de données
     * pour une table donnée
     * @param string $csvFilePath
     * @param string $tableName
     * @return bool
     * @throws Exception
     */
    public function uploadCsv(string $csvFilePath, string $tableName): bool {
        $db = $this->db;

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            error_log("CSV headers: " . implode(",", $headers));

            if (!$this->validateHeaders($headers, $tableName)) {
                error_log("Les colonnes du fichier CSV ne correspondent pas aux colonnes attendues par la $tableName");
                fclose($handle);
                return false;
            }

            try {
                $tableColumns = $this->getTableColumn($tableName);
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) !== count($tableColumns)) {
                        error_log("Données CSV non valides : nombre de colonnes incorrect.");
                        continue;
                    }

                    $query = "INSERT INTO $tableName (" . implode(',', $tableColumns) . ") VALUES (" . implode(',', array_map(fn($i) => ":column$i", range(1, count($tableColumns)))) . ")";
                    $stmt = $db->getConn()->prepare($query);
                    foreach ($data as $index => $value) {
                        error_log("Value type at column $index: " . gettype($value));
                        if (is_array($value)) {
                            error_log("Value type at column $index: " . json_encode($value));
                            $value = implode(',', $value);
                        }
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
     * @param string $tableName
     * @return bool
     * @throws Exception
     */
    public function validateHeaders(array $headers, string $tableName): bool {
        $tableColumns = array_map('strtolower', $this->getTableColumn($tableName));
        $csvHeaders = array_map('strtolower', $headers);
        if (array_diff($csvHeaders, $tableColumns)) {
            throw new Exception("Les colonnes CSV ne correspondent pas à la table $tableName.");
        }

        return empty(array_diff($csvHeaders, $tableColumns));
    }


    /**
     * Exportation des données de la base de données vers un fichier CSV
     * pour une table donnée
     * @param string $tableName
     * @param array $headers
     * @return bool
     * @throws Exception
     */
    public function exportToCsvByDepartment(string $tableName, array $headers): bool {
        $db = $this->db;
        $department = $_SESSION['role_department'];

        ob_start();

        //envoyer les en-têtes pour le téléchargement
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tableName . '_export.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        if ($output === false){
            throw new Exception("Impossible d'ouvrir le fichier CSV");
        }

        fputcsv($output, $headers);

        if (empty($headers)) {
            throw new Exception("Les en-têtes sont manquants ou invalides pour la table $tableName.");
        }

        // Convertir explicitement chaque valeur en chaîne
        $query = "SELECT " . implode(',', array_map(fn($header) => "$tableName." . (string)$header, $headers)) . " FROM $tableName";

        //condition filtrant par département
        $query .= match ($tableName) {
//            'teaches', 'department', 'study_at' => " WHERE department_name = :department",
//            'is_requested' => " JOIN study_at ON is_requested.student_number = study_at.student_number WHERE study_at.department_name = :department",
//            'is_taught' => " JOIN teaches ON is_taught.id_teacher = teaches.id_teacher WHERE teaches.department_name = :department",
//            'is_responsible' => " JOIN study_at ON is_responsible.student_number = study_at.student_number WHERE study_at.department_name = :department",
//            'discipline' => " JOIN is_taught ON discipline.discipline_name = is_taught.discipline_name JOIN teaches ON is_taught.id_teacher = teaches.id_teacher WHERE teaches.department_name = :department",
//            'address_type' => " JOIN has_address ON address_type.type = has_address.type JOIN teaches ON has_address.id_teacher = teaches.id_teacher WHERE teaches.department_name = :department",
//            'addr_name' => " JOIN has_address ON addr_name.address = has_address.address JOIN teaches ON has_address.id_teacher = teaches.id_teacher WHERE teaches.department_name = :department",
//            'has_address' => " JOIN teaches ON has_address.id_teacher = teaches.id_teacher WHERE teaches.department_name = :department",
//            'has_role' => " WHERE role_department = :department",
//            'role' => " JOIN has_role ON role.role_name = has_role.role_name WHERE role_department = :department",
//            'user_connect' => " JOIN has_role ON user_connect.user_id = has_role.user_id WHERE role_department = :department",
//            'backup' => " JOIN has_role ON backup.user_id = has_role.user_id WHERE role_department = :department",
//            'distribution_criteria' => " JOIN backup ON distribution_criteria.name_criteria = backup.name_criteria JOIN has_role ON backup.user_id = has_role.user_id WHERE role_department = :department",

            'internship' => " JOIN student ON internship.student_number = student.student_number JOIN study_at ON study_at.student_number = student.student_number WHERE study_at.department_name = :department",
            'student' => " JOIN study_at ON student.student_number = study_at.student_number WHERE study_at.department_name = :department",
            'teacher' => " JOIN has_role ON teacher.id_teacher = has_role.user_id JOIN department ON department.department_name = has_role.department_name WHERE department.department_name = :department",
//            'is_requested',
//            'discipline',
//            'is_taught',
//            'distance',
//            'type_address',
//            'has_address',
//            'role',
//            'has_role',
//            'department',
//            'study_at',
//            'user_connect',
//            'addr_name',
//            '_backup_',
//            'id_backup',
//            'distribution_criteria',




            default => throw new Exception("Table non reconnue : " . $tableName),
        };

        // Vérifier et logger la valeur de $department
        if (is_array($department)) {
            error_log("Department is an array: " . json_encode($department));
            // Si c'est un tableau, vous pouvez choisir de récupérer un seul élément (par exemple le premier)
            $department = $department[0] ?? '';  // ou une autre logique selon votre cas
        }

        error_log("Department value: " . $department);  // Vérifier la valeur finale


        //préparation et exécution de la requête
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':department', $department);
        $stmt->execute();

        //écriture des données récupérées
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            foreach ($row as $key => $value) {

                if (is_array($value)) {
                    $value = array_map(function ($item){
                        return is_array($item) ? implode(',', $item) : $item;
                    }, $value);
                    $row[$key] = implode(',', $value);
                } elseif (is_object($value)) {
                    $row[$key] = json_encode($value);
                } elseif (is_null($value)) {
                    $row[$key] = '';
                }
            }
            fputcsv($output, $row);
        }


        fclose($output);
        $csvData = ob_get_clean();
        echo $csvData;

        exit();
    }
}