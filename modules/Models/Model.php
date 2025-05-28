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
    private StatefulGeocoder $geocoder;
    private string $cacheFile;
    private array $cache = [];
    private array $preparedStatements = [];

    public function __construct(Database $db, string $photonUrl = 'http://localhost:2322', string $cacheFile = __DIR__ . '/geocode_cache.json')
    {
        $this->_db = $db;
        $this->cacheFile = $cacheFile;
        $this->loadCache();

        $httpClient = new GuzzleHttpClient();
        $provider = new Photon($httpClient, $photonUrl);
        $this->geocoder = new StatefulGeocoder($provider, 'fr');
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
     * Géocode une adresse en latitude/longitude, avec cache local.
     */
    public function geocodeAddress(string $address): ?array
    {
        $cacheKey = md5($address);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $result = $this->geocoder->geocodeQuery(GeocodeQuery::create($address));
            if ($result->isEmpty()) {
                $coordinates = null;
            } else {
                $coordinates = [
                    'lat' => $result->first()->getLatitude(),
                    'lng' => $result->first()->getLongitude(),
                ];
            }
            $this->cache[$cacheKey] = $coordinates;
            $this->saveCache();
            return $coordinates;
        } catch (Exception $e) {
            // En cas d’erreur de géocodage, on renvoie null
            return null;
        }
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
    public function calculateRelevanceTeacherStudentsAssociate(array $teacher, array $dictCoef, array $internship): array
    {
        $identifier = $teacher['id_teacher'];
        $dictValues = [];
        $internshipModel = new Internship($this->_db);

        if (isset($dictCoef['Distance'])) {
            $dictValues["Distance"] = $internshipModel->getDistance($internship['internship_identifier'], $identifier, isset($internship['id_teacher']));
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
            "id_teacher" => $identifier,
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
            "score" => round($ScoreFinal, 2),
            "type" => $internship['type']
        ];

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

    public function getCsvHeaders(string $csvFilePath): array
    {
        if (($handle = fopen($csvFilePath, "r")) !== false) {
            $headers = fgetcsv($handle, 1000, ",");
            fclose($handle);
            return $headers ?: [];
        }
        throw new Exception("Impossible de lire le fichier CSV");
    }

    public function validateHeaders(array $headers, string $tableName): bool
    {
        $tableColumns = array_map('strtolower', $this->getTableColumn($tableName));
        $csvHeaders = array_map('strtolower', $headers);

        if (($tableName != 'teacher' && array_diff($csvHeaders, $tableColumns)) || ($tableName == 'teacher' && array_diff($csvHeaders, array_merge($tableColumns, ['address$type'], ['discipline_name'])))) {
            throw new Exception("Les colonnes CSV ne correspondent pas à la table " . $tableName . " ou aux valeurs demandées pour la table teacher pour une insertion de stage.");
        } else {
            return true;
        }
    }

    public function processCsv(string $csvFilePath, string $tableName): bool
    {
        if (($handle = fopen($csvFilePath, "r")) === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV.");
        }

        $headers = fgetcsv($handle, 1000, ",");

        if (!$this->validateHeaders($headers, $tableName)) {
            fclose($handle);
            return false;
        }

        try {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $this->insertIntoDatabase($data, $tableName);
            }
            fclose($handle);
            return true;
        } catch (Exception) {
            fclose($handle);
            throw new Exception("Erreur lors du traitement du fichier CSV (merci de vérifier que vous respectez bien le guide utilisateur avec un éditeur de texte).");
        }
    }

    public function insertIntoDatabase(array $data, string $tableName): void
    {
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

    public function insertStudentData(array $data): void
    {
        $studentColumns = $this->getTableColumn('student');
        if (count($studentColumns) !== count($data)) {
            throw new Exception();
        }
        $studentData = array_combine($studentColumns, $data);

        $this->insertGenericData($data, 'student');

        $department = $_SESSION['role_department'] ?? null;
        if ($department) {
            $this->insertStudyAt($studentData['student_number'], $department[0]);
        }
    }

    public function insertStudyAt(string $student_number, string $department): void
    {
        $query = "INSERT INTO study_at (student_number, department_name) VALUES (:student_number, :department)";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindValue(':student_number', $student_number);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
    }

    public function insertTeacherData(array $data): void
    {
        $userModel = new User($this->_db);

        $teacher = [$data[0], $data[1], $data[2], $data[3], $data[4]];
        $discipline = [1 => $data[6]];
        $explodedData = explode('$', $data[5]);
        $address = [1 => $explodedData[0], 2 => $explodedData[1] ?? null];

        $teacherColumns = $this->getTableColumn('teacher');

        if (count($teacherColumns) + 2 !== count($data)) {
            throw new Exception('Le nombre de données ne correspond pas aux colonnes attendues pour la table teacher.');
        }

        $teacherData = array_combine($teacherColumns, $teacher);

        $this->insertGenericData($teacher, 'teacher');

        $this->insertGenericData([0 => $teacherData['id_teacher']] + $address, 'has_address');

        $this->insertGenericData([0 => $teacherData['id_teacher']] + $discipline, 'is_taught');

        $userModel->insertUserConnect((string)$teacherData['id_teacher'], 'default_password');

        $department = $_SESSION['role_department'] ?? null;
        if ($department) {
            $userModel->insertHasRole((string)$teacherData['id_teacher'], $department[0]);
        }
    }

    public function insertInternshipData(array $data): void
    {
        $internshipColumns = $this->getTableColumn('internship');
        if (count($internshipColumns) !== count($data)) {
            throw new Exception();
        }
        $internshipData = array_combine($internshipColumns, $data);

        $idTeacher = $internshipData['id_teacher'] ?? null;
        $studentNumber = $internshipData['student_number'] ?? null;

        $internshipModel = new Internship($this->_db);

        if (!$studentNumber) {
            throw new Exception("Les données student_number sont manquantes.");
        }

        if ($internshipModel->internshipExists($idTeacher, $studentNumber)) {
            throw new Exception("L'association id_teacher '" . $idTeacher . "' et student_number '" . $studentNumber . "' existe déjà.");
        }

        $this->insertGenericData($data, 'internship');
    }

    public function insertGenericData(array $data, string $tableName): void
    {
        $tableColumns = $this->getTableColumn($tableName);

        if (count($data) !== count($tableColumns)) {
            return;
        }

        $query = "INSERT INTO $tableName (" . implode(',', $tableColumns) . ") VALUES (" . implode(',', array_map(fn($i) => ":column$i", range(1, count($tableColumns)))) . ")";

        $stmt = $this->_db->getConn()->prepare($query);

        foreach ($data as $index => $value) {
            $stmt->bindValue(":column" . ($index + 1), $value === '' || $value === false ? null : $value);
        }

        $stmt->execute();
    }

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
            $query = "SELECT teacher.maxi_number_intern, teacher.maxi_number_apprentice, teacher.id_teacher, teacher.teacher_name, teacher.teacher_firstname, CONCAT(has_address.address, '$', has_address.type) AS address_type, is_taught.discipline_name AS discipline FROM teacher JOIN has_role ON teacher.id_teacher = has_role.user_id JOIN department ON department.department_name = has_role.department_name JOIN has_address ON teacher.id_teacher = has_address.id_teacher JOIN is_taught ON teacher.id_teacher = is_taught.id_teacher WHERE department.department_name = :department group by teacher.id_teacher, maxi_number_intern, maxi_number_apprentice, teacher_name, teacher_firstname, address_type, discipline";
        }

        if (is_array($department)) {
            $department = $department[0] ?? '';
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

    private function paginateGeneric(string $identifier, int $start, int $length, string $search, array $order, array $columns, string $baseCountQuery, string $baseDataQuery, array $bindings = []): array
    {
        $searchParam = '%' . $search . '%';

        if (!empty($search)) {
            $baseCountQuery .= ' AND (' . implode(' OR ', array_map(fn($col) => "$col ILIKE :search", $bindings['searchable'])) . ')';
        }
        $countStmt = $this->_db->getConn()->prepare($baseCountQuery);
        $countStmt->bindValue(':identifier', $identifier);
        if (!empty($search)) {
            $countStmt->bindValue(':search', $searchParam);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if (!empty($search)) {
            $baseDataQuery .= ' AND (' . implode(' OR ', array_map(fn($col) => "$col ILIKE :search", $bindings['searchable'])) . ')';
        }
        if (!empty($order) && isset($order['column']) && isset($columns[$order['column']])) {
            $colName = $columns[$order['column']];
            $dir = strtoupper($order['dir']) === 'DESC' ? 'DESC' : 'ASC';
            $baseDataQuery .= " ORDER BY $colName $dir";
        } else {
            $baseDataQuery .= ' ORDER BY s.student_name ASC';
        }
        $baseDataQuery .= ' LIMIT :limit OFFSET :offset';

        $dataStmt = $this->_db->getConn()->prepare($baseDataQuery);
        $dataStmt->bindValue(':identifier', $identifier);
        if (!empty($search)) {
            $dataStmt->bindValue(':search', $searchParam);
        }
        $dataStmt->bindValue(':limit', $length, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $start, PDO::PARAM_INT);
        $dataStmt->execute();
        $results = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $results,
            'total' => $total
        ];
    }
}
