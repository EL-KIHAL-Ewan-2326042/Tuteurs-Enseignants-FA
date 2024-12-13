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
     * @param string $tableName Nom de la table
     * @return array Liste des colonnes
     * @throws Exception
     */
    public function getTableColumn(string $tableName): array {
        try {
            //requête pour obtenir les noms des colonnes d'une table spécifique
            $query = "
                SELECT column_name
                FROM information_schema.columns
                WHERE table_name = :table_name";

            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->execute();

            //récupération des colonnes en tant que tableau
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $columns ?: [];
        } catch (PDOException $e) {
            //gestion des erreurs liées à la base de données
            throw new Exception("Impossible de récupérer les colonnes pour la table $tableName.");
        }
    }

    /**
     * Vérifie que la table existe et qu'elle contient des colonnes
     * @param string $tableName Nom de la table
     * @return bool True si la table est valide, sinon False
     */
    public function isValidTable(string $tableName): bool {
        try {
            //vérification la présence d'au moins une colonne dans la table
            return !empty($this->getTableColumn($tableName));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupère les en-têtes d'un fichier CSV
     * @param string $csvFilePath Chemin du fichier CSV
     * @return array Liste des en-têtes
     * @throws Exception
     */
    public function getCsvHeaders(string $csvFilePath): array {
        //ouverture du fichier CSV et lecture de la première ligne (les en-têtes)
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
     * @param string $csvFilePath Chemin du fichier CSV
     * @return bool True si l'importation réussit, sinon False
     * @throws Exception
     */
    public function uploadCsvStudent(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'student');
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Teacher)
     * @param string $csvFilePath Chemin du fichier CSV
     * @return bool True si l'importation réussit, sinon False
     * @throws Exception
     */
    public function uploadCsvTeacher(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'teacher');
    }

    /**
     * Importation des données depuis un fichier CSV vers la base de données
     * (pour la table Internship)
     * @param string $csvFilePath Chemin du fichier CSV
     * @return bool True si l'importation réussit, sinon False
     * @throws Exception
     */
    public function uploadCsvInternship(string $csvFilePath): bool {
        return $this->uploadCsv($csvFilePath, 'internship');
    }

    /**
     * Importation des données depuis un fichiers CSV vers la base de données
     * pour une table donnée
     * @param string $csvFilePath Chemin du fichier CSV
     * @param string $tableName Nom de la table
     * @return bool True si l'importation réussit, sinon False
     * @throws Exception
     */
    public function uploadCsv(string $csvFilePath, string $tableName): bool {
        $db = $this->db;

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            //lecture de la première ligne du fichier CSV (les en-têtes)
            $headers = fgetcsv($handle, 1000, ",");

            if (!$this->validateHeaders($headers, $tableName)) {
                //retourne false si il y une incohérence entre les colonnes de la table
                fclose($handle);
                return false;
            }

            try {
                //recupération des colonnes de la tables dans la base de données
                $tableColumns = $this->getTableColumn($tableName);

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    //vérification de la correspondance du nombre de données et le nombre de colonne
                    if (count($data) !== count($tableColumns)) {
                        continue;
                    }

                    //prépartation de la requête d'insertion
                    $query = "INSERT INTO $tableName (" . implode(',', $tableColumns) . ") VALUES (" . implode(',', array_map(fn($i) => ":column$i", range(1, count($tableColumns)))) . ")";
                    $stmt = $db->getConn()->prepare($query);

                    //association des données aux paramètres de la requête
                    foreach ($data as $index => $value) {
                        if (empty($value)) {
                            $value = null;
                        } elseif (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $stmt->bindValue(":column" . ($index + 1), $value);
                    }
                    $stmt->execute();
                }
                fclose($handle);
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Vérifie que les colonnes du fichier CSV correspondent aux colonnes attendues
     * dans la base de données
     * @param array $headers Liste des en-têtes
     * @param string $tableName Nom de la table
     * @return bool True si il y a un correspondance entre les colonnes, sinon False
     * @throws Exception
     */
    public function validateHeaders(array $headers, string $tableName): bool {
        //comparaison des en-têtes du CSV avec les colonnes de la table dans la base de données
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
     * @param string $tableName Nom de la table
     * @param array $headers Liste des en-têtes
     * @return bool True si l'exportation réussit, sinon False
     * @throws Exception
     */
    public function exportToCsvByDepartment(string $tableName, array $headers): bool {
        $db = $this->db;
        $department = $_SESSION['role_department'];

        ob_start();

        //envoie des en-têtes HTTP pour le téléchargement du fichier CSV
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

        //construction de la requête SQL filtré par le département de l'administrateur
        $query = "SELECT " . implode(',', array_map(fn($header) => "$tableName." . (string)$header, $headers)) . " FROM $tableName";

        $query .= match ($tableName) {
            'internship' => " JOIN student ON internship.student_number = student.student_number JOIN study_at ON study_at.student_number = student.student_number WHERE study_at.department_name = :department",
            'student' => " JOIN study_at ON student.student_number = study_at.student_number WHERE study_at.department_name = :department",
            'teacher' => " JOIN has_role ON teacher.id_teacher = has_role.user_id JOIN department ON department.department_name = has_role.department_name WHERE department.department_name = :department",
            default => throw new Exception("Table non reconnue : " . $tableName),
        };


        if (is_array($department)) {
            $department = $department[0] ?? '';
        }

        //préparation et exécution de la requête SQL
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':department', $department);
        $stmt->execute();

        //écriture des données récupérées dans le fichier CSV
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }


        fclose($output);
        $csvData = ob_get_clean();
        echo $csvData;

        exit();
    }
}