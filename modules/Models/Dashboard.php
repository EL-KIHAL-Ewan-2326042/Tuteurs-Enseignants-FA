<?php

namespace Blog\Models;

use Exception;
use Includes\Database;
use PDO;
use PDOException;

class Dashboard{
    private Database $db;

    /**
     * Constructeur de la classe dashboard (modèle)
     * @param Database $db Instance de la base de données
     */
    public function __construct(Database $db){
        $this->db = $db;
    }

    /**
     * Récupère les colonnes d'une table donnée dans la base de données
     * @param string $tableName Nom de la table
     * @return array Liste des colonnes
     * @throws Exception En cas d'erreur lors de l'exécution de la requête SQL
     */
    public function getTableColumn(string $tableName): array {
        try {
            // Requête SQL pour obtenir les noms des colonnes
            $query = "
                SELECT column_name
                FROM information_schema.columns
                WHERE table_name = :table_name";

            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->execute();

            // Récupération des colonnes en tant que tableau
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $columns ?: [];
        } catch (PDOException) {
            // Gestion des erreurs liées à la base de données
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
            // Vérifie la présence d'au moins une colonne dans la table
            return !empty($this->getTableColumn($tableName));
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Récupère les en-têtes d'un fichier CSV
     * @param string $csvFilePath Chemin du fichier CSV
     * @return array Liste des en-têtes
     * @throws Exception En cas d'erreur de lecture du fichier CSV
     */
    public function getCsvHeaders(string $csvFilePath): array {
        // Ouverture du fichier CSV et lecture de la première ligne (les en-têtes)
        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle,1000,";");
            fclose($handle);
            return $headers ?: [];
        }
        throw new Exception("Impossible de lire le fichier CSV");
    }

    /**
     * Vérifie que les colonnes du fichier CSV correspondent aux colonnes attendues
     * dans la base de données
     * @param array $headers Liste des en-têtes
     * @param string $tableName Nom de la table
     * @return bool True si il y a un correspondance entre les colonnes, sinon False
     * @throws Exception En cas de non-correspondance entre les colonnes
     */
    public function validateHeaders(array $headers, string $tableName): bool {
        // Comparaison des en-têtes du CSV avec les colonnes de la table dans la base de données
        $tableColumns = array_map('strtolower', $this->getTableColumn($tableName));
        $csvHeaders = array_map('strtolower', $headers);

        error_log("Table columns: " . print_r($tableColumns[0], true));
        error_log("CSV headers: " . print_r($csvHeaders[0], true));

        try {
            if (($tableName != 'teacher' AND array_diff($csvHeaders, $tableColumns)) OR
                ($tableName == 'teacher' AND array_diff($csvHeaders, array_merge($tableColumns, ['address$type'], ['discipline_name'])))) {

                // Crée une exception avec les colonnes CSV qui causent l'erreur
                throw new Exception("Les colonnes CSV ne correspondent pas à la table $tableName ou aux valeurs demandées pour la table teacher. Colonnes CSV: " . implode(', ', $csvHeaders) . " Colonnes attendues: " . implode(', ', array_merge($tableColumns, ['address$type'], ['discipline_name'])));
            } else {
                return true;
            }
        } catch (Exception $e) {
            // Affichage du message d'erreur détaillé
            echo "Erreur : " . $e->getMessage();
        }
    }

    /**
     * Traite un fichier CSV et insère ses données dans la table correspondante
     * @param string $csvFilePath
     * @param string $tableName
     * @return bool Ture si le traitement réussit, sinon False
     * @throws Exception En cas d'erreur lors de l'importation des données
     */
    public function processCsv(string $csvFilePath, string $tableName): bool {
        if (($handle = fopen($csvFilePath, "r")) === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV.");
        }

        $headers = fgetcsv($handle, 1000, ";");
        if (!$this->validateHeaders($headers, $tableName)) {
            fclose($handle);
            return false;
        }

        try {
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $this->insertIntoDatabase($data, $tableName);
            }
            fclose($handle);
            return true;
        } catch (Exception $e) {
            fclose($handle);
            throw new Exception("Erreur lors du traitement du fichier CSV (merci de vérifier que vous repectez bien le guide utilisateur).". $e->getMessage());
        }
    }


    /**
     * Insère des données dans la base de données en fonction du type de table
     * @param array $data Données à insérer
     * @param string $tableName Nom de la table
     * @return void
     * @throws Exception En cas d'erreur lors de l'insertion
     */
    private function insertIntoDatabase(array $data, string $tableName): void {
        switch ($tableName) {
            case 'teacher':
                $this->insertTeacherData($data);
                break;
            case 'student':
                $this->insertStudentData($data);
                break;
            case 'internship':
                $this->insertInternshipData($data);
                break;
            default:
                $this->insertGenericData($data, $tableName);
                break;
        }
    }

    /**
     * Insère des données génériques dans la base de données
     * @param array $data Données à insérer
     * @param string $tableName Nom de la table
     * @return void
     * @throws Exception En cas d'erreur lors de l'insertion
     */
    private function insertGenericData(array $data, string $tableName): void {
        $tableColumns = $this->getTableColumn($tableName);
        if (count($data) !== count($tableColumns)) {
            return;
        }

        $query = "INSERT INTO $tableName (" . implode(',', $tableColumns) . ") 
                  VALUES (" . implode(',', array_map(fn($i) => ":column$i", range(1, count($tableColumns)))) . ")";
        $stmt = $this->db->getConn()->prepare($query);

        foreach ($data as $index => $value) {
            $stmt->bindValue(":column" . ($index + 1), $value ?: null);
        }

        $stmt->execute();
    }

    //----- IMPORTATION Teacher -----//

    /**
     * Insère des données spécifiques pour la table teacher
     * @param array $data Données à insérer
     * @return void
     * @throws Exception En cas d'erreur lors de l'insertion
     */
    private function insertTeacherData(array $data): void {
        $teacher = [$data[0], $data[1], $data[2], $data[3]];
        $discipline = ['discipline_name' => $data[4]];
        $address = [
            'address' => explode('$',$data[5])[0],
            'type'=> explode('$',$data[5])[1]];

        // Colonnes pour la table teacher
        $teacherColumns = $this->getTableColumn('teacher');
        $teacherData = array_combine($teacherColumns, $teacher);
        // Insertion dans la table teacher
        $this->insertGenericData($teacher, 'teacher');

        // Insertion dans la table has_address
        $this->insertGenericData([['id_teacher' => $teacherData['id_teacher']], $address], 'has_address');

        // Insertion dans la table is_taught
        $this->insertGenericData([['id_teacher' => $teacherData['id_teacher']], $discipline], 'is_taught');

        // Insertion dans la table user_connect
        $this->insertUserConnect($teacherData['id_teacher'], 'default_password');

        // Insertion dans la table has_role
        $department = $_SESSION['role_department'] ?? null;
        if ($department) {
            $this->insertHasRole($teacherData['id_teacher'], $department[0]);
        }
    }

    /**
     * Insère un utilisateur dans la table user_connect
     * @param string $userId Identifiant de l'utilisateur
     * @param string $user_pass Mot de passe de l'utilisateur
     * @return void
     */
    private function insertUserConnect(string $userId, string $user_pass): void {
        $query = "INSERT INTO user_connect (user_id, user_pass) VALUES (:user_id, :user_pass)";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':user_pass', password_hash($user_pass, PASSWORD_DEFAULT));
        $stmt->execute();
    }

    /**
     * Insère une association utilisateur-départment dans la table has_role
     * @param string $userId Identifiant de l'utilisateur
     * @param string $department Nom du département
     * @return void
     */
    private function insertHasRole(string $userId, string $department): void {
        $query = "INSERT INTO has_role (user_id, role_name, department_name) VALUES (:user_id, 'Teacher' ,:department)";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
    }

    //----- IMPORTATION Student -----//

    /**
     * Insère des données spécifiques pour la table student
     * @param array $data Données à insérer
     * @return void
     * @throws Exception En cas d'erreur lors de l'insertion
     */
    private function insertStudentData(array $data): void {
        // Colonnes pour la table student
        $studentColumns = $this->getTableColumn('student');
        $studentData = array_combine($studentColumns, $data);

        // Insertion dans la table teacher
        $this->insertGenericData($data, 'student');

        // Insertion dans la table study_at
        $department = $_SESSION['role_department'] ?? null;
        if ($department) {
            $this->insertStudyAt($studentData['student_number'], $department[0]);
        }
    }

    /**
     * Associe un étudiant à un département dans la table study_at
     * @param string $student_number Numéro étudiant
     * @param string $department Nom de département
     * @return void
     */
    public function insertStudyAt(string $student_number, string $department): void {
        $query = "INSERT INTO study_at (student_number, department_name) VALUES (:student_number, :department)";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindValue(':student_number', $student_number);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
    }

    //----- IMPORTATION Student -----//

    /**
     * Insère les données relatives à un stage dans la table internship
     * @param array $data Données du stage à insérer
     * @return void
     * @throws Exception En cas de données manquantes ou d'association déjà existante
     */
    private function insertInternshipData(array $data): void {
        // Colonnes pour la table internship
        $internshipColumns = $this->getTableColumn('internship');
        $internshipData = array_combine($internshipColumns, $data);

        $idTeacher = $internshipData['id_teacher'] ?? null;
        $studentNumber = $internshipData['student_number'] ?? null;

        if (!$studentNumber) {
            throw new Exception("Les données student_number sont manquantes.");
        }

        // Vérification si la combinaison existe déjà
        if ($this->internshipExists($idTeacher, $studentNumber)) {
            throw new Exception("L'association id_teacher '$idTeacher' et student_number '$studentNumber' existe déjà.");
        }

        // Insertion dans la table internship
        $this->insertGenericData($data, 'internship');
    }

    /**
     * Vérifie si une association id_teacher et student_number existe déjà avant insertion
     * @param string $idTeacher L'identifiant de l'enseignant
     * @param string $studentNumber Le numéro d'étudiant
     * @return bool Retourne true si l'association existe déjà, sinon false
     */
    public function internshipExists(string $idTeacher, string $studentNumber): bool {
        $query = "
        SELECT COUNT(*) 
        FROM internship 
        WHERE id_teacher = :id_teacher AND student_number = :student_number
    ";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindValue(':id_teacher', $idTeacher);
        $stmt->bindValue(':student_number', $studentNumber);
        $stmt->execute();

        // Retourne True si un enregistrement existe, False sinon
        return $stmt->fetchColumn() > 0;
    }


    //----- EXPORTATION -----//

    /**
     * Exportation des données de la base de données vers un fichier CSV
     * pour une table donnée
     * @param string $tableName Nom de la table
     * @param array $headers Liste des en-têtes
     * @return bool True si l'exportation réussit, sinon False
     * @throws Exception En cas d'erreur lors de l'exportation
     */
    public function exportToCsvByDepartment(string $tableName, array $headers): bool {
        $db = $this->db;
        $department = $_SESSION['role_department'];

        ob_start();

        // Configuration des en-têtes HTTP pour le téléchargement du fichier CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tableName . '_export.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        if ($output === false){
            throw new Exception("Impossible d'ouvrir le fichier CSV");
        }

        // Ecriture des en-têtes dans le fichier CSV
        fputcsv($output, $headers, ';');

        if (empty($headers)) {
            throw new Exception("Les en-têtes sont manquants ou invalides pour la table $tableName.");
        }

        if ($tableName != 'teacher') {
            // Construction de la requête SQL filtré par le département de l'administrateur
            $query = "SELECT " . implode(',', array_map(fn($header) => "$tableName." . (string)$header, $headers)) . " FROM $tableName";

            $query .= match ($tableName) {
                'internship' => " JOIN student ON internship.student_number = student.student_number JOIN study_at ON study_at.student_number = student.student_number WHERE study_at.department_name = :department",
                'student' => " JOIN study_at ON student.student_number = study_at.student_number WHERE study_at.department_name = :department",
                default => throw new Exception("Table non reconnue : " . $tableName),
            };
        }
        else {$query = "SELECT teacher.maxi_number_trainees, teacher.id_teacher, teacher.teacher_name, teacher.teacher_firstname, CONCAT(has_address.address, '$', has_address.type) AS address_type, is_taught.discipline_name AS discipline FROM teacher  JOIN has_role ON teacher.id_teacher = has_role.user_id  JOIN department ON department.department_name = has_role.department_name  JOIN has_address ON teacher.id_teacher = has_address.id_teacher  JOIN is_taught ON teacher.id_teacher = is_taught.id_teacher  WHERE department.department_name = :department";
        }


        if (is_array($department)) {
            $department = $department[0] ?? '';
        }

        // Préparation et exécution de la requête SQL
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':department', $department);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($tableName == 'teacher') {
                if (isset($row['address_type']) && isset($row['discipline_name'])) {
                    $row['address$type'] = $row['address_type'] . ' ' . $row['discipline_name'];
                    unset($row['address_type']);
                    unset($row['discipline_name']);
                }
            }
            fputcsv($output, $row, ';');
        }
        fclose($output);
        $csvData = ob_get_clean();
        echo $csvData;

        exit();
    }

    /**
     * Exporte un modèle CSV avec les bonnes colonnes
     * @param string $tableName Nom de la table
     * @return bool Retourne true si l'export réussit, sinon lève une exception
     * @throws Exception Si le fichier CSV ne peut être ouvert ou si aucune colonne n'est trouvée dans la table
     */
    public function exportModel(string $tableName): bool {
        $db = $this->db;

        ob_start();

        // Configuration des en-têtes HTTP pour le téléchargement du fichier CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tableName . '_export.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Ouverture d'un flux de sortie pour écrire dans le fichier CSV
        $output = fopen('php://output', 'w');
        if ($output === false){
            throw new Exception("Impossible d'ouvrir le fichier CSV");
        }

        // Récupération des colonnes de la base de données
        $columns = $this->getTableColumn($tableName);

        // Ajout de colonnes spécifiques pour la table 'teacher'
        if ($tableName === 'teacher') {
            $columns[] = 'address$type';
            $columns[] = 'discipline_name';
        }

        // Vérification que des colonnes ont bien été trouvées
        if (empty($columns)) {
            throw new Exception("Aucune colonne trouvée pour la table $tableName.");
        }

        // Ecriture des en-têtes dans le fichier CSV
        fputcsv($output, $columns,';');

        fclose($output);
        $csvData = ob_get_clean();
        echo $csvData;

        exit();
    }

}