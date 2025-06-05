<?php
declare(strict_types=1);

namespace Blog\Models;

use Exception;
use includes\Database;
use PDO;
use PDOException;
use Geocoder\Provider\Photon\Photon;
use Geocoder\Query\GeocodeQuery;
use Http\Adapter\Guzzle7\Client as GuzzleHttpClient;
use Geocoder\StatefulGeocoder;

class Model
{
    private Database $_db;
    private string $cacheFile;
    private array $cache = [];
    private array $preparedStatements = [];

    public function __construct(Database $db, string $cacheFile = __DIR__ . '/geocode_cache.json')
    {
        $this->_db = $db;
        $this->cacheFile = $cacheFile;
        $this->loadCache();
    }

    private function loadCache(): void
    {
        if (file_exists($this->cacheFile)) {
            $content = file_get_contents($this->cacheFile);
            $this->cache = json_decode($content, true) ?: [];
        }
    }

    private function saveCache(): void
    {
        file_put_contents($this->cacheFile, json_encode($this->cache, JSON_PRETTY_PRINT));
    }

    /**
     * Géocode une adresse en latitude/longitude en utilisant Nominatim avec cache local.
     */
    public function geocodeAddress(string $address): ?array
    {
        // Nettoyage de l'adresse pour l'utiliser comme clé de cache
        $key = md5(trim(strtolower($address)));

        // Vérifie si l'adresse est déjà en cache
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($address);

        $options = [
            "http" => [
                "header" => "User-Agent: TutormapFA/1.0 (contact@monapplication.com)"
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            return null;
        }

        if (isset($data[0]['lat'], $data[0]['lon'])) {
            $coords = [
                'lat' => (float)$data[0]['lat'],
                'lng' => (float)$data[0]['lon']
            ];

            // Sauvegarde dans le cache et dans le fichier
            $this->cache[$key] = $coords;
            $this->saveCache();

            return $coords;
        }

        return null;
    }



    public function calculateDuration(array $latLngInternship, array $latLngTeacher): ?int
    {
        $url = sprintf(
            "http://router.project-osrm.org/route/v1/driving/%F,%F;%F,%F?overview=false&alternatives=false&steps=false",
            $latLngInternship['lng'], $latLngInternship['lat'], $latLngTeacher['lng'], $latLngTeacher['lat']
        );

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => "MonApplication/1.0 (contact@monapplication.com)",
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            // Log error $curlError si besoin
            return 60; // timeout fallback
        }

        $data = json_decode($response, true);

        if (!isset($data['routes'][0]['duration'])) {
            return null;
        }

        $duration = round($data['routes'][0]['duration'] / 60);

        if ($duration >= 9999999) {
            return 60;
        }

        return (int)$duration;
    }

    public function getDispatchList(): void
    {
        header('Content-Type: application/json');

        $dictCoef = $_SESSION['last_dict_coef'] ?? [];
        if (empty($dictCoef)) {
            echo json_encode(['data' => []]);
            exit();
        }

        $resultDispatchList = $this->internshipModel
            ->dispatcher(
                $this->departmentModel,
                $this->teacherModel,
                $dictCoef
            )[0];

        $data = [];
        foreach ($resultDispatchList as $item) {
            $checkboxValue = $item['id_teacher'] . '$' . $item['internship_identifier'] . '$' . $item['score'];
            $companyName = $item['company_name'];

            $data[] = [
                'teacher' => $item['teacher_firstname'] . ' ' . $item['teacher_name'] . ' (' . $item['id_teacher'] . ')',
                'student' => $item['student_firstname'] . ' ' . $item['student_name'] . ' (' . $item['student_number'] . ')',
                'internship' => $companyName . ' (' . $item['internship_identifier'] . ')',
                'formation' => $item['formation'],
                'group' => $item['class_group'],
                'subject' => $item['internship_subject'],
                'address' => $item['address'],
                'score' => $item['score'],
                'associate' => '<input type="checkbox" class="dispatch-checkbox" name="listTupleAssociate[]" value="' . htmlspecialchars($checkboxValue) . '">'
            ];
        }

        echo json_encode(['data' => $data]);
    }
    public function insertScoreIntoDatabase(array $data): void
    {
        $query = "UPDATE public.internship SET relevance_score = :score WHERE internship_identifier = :internship_identifier AND student_number = :student_number";

        $stmt = $this->_db->getConn()->prepare($query);

        $stmt->bindValue(':score', $data['score']);
        $stmt->bindValue(':internship_identifier', $data['internship_identifier']);
        $stmt->bindValue(':student_number', $data['student_number']);

        $stmt->execute();
    }


    public function calculateRelevanceTeacherStudentsAssociate(array $teacher,array $dictCoef, array $internship): array
    {
        $defaultCoefs = [
            'Distance' => 10,
            'Discipline' => 3,
            'A été responsable' => 1,
            'Est demandé' => 1
        ];
        $dictCoef = array_merge($defaultCoefs, $dictCoef);

        $identifier = $teacher['id_teacher'];
        $dictValues = [];
        $internshipModel = new Internship($this->_db);

        if (isset($dictCoef['Distance'])) {
            $distance = $internshipModel->getDistance($internship['internship_identifier'], $identifier, isset($internship['id_teacher']));
            $dictValues["Distance"] = $distance;

            if (is_numeric($distance)) {
                $conn = $this->_db->getConn();
                $stmt = $conn->prepare('INSERT INTO Distance (internship_identifier, id_teacher, distance)
                                                                      VALUES (:internship, :teacher, :distance)
                                                                      ON CONFLICT (internship_identifier, id_teacher) 
                                                                      DO UPDATE SET distance = :distance');

                $stmt->execute([
                    ':teacher' => $teacher['id_teacher'],
                    ':internship' => $internship['internship_identifier'],
                    ':distance' => $distance,
                ]);
            }
        }

        if (isset($dictCoef['Discipline'])) {
            $dictValues["Discipline"] = round($internshipModel->scoreDiscipSubject($internship['internship_identifier'], $identifier), 2);
        }

        if (isset($dictCoef['A été responsable'])) {
            $internshipListData = $internshipModel->getInternships($internship['internship_identifier']);
            $dictValues["A été responsable"] = $internshipListData;
        }

        if (isset($dictCoef['Est demandé'])) {
            $dictValues["Est demandé"] = $internshipModel->isRequested($internship['internship_identifier'], $identifier);
        }

        $totalScore = 0;
        $totalCoef = 0;

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
                            $ScoreInternship = $coef * min(1, log(1 + $numberOfInternships, 2));
                        } else {
                            $ScoreInternship = $baselineScore;
                        }

                        $totalScore += $ScoreInternship;
                        break;

                    case 'Est demandé':
                    case 'Discipline':
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

        $ScoreFinal = ($totalScore * 5) / $totalCoef;

        $newList = [
            "teacher_firstname" => 'errr',
            "teacher_name" => 'eezeezz',
            "Distance" =>$distance,
            "Discipline" =>$dictValues["Discipline"],
            "company_name" => $internship['company_name'],
            "internship_subject" => $internship['internship_subject'],
            "address" => $internship['address'],
            "id_teacher" => $identifier,
            "internship_identifier" => $internship['internship_identifier'],
            "student_number" => $internship['student_number'],
            "score" => round($ScoreFinal, 2),
            "type" => $internship['type']
        ];

        $this->insertScoreIntoDatabase($newList);

        if (!empty($newList)) {
            return $newList;
        }

        return [[]];
    }

    public function getTableColumn(string $tableName): array
    {
        if (isset($this->cache['getTableColumn'][$tableName])) {
            return $this->cache['getTableColumn'][$tableName];
        }

        try {
            $query = "SELECT column_name FROM information_schema.columns WHERE table_name = :table_name";
            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->execute();

            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->cache['getTableColumn'][$tableName] = $columns ?: [];
            return $columns ?: [];
        } catch (PDOException) {
            throw new Exception("Impossible de récupérer les colonnes pour la table $tableName.");
        }
    }

    public function isValidTable(string $tableName): bool
    {
        try {
            return !empty($this->getTableColumn($tableName));
        } catch (Exception) {
            return false;
        }
    }

    /******* Import CSV ********/

    /**
     * Récupère les en-têtes d'un fichier CSV
     *
     * @param string $csvFile Chemin du fichier CSV
     * @return array Liste des en-têtes du CSV
     * @throws Exception Si le fichier ne peut pas être lu
     */
    public function getCsvHeaders(string $csvFile): array
    {
        if (!file_exists($csvFile) || !is_readable($csvFile)) {
            throw new Exception("Le fichier CSV n'existe pas ou n'est pas lisible.");
        }

        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV.");
        }

        // Lire la première ligne pour obtenir les en-têtes
        $headers = fgetcsv($handle);
        fclose($handle);

        if ($headers === false) {
            throw new Exception("Impossible de lire les en-têtes du fichier CSV.");
        }

        return $headers;
    }

    /**
     * Vérifie si les en-têtes du CSV correspondent à la structure de la table
     *
     * @param array $csvHeaders En-têtes du CSV
     * @param string $tableName Nom de la table
     * @return bool True si les en-têtes sont valides
     */
    public function validateHeaders(array $csvHeaders, string $tableName): bool
    {
        $tableColumns = $this->getTableColumn($tableName);

        // Cas spécial pour la table teacher qui peut avoir des colonnes additionnelles
        if ($tableName === 'teacher') {
            $tableColumns[] = 'address$type';
            $tableColumns[] = 'discipline_name';
        }

        // Vérifier que tous les en-têtes du CSV existent dans la table
        foreach ($csvHeaders as $header) {
            if (!in_array($header, $tableColumns)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Traite un fichier CSV et insère/met à jour les données en base
     *
     * @param string $csvFile Chemin du fichier CSV
     * @param string $tableName Nom de la table
     * @return bool True si le traitement a réussi
     * @throws Exception En cas d'erreur pendant le traitement
     */
    public function processCsv(string $csvFile, string $tableName): bool
    {
        $handle = fopen($csvFile, 'r');
        if ($handle === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV.");
        }

        // Lire les en-têtes
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new Exception("Impossible de lire les en-têtes du fichier CSV.");
        }

        // Préparer la transaction
        $conn = $this->_db->getConn();
        $conn->beginTransaction();

        try {
            // Identifier la clé primaire pour la table
            $primaryKey = $this->getPrimaryKeyColumn($tableName);

            // Préparer les requêtes
            if ($tableName === 'teacher') {
                $this->prepareTeacherImport($handle, $headers);
            } else if ($tableName === 'student') {
                $this->prepareStudentImport($handle, $headers);
            } else {
                $this->prepareRegularImport($handle, $headers, $tableName, $primaryKey);
            }

            $conn->commit();
            fclose($handle);
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Récupère la colonne clé primaire d'une table
     *
     * @param string $tableName Nom de la table
     * @return string Nom de la colonne clé primaire
     */
    private function getPrimaryKeyColumn(string $tableName): string
    {
        $keyMap = [
            'teacher' => 'id_teacher',
            'student' => 'student_number',
            'internship' => 'internship_identifier'
            // Ajouter d'autres tables au besoin
        ];

        return $keyMap[$tableName] ?? 'id';
    }

    /**
     * Importe les données pour les tables standard
     */
    private function prepareRegularImport($handle, array $headers, string $tableName, string $primaryKey): void
    {
        // Lecture ligne par ligne
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) {
                throw new Exception("Nombre de colonnes incorrect à la ligne");
            }

            $rowData = array_combine($headers, $data);

            // Vérifier si l'enregistrement existe déjà
            $stmt = $this->_db->getConn()->prepare("SELECT COUNT(*) FROM $tableName WHERE $primaryKey = :key");
            $stmt->bindValue(':key', $rowData[$primaryKey]);
            $stmt->execute();

            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                // UPDATE
                $this->updateRecord($tableName, $rowData, $primaryKey);
            } else {
                // INSERT
                $this->insertRecord($tableName, $rowData);
            }
        }
    }

    /**
     * Traitement spécial pour l'import des étudiants
     */
    private function prepareStudentImport($handle, array $headers): void
    {
        $conn = $this->_db->getConn();
        $department = 'IUT_INFO_AIX'; // Département par défaut

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) {
                throw new Exception("Nombre de colonnes incorrect à la ligne");
            }

            $rowData = array_combine($headers, $data);
            $studentNumber = $rowData['student_number'];

            // Vérifier si l'étudiant existe déjà
            $stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE student_number = :student_number");
            $stmt->bindValue(':student_number', $studentNumber);
            $stmt->execute();
            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                // Mettre à jour l'étudiant
                $this->updateRecord('student', $rowData, 'student_number');
            } else {
                // Insérer le nouvel étudiant
                $this->insertRecord('student', $rowData);
            }

            // Gérer la relation study_at
            $stmt = $conn->prepare("SELECT COUNT(*) FROM study_at WHERE student_number = :student_number");
            $stmt->bindValue(':student_number', $studentNumber);
            $stmt->execute();
            $relationExists = $stmt->fetchColumn() > 0;

            if ($relationExists) {
                // Mettre à jour la relation
                $stmt = $conn->prepare("UPDATE study_at SET department_name = :department_name WHERE student_number = :student_number");
                $stmt->bindValue(':student_number', $studentNumber);
                $stmt->bindValue(':department_name', $department);
                $stmt->execute();
            } else {
                // Insérer la nouvelle relation
                $stmt = $conn->prepare("INSERT INTO study_at (student_number, department_name) VALUES (:student_number, :department_name)");
                $stmt->bindValue(':student_number', $studentNumber);
                $stmt->bindValue(':department_name', $department);
                $stmt->execute();
            }
        }
    }

    /**
     * Traitement spécial pour l'import des enseignants
     */
    private function prepareTeacherImport($handle, array $headers): void
    {
        $conn = $this->_db->getConn();

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) {
                throw new Exception("Nombre de colonnes incorrect à la ligne");
            }

            $rowData = array_combine($headers, $data);
            $teacherId = $rowData['id_teacher'];

            // Vérifier si l'enseignant existe déjà
            $stmt = $conn->prepare("SELECT COUNT(*) FROM teacher WHERE id_teacher = :id");
            $stmt->bindValue(':id', $teacherId);
            $stmt->execute();
            $exists = $stmt->fetchColumn() > 0;

            // Traitement des données enseignant
            $teacherData = array_filter($rowData, function($key) {
                return !in_array($key, ['address$type', 'discipline_name']);
            }, ARRAY_FILTER_USE_KEY);

            if ($exists) {
                $this->updateRecord('teacher', $teacherData, 'id_teacher');
            } else {
                $this->insertRecord('teacher', $teacherData);
            }

            // Traitement de l'adresse si présente
            if (isset($rowData['address$type'])) {
                list($address, $type) = explode('$', $rowData['address$type'] . '$home');

                // Supprimer les anciennes adresses
                $stmt = $conn->prepare("DELETE FROM has_address WHERE id_teacher = :id");
                $stmt->bindValue(':id', $teacherId);
                $stmt->execute();

                // Insérer la nouvelle adresse
                $stmt = $conn->prepare("INSERT INTO has_address (id_teacher, address, type) VALUES (:id, :address, :type)");
                $stmt->bindValue(':id', $teacherId);
                $stmt->bindValue(':address', $address);
                $stmt->bindValue(':type', $type);
                $stmt->execute();
            }

            // Traitement de la discipline si présente
            if (isset($rowData['discipline_name'])) {
                // Supprimer les anciennes disciplines
                $stmt = $conn->prepare("DELETE FROM is_taught WHERE id_teacher = :id");
                $stmt->bindValue(':id', $teacherId);
                $stmt->execute();

                // Insérer la nouvelle discipline
                $stmt = $conn->prepare("INSERT INTO is_taught (id_teacher, discipline_name) VALUES (:id, :discipline)");
                $stmt->bindValue(':id', $teacherId);
                $stmt->bindValue(':discipline', $rowData['discipline_name']);
                $stmt->execute();
            }
        }
    }

    /**
     * Insère un nouvel enregistrement
     */
    private function insertRecord(string $tableName, array $data): void
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $query = "INSERT INTO $tableName (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->_db->getConn()->prepare($query);

        foreach ($data as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }

        $stmt->execute();
    }

    /**
     * Met à jour un enregistrement existant
     */
    private function updateRecord(string $tableName, array $data, string $primaryKey): void
    {
        $setClause = [];
        $params = [];

        foreach ($data as $column => $value) {
            if ($column !== $primaryKey) {
                $setClause[] = "$column = :set_$column";
                $params["set_$column"] = $value;
            }
        }

        if (empty($setClause)) {
            return; // Rien à mettre à jour
        }

        // Préparation de la requête
        $query = "UPDATE $tableName SET " . implode(', ', $setClause) . " WHERE $primaryKey = :pk";
        $stmt = $this->_db->getConn()->prepare($query);

        // Lier tous les paramètres avec leurs préfixes
        foreach ($params as $param => $value) {
            $stmt->bindValue(":$param", $value);
        }

        // Lier la clé primaire séparément
        $stmt->bindValue(":pk", $data[$primaryKey]);

        $stmt->execute();
    }

    /******* Export CSV ********/

    public function exportToCsvByDepartment(string $tableName, array $headers): bool
    {
        $db = $this->_db;
        $department = $_SESSION['role_department'];

        ob_start();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tableName . '_export.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV");
        }

        fputcsv($output, $headers, ',');

        if (empty($headers)) {
            throw new Exception("Les en-têtes sont manquants ou invalides pour la table $tableName.");
        }

        if ($tableName != 'teacher') {
            $query = "SELECT " . implode(',', array_map(fn($header) => "$tableName." . $header, $headers)) . " FROM $tableName";

            $query .= match ($tableName) {
                'internship' => " JOIN student ON internship.student_number = student.student_number JOIN study_at ON study_at.student_number = student.student_number WHERE study_at.department_name = :department",
                'student' => " JOIN study_at ON student.student_number = study_at.student_number WHERE study_at.department_name = :department",
                default => throw new Exception("Table non reconnue : " . $tableName),
            };
        } else {
            $query = "SELECT teacher.maxi_number_intern, teacher.maxi_number_apprentice, teacher.id_teacher, 
                      teacher.teacher_mail, teacher.teacher_name, teacher.teacher_firstname, 
                      CONCAT(has_address.address, '$', has_address.type) AS address_type, 
                      is_taught.discipline_name AS discipline 
                      FROM teacher 
                      JOIN has_role ON teacher.id_teacher = has_role.user_id 
                      JOIN department ON department.department_name = has_role.department_name 
                      JOIN has_address ON teacher.id_teacher = has_address.id_teacher 
                      JOIN is_taught ON teacher.id_teacher = is_taught.id_teacher 
                      WHERE department.department_name = :department 
                      GROUP BY teacher.id_teacher, maxi_number_intern, maxi_number_apprentice, 
                      teacher_mail, teacher_name, teacher_firstname, address_type, discipline";
        }

        if (is_array($department)) {
            if (!empty($department)) {
                $department = $department[0];
            } else {
                throw new Exception("Le département n'est pas défini correctement.");
            }
        }

        if (empty($department)) {
            throw new Exception("Le département n'est pas défini.");
        }

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
            fputcsv($output, $row, ',');
        }
        fclose($output);
        $csvData = ob_get_clean();
        echo $csvData;

        exit();
    }

    public function exportModel(string $tableName): bool
    {
        ob_start();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tableName . '_export_modele.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV");
        }

        $columns = $this->getTableColumn($tableName);

        if ($tableName === 'teacher') {
            $columns[] = 'address$type';
            $columns[] = 'discipline_name';
        }

        if (empty($columns)) {
            throw new Exception("Aucune colonne trouvée pour la table $tableName.");
        }

        fputcsv($output, $columns, ',');

        fclose($output);
        $csvData = ob_get_clean();
        echo $csvData;

        exit();
    }



    public function dispatcherEnMieux($dictCoef): array {
        $final = [];
        $stmt = $this->_db->getConn()->prepare("
        SELECT * FROM internship WHERE id_teacher IS NULL AND end_date_internship > NOW();
    ");
        $stmt->execute();
        $intershipSansTuteur = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->_db->getConn()->prepare("
        with cte_alternance as (
            select id_teacher, COUNT(*) as nb_alternance
            from internship i
            where end_date_internship > Now() and type = 'alternance'
            group by id_teacher
        ), cte_stage as (
            select id_teacher, COUNT(*) as nb_stage
            from internship i
            where end_date_internship > Now() and type = 'stage'
            group by id_teacher
        )
        select t.id_teacher, t.teacher_name, t.teacher_firstname from teacher t
        left join cte_alternance ca on t.id_teacher = ca.id_teacher
        left join cte_stage cs on t.id_teacher = cs.id_teacher
        where maxi_number_intern > nb_stage and maxi_number_apprentice > nb_alternance
    ");
        $stmt->execute();
        $teacherQuiSontPasFull = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($intershipSansTuteur as $internship) {
            $bestTeacher = null;
            $bestScore = -1;

            foreach ($teacherQuiSontPasFull as $teacher) {
                $result = $this->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship);
                if ($result['score'] > $bestScore) {
                    $bestScore = $result['score'];
                    $bestTeacher = $result;
                }
            }

            if ($bestTeacher !== null) {
                $final[] = $bestTeacher;
            }
        }

        usort($final, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $final;
    }


}
