<?php
/**
 * Fichier contenant le modèle des fonctionnalités n'étant associées
 * à aucune table de la base de données en particulier
 *
 * PHP version 8.3
 *
 * @category Model
 * @package  TutorMap/modules/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Models;

use Exception;
use Includes\Database;
use PDO;
use PDOException;

/**
 * Classe gérant toutes les fonctionnalités du site associées n'étant
 * associées à aucune table de la base de données en particulier
 *
 * PHP version 8.3
 *
 * @category Model
 * @package  TutorMap/modules/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Model
{

    private Database $_db;

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Database $db Instance de la classe Database
     *                     servant de lien avec la base de données
     */
    public function __construct(Database $db)
    {
        $this->_db = $db;
    }

    /**
     * Géocode une adresse passée en paramètre
     *
     * @param string $address Adresse que l'on veut géocoder
     *
     * @return array|null Renvoie un tableau contenant la latitude et la longitude
     */
    public function geocodeAddress(string $address): ?array
    {
        $url = "https://nominatim.openstreetmap.org/search?format=json&q="
            . urlencode($address);

        $options = [
            "http" => [
                "header" =>
                    "User-Agent: MonApplication/1.0 (contact@monapplication.com)"
            ]
        ];

        $context = stream_context_create($options);

        try {
            $response = @file_get_contents($url, false, $context);
        }
        catch (Exception) {
            return null;
        }

        $data = json_decode($response, true);

        if (!empty($data)) {
            return [
                'lat' => $data[0]['lat'],
                'lng' => $data[0]['lon']
            ];
        }

        return null;
    }

    /**
     * Calcule la durée entre un stage et un professeur avec OSRM
     *
     * @param array $latLngInternship Latitude et longitude de l'origine
     * @param array $latLngTeacher    Latitude et longitude de la destination
     *
     * @return float|int|null Renvoie la durée en minutes, ou null en cas d'erreur
     */
    public function calculateDuration(
        array $latLngInternship,
        array $latLngTeacher
    ): float|int|null {
        $url = "http://router.project-osrm.org/route/v1/driving/"
            . $latLngInternship['lng'] . "," . $latLngInternship['lat'] . ";"
            . $latLngTeacher['lng'] . "," . $latLngTeacher['lat']
            . "?overview=false&alternatives=false&steps=false";

        $options = [
            "http" => [
                "header" =>
                    "User-Agent: MonApplication/1.0 (contact@monapplication.com)"
            ]
        ];

        $context = stream_context_create($options);
        try {
            $response = @file_get_contents($url, false, $context);
        }
        catch (Exception) {
            return 60;
        }

        $data = json_decode($response, true);

        if (isset($data['routes'][0]['duration'])) {
            $duration = round($data['routes'][0]['duration'] / 60);
        } else {
            return null;
        }

        if ($duration >= 9999999) {
            return 60;
        } else {
            return $duration;
        }

    }

    /**
     * Calcule les scores de pertinence entre chaque enseignant
     * et stage/alternance passés en paramètre
     *
     * @param array $teacher    Tableau contenant l'identifiant de l'enseignant
     * @param array $dictCoef   Tableau contenant les coefficients associés à chaque
     *                          critère
     * @param array $internship Tableau contenant le stage/alternance
     *
     * @return array|array[] Renvoie un tableau contenant toutes les informations des
     * associations entre l'enseignant et le stage/alternance
     * ainsi que le score associé
     */
    public function calculateRelevanceTeacherStudentsAssociate(
        array $teacher,
        array $dictCoef,
        array $internship
    ): array {
        $identifier = $teacher['id_teacher'];
        $dictValues = array();
        $internshipModel = new Internship($this->_db);

        // Calculer les valeurs uniquement si elles sont nécessaires
        if (isset($dictCoef['Distance'])) {
            $dictValues["Distance"] = $internshipModel->getDistance(
                $internship['internship_identifier'],
                $identifier,
                isset($internship['id_teacher'])
            );
        }

        if (isset($dictCoef['Cohérence'])) {
            $dictValues["Cohérence"] = round(
                $internshipModel->scoreDiscipSubject(
                    $internship['internship_identifier'],
                    $identifier
                ),
                2
            );
        }

        if (isset($dictCoef['A été responsable'])) {
            $internshipListData = $internshipModel->getInternships(
                $internship['internship_identifier']
            );
            $dictValues["A été responsable"] = $internshipListData;
        }

        if (isset($dictCoef['Est demandé'])) {
            $dictValues["Est demandé"] = $internshipModel->isRequested(
                $internship['internship_identifier'],
                $identifier
            );
        }

        $totalScore = 0;
        $totalCoef = 0;

        // Pour chaque critère dans le dictionnaire de coefficients,
        // calculer le score associé
        foreach ($dictCoef as $criteria => $coef) {
            if (isset($dictValues[$criteria])) {
                $value = $dictValues[$criteria];

                switch ($criteria) {
                case 'Distance':
                    $ScoreDuration = $coef / (1 + 0.02 * $value);
                    $totalScore += $ScoreDuration;
                    break;

                case 'A été responsable':
                    $numberOfInternships = count($value);
                    $baselineScore = 0.7 * $coef;

                    if ($numberOfInternships > 0) {
                        $ScoreInternship = $coef * min(
                            1, log(1 + $numberOfInternships, 2)
                        );
                    } else {
                        $ScoreInternship = $baselineScore;
                    }

                    $totalScore += $ScoreInternship;
                    break;

                case 'Est demandé':
                case 'Cohérence':
                    $ScoreRelevance = $value * $coef;
                    $totalScore += $ScoreRelevance;
                    break;

                default:
                    $totalScore += $value * $coef;
                    break;
                }
                $totalCoef += $coef;
            }
        }

        // Score normalise sur 5
        $ScoreFinal = ($totalScore * 5) / $totalCoef;

        $newList = ["id_teacher" => $identifier,
            "teacher_name" => $teacher["teacher_name"],
            "teacher_firstname" => $teacher["teacher_firstname"],
            "student_number" => $internship["student_number"],
            "student_name" => $internship["student_name"],
            "student_firstname" => $internship["student_firstname"],
            "internship_identifier" => $internship['internship_identifier'],
            "internship_subject" => $internship["internship_subject"],
            "address" => $internship["address"],
            "company_name" => $internship["company_name"],
            "formation" => $internship["formation"],
            "class_group" => $internship["class_group"],
            "score" => round($ScoreFinal, 2), "type" => $internship['type']];

        if (!empty($newList)) {
            return $newList;
        }

        return [[]];
    }

    /**
     * Récupère les colonnes d'une table donnée dans la base de données
     *
     * @param string $tableName Nom de la table
     *
     * @return array Renvoie un tableau contenant les colonnes de la table
     * @throws Exception En cas d'erreur lors de l'exécution de la requête SQL
     */
    public function getTableColumn(string $tableName): array
    {
        try {
            // Requête SQL pour obtenir les noms des colonnes
            $query = "
                SELECT column_name
                FROM information_schema.columns
                WHERE table_name = :table_name";

            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->execute();

            // Récupération des colonnes en tant que tableau
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $columns ?: [];
        } catch (PDOException) {
            // Gestion des erreurs liées à la base de données
            throw new Exception(
                "Impossible de récupérer les colonnes pour la table $tableName."
            );
        }
    }

    /**
     * Vérifie que la table existe et qu'elle contient des colonnes
     *
     * @param string $tableName Nom de la table
     *
     * @return bool Renvoie true si la table est valide, false sinon
     */
    public function isValidTable(string $tableName): bool
    {
        try {
            // Vérifie la présence d'au moins une colonne dans la table
            return !empty($this->getTableColumn($tableName));
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Récupère les en-têtes d'un fichier CSV
     *
     * @param string $csvFilePath Chemin du fichier CSV
     *
     * @return array Renvoie un tableau contenant les en-têtes
     * @throws Exception En cas d'erreur de lecture du fichier CSV
     */
    public function getCsvHeaders(string $csvFilePath): array
    {
        // Ouverture du fichier CSV et lecture de la première ligne (les en-têtes)
        if (($handle = fopen($csvFilePath, "r")) !== false) {
            $headers = fgetcsv($handle, 1000, ";");
            fclose($handle);
            return $headers ?: [];
        }
        throw new Exception("Impossible de lire le fichier CSV");
    }

    /**
     * Vérifie que les colonnes du fichier CSV correspondent aux colonnes attendues
     * dans la base de données
     *
     * @param array  $headers   Liste des en-têtes
     * @param string $tableName Nom de la table
     *
     * @return bool Renvoie true si il y a un correspondance entre les colonnes,
     * false sinon
     * @throws Exception En cas de non-correspondance entre les colonnes
     */
    public function validateHeaders(array $headers, string $tableName): bool
    {
        // Comparaison des en-têtes du CSV avec les colonnes
        // de la table dans la base de données
        $tableColumns = array_map('strtolower', $this->getTableColumn($tableName));
        $csvHeaders = array_map('strtolower', $headers);

        if (($tableName != 'teacher' AND array_diff($csvHeaders, $tableColumns)) 
            OR ($tableName == 'teacher' AND array_diff(
                $csvHeaders,
                array_merge($tableColumns, ['address$type'], ['discipline_name'])
            ))
        ) {

            // Crée une exception avec les colonnes CSV qui causent l'erreur
            throw new Exception(
                "Les colonnes CSV ne correspondent pas à la table "
                . $tableName . " ou aux valeurs demandées pour la table teacher. "
            );
        } else {
            return true;
        }
    }

    /**
     * Traite un fichier CSV et insère ses données dans la table correspondante
     *
     * @param string $csvFilePath Le chemin du fichier CSV à traiter
     * @param string $tableName   Le nom de la table
     *
     * @return bool Renvoie true si le traitement réussit, false sinon
     * @throws Exception En cas d'erreur lors de l'importation des données
     */
    public function processCsv(string $csvFilePath, string $tableName): bool
    {
        // Ouvre le fichier en lecture
        if (($handle = fopen($csvFilePath, "r")) === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV.");
        }

        // Lecture le première ligne du fichier
        $headers = fgetcsv($handle, 1000, ";");

        // Vérifie que les en-têtes du fichier
        // correspondent à celles de la base de données
        if (!$this->validateHeaders($headers, $tableName)) {
            fclose($handle);
            return false;
        }

        try {
            // Insertion des données
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $this->insertIntoDatabase($data, $tableName);
            }
            fclose($handle);
            return true;
        } catch (Exception) {
            fclose($handle);
            throw new Exception(
                "Erreur lors du traitement du fichier CSV 
                (merci de vérifier que vous repectez bien le guide utilisateur). "
            );
        }
    }


    /**
     * Insère des données dans la base de données en fonction du type de table
     *
     * @param array  $data      Données à insérer
     * @param string $tableName Nom de la table
     *
     * @return void
     * @throws Exception En cas d'erreur lors de l'insertion
     */
    public function insertIntoDatabase(array $data, string $tableName): void
    {
        // Appelle de la méthode d'insertion correspondante selon le nom de la table
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
     * Insère des données spécifiques pour la table student
     *
     * @param array $data Données à insérer
     *
     * @return void
     * @throws Exception En cas d'erreur lors de l'insertion
     */
    public function insertStudentData(array $data): void
    {
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
     *
     * @param string $student_number Numéro étudiant
     * @param string $department     Nom de département
     *
     * @return void
     */
    public function insertStudyAt(string $student_number, string $department): void
    {
        $query = "INSERT INTO study_at (student_number, department_name)
                    VALUES (:student_number, :department)";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindValue(':student_number', $student_number);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
    }


    /**
     * Insère des données spécifiques pour la table teacher
     *
     * @param array $data Données à insérer
     *
     * @return void
     * @throws \mysql_xdevapi\Exception En cas d'erreur lors de l'insertion
     * @throws Exception
     */
    public function insertTeacherData(array $data): void
    {
        $userModel = new User($this->_db);
        $teacher = [$data[0], $data[1], $data[2], $data[3]];
        $discipline = ['discipline_name' => $data[4]];
        $explodedData = explode('$', $data[5]);
        $address = [
            'address' => $explodedData[0],
            'type' => isset($explodedData[1]) ? $explodedData[1] : null
        ];
        // Colonnes pour la table teacher
        $teacherColumns = $this->getTableColumn('teacher');
        $teacherData = array_combine($teacherColumns, $teacher);

        // Insertion dans la table teacher
        $this->insertGenericData($teacher, 'teacher');

        // Insertion dans la table has_address, is_taught et user_connect
        $this->insertGenericData(
            [['id_teacher' => $teacherData['id_teacher']] + $address],
            'has_address'
        );
        $this->insertGenericData(
            [['id_teacher' => $teacherData['id_teacher']] + $discipline],
            'is_taught'
        );
        $userModel->insertUserConnect(
            $teacherData['id_teacher'],
            'default_password'
        );

        // Insertion dans la table has_role
        $department = $_SESSION['role_department'] ?? null;
        if ($department) {
            $userModel->insertHasRole($teacherData['id_teacher'], $department[0]);
        }
    }

    /**
     * Insère les données relatives à un stage dans la table internship
     *
     * @param array $data Données du stage à insérer
     *
     * @return void
     * @throws Exception En cas de données manquantes ou d'association déjà existante
     */
    public function insertInternshipData(array $data): void
    {
        // Colonnes pour la table internship
        $internshipColumns = $this->getTableColumn('internship');
        $internshipData = array_combine($internshipColumns, $data);

        $idTeacher = $internshipData['id_teacher'] ?? null;
        $studentNumber = $internshipData['student_number'] ?? null;

        $internshipModel = new Internship($this->_db);

        if (!$studentNumber) {
            throw new Exception("Les données student_number sont manquantes.");
        }

        // Vérification si la combinaison existe déjà
        if ($internshipModel->internshipExists($idTeacher, $studentNumber)) {
            throw new Exception(
                "L'association id_teacher '" . $idTeacher . "' et student_number '"
                . $studentNumber . "' existe déjà."
            );
        }

        // Insertion dans la table internship
        $this->insertGenericData($data, 'internship');
    }

    /**
     * Insère des données génériques dans la base de données
     *
     * @param array  $data      Données à insérer
     * @param string $tableName Nom de la table
     *
     * @return void
     * @throws Exception En cas d'erreur lors de l'insertion
     */
    public function insertGenericData(array $data, string $tableName): void
    {
        // Récupère les colonne de la base de données
        $tableColumns = $this->getTableColumn($tableName);

        // Vérifie que le nombre de données
        // correspond au nombre de colonne dans la table
        if (count($data) !== count($tableColumns)) {
            return;
        }

        // Réquee SQL d'insertion
        $query = "INSERT INTO $tableName (" . implode(',', $tableColumns) . ")
                  VALUES (" . implode(
            ',', array_map(
                fn($i) => ":column$i", range(
                    1, count($tableColumns)
                )
            )
        ) . ")";
        $stmt = $this->_db->getConn()->prepare($query);

        // Lier les valeurs des données aux paramètres nommés dans le requête
        foreach ($data as $index => $value) {
            $stmt->bindValue(":column" . ($index + 1), $value ?: null);
        }

        $stmt->execute();
    }

    /**
     * Exportation des données de la base de données vers un fichier CSV
     * pour une table donnée
     *
     * @param string $tableName Nom de la table
     * @param array  $headers   Liste des en-têtes
     *
     * @return bool Renvoie true si l'exportation réussit, false sinon
     * @throws Exception En cas d'erreur lors de l'exportation
     */
    public function exportToCsvByDepartment(string $tableName, array $headers): bool
    {
        $db = $this->_db;
        $department = $_SESSION['role_department'];

        ob_start();

        // Configuration des en-têtes HTTP pour le téléchargement du fichier CSV
        header('Content-Type: text/csv');
        header(
            'Content-Disposition: attachment; filename="'
            . $tableName . '_export.csv"'
        );
        header('Pragma: no-cache');
        header('Expires: 0');

        // Ouverture du flux de sortie pour écrire le fichier CSV
        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV");
        }

        // Ecriture des en-têtes dans le fichier CSV
        fputcsv($output, $headers, ';');

        // Vérification des en-têtes
        if (empty($headers)) {
            throw new Exception(
                "Les en-têtes sont manquants ou invalides pour la table $tableName."
            );
        }

        // Construction de la requête SQL selon la table à exporter
        if ($tableName != 'teacher') {
            // Construction de la requête SQL filtré
            // par le département de l'administrateur
            $query = "SELECT " . implode(
                ',',
                array_map(
                    fn($header) =>
                    "$tableName." . $header, $headers
                )
            ) . " FROM $tableName";

            $query .= match ($tableName) {
                'internship' => " JOIN student ON internship.student_number 
                 = student.student_number JOIN study_at ON study_at.student_number 
                 = student.student_number 
                 WHERE study_at.department_name = :department",
                'student' => " JOIN study_at ON student.student_number 
                 = study_at.student_number 
                 WHERE study_at.department_name = :department",
                default => throw new Exception("Table non reconnue : " . $tableName),
            };
        } else {
            $query = "SELECT teacher.maxi_number_trainees, teacher.id_teacher, 
                        teacher.teacher_name, teacher.teacher_firstname, 
                        CONCAT(has_address.address, '$', has_address.type) 
                            AS address_type, is_taught.discipline_name AS discipline 
                        FROM teacher  
                        JOIN has_role ON teacher.id_teacher = has_role.user_id 
                        JOIN department 
                            ON department.department_name = has_role.department_name 
                        JOIN has_address 
                            ON teacher.id_teacher = has_address.id_teacher 
                        JOIN is_taught ON teacher.id_teacher = is_taught.id_teacher 
                        WHERE department.department_name = :department";
        }

        if (is_array($department)) {
            $department = $department[0] ?? '';
        }

        // Préparation et exécution de la requête SQL
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindValue(':department', $department);
        $stmt->execute();

        // Boucle pour récupérer les résultats
        // de la requête et les ajouter au fichier CSV
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($tableName == 'teacher') {
                if (isset($row['address_type']) && isset($row['discipline_name'])) {
                    $row['address$type'] = $row['address_type']
                        . ' ' . $row['discipline_name'];
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
     *
     * @param string $tableName Nom de la table
     *
     * @return bool Renvoie true si l'export réussit, sinon lève une exception
     * @throws Exception Si le fichier CSV ne peut être ouvert
     * ou si aucune colonne n'est trouvée dans la table
     */
    public function exportModel(string $tableName): bool
    {
        ob_start();

        // Configuration des en-têtes HTTP pour le téléchargement du fichier CSV
        header('Content-Type: text/csv');
        header(
            'Content-Disposition: attachment; filename="'
            . $tableName . '_export.csv"'
        );
        header('Pragma: no-cache');
        header('Expires: 0');

        // Ouverture d'un flux de sortie pour écrire dans le fichier CSV
        $output = fopen('php://output', 'w');
        if ($output === false) {
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
        fputcsv($output, $columns, ';');

        fclose($output);
        $csvData = ob_get_clean();
        echo $csvData;

        exit();
    }
}