<?php

namespace Models;

use Blog\Models\Dashboard;
use Includes\Database;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de DashboardTest
 *
 * Test du modèle Dashboard : s'assure que uploadCsv()
 * fonctionne comme prévu
 *
 */
class DashboardTest extends TestCase  {
    private string $tempDir;

    /**
     * Créer un répertoire temporaire pour les fichiers CSV
     * @return void
     */
    public function setUp(): void {
        $this->tempDir = 'path/to/temp';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir,0777,true);
        }
    }

    /**
     * Nettoie le répertoire temporaire après les tests
     * @return void
     */
    public function tearDown(): void {
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }

    /**
     * Crée un fichier CSV temporaire
     * @param array $data
     * @return string
     */
    private function createTempCsv(array $data): string {
        $csvFilePath = $this->tempDir . '/file.csv';
        $tempFile = fopen($csvFilePath, 'w');
        if ($tempFile === false) {
            $this->fail("Impossible d'ouvrir le fichier : $csvFilePath");
        }
        foreach ($data as $row) {
            fputcsv($tempFile, $row);
        }
        fclose($tempFile);
        return $csvFilePath;
    }

    // --------- Test importation --------- //

    /**
     * Test pour l'importation de fichier CSV avec succés
     * @param string $method
     * @param array $row
     * @return void
     * @throws Exception
     */
    private function runCsvUploadTest(string $method, array $row): void {
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        //attente pour le mock de la base de données
        $mockDb->method('getConn')->willReturn($mockConn);

        //simulation de la requête
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);

        //création du fichier csv temporaire
        $csvFilePath = $this->createTempCsv([$row]);

        //exécution
        $dashboard = new Dashboard($mockDb);
        $result = $dashboard->$method($csvFilePath);
        $this->assertTrue($result, "L'importation aurait dû réussir pour $method");
    }

    /**
     * Test d'importation pour les étudiants
     * @return void
     * @throws Exception
     */
    public function testUploadCSvStudentSuccess(): void {
        $this->runCsvUploadTest('uploadCsvStudent', ['student_number','student_name','student_firstname','formation','class_group']);
    }
    /**
     * Test d'importation pour les enseignants
     * @return void
     * @throws Exception
     */
    public function testUploadCsvTeacherSuccess() {
        $this->runCsvUploadTest('uploadCsvTeacher', ['id_teacher','teacher_name','teacher_firstname','maxi_number_trainees']);
    }
    /**
     * Test d'importation pour les stages/alternances
     * @return void
     * @throws Exception
     */
    public function testUploadCsvInternshipSuccess() {
        $this->runCsvUploadTest('uploadCsvInternship', ['internship_identifier','company_name','keywords','start_date_internship','type','end_date_internship','internship_subject','address','student_number']);
    }

    /**
     * Test pour l'importation d'un fichier CSV inexistant
     * @return void
     * @throws Exception
     */
    public function testUploadCsvNotFound() {
        //création des mocks
        $mockDb = $this->createMock(Database::class);
        $dashboard = new Dashboard($mockDb);

        //exécution
        $result = $dashboard->uploadCsvStudent('invalide/path/to/file.csv');
        $this->assertFalse($result, "L'importation aurait dû échouer avec un fichier inexistant");
    }

    /**
     * Test d'erreur de base de données lors de l'importation
     * @param string $method
     * @param array $headers
     * @param array $row
     * @return void
     * @throws Exception
     */
    private function runCsvUploadTestDatabaseError (string $method, array $headers, array $row): void {
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        //simulation de l'erreur
        $mockDb->method('getConn')->willReturn($mockConn);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->will($this->throwException(new PDOException("Erreur d'insertion")));

        //creation d'un fichier CSV temporaire
        $csvFilePath = $this->createTempCsv([$headers,$row]);

        //execution
        $dashboard = new Dashboard($mockDb);
        $result = $dashboard->$method($csvFilePath);
        $this->assertFalse($result,"L'importation aurait dû échouer pour $method à cause d'une erreur de base de donnée");
    }

    /**
     * Test de l'erreur d'importation pour les étudiants
     * @return void
     * @throws Exception
     */
    public function testUploadCsvStudentDatabaseError() {
        $headers = [
            'student_number',
            'student_name',
            'student_firstname',
            'formation',
            'class_group'
        ] ;

        $this->runCsvUploadTestDatabaseError('uploadCsvStudent', $headers, [
            '1', //student_number
            'Doe', //student_name
            'John', //student_firstname
            'Informatique', //formation
            'A' //class_group
        ]);
    }
    /**
     * Test de l'erreur d'importation pour les professeurs
     * @return void
     * @throws Exception
     */
    public function testUploadCsvTeacherDatabaseError() {
        $headers = [
            'id_teacher',
            'teacher_name',
            'teacher_firstname',
            'maxi_number_trainees'
        ];

        $this->runCsvUploadTestDatabaseError('uploadCsvTeacher', $headers, [
            '1', //id_teacher
            'Smith', //teacher_name
            'Alice', //teacher_firstname
            '10' //maxi_number_trainees
        ]);
    }
    /**
     * Test de l'erreur d'importation pour les stages/alternances
     * @return void
     * @throws Exception
     */
    public function testUploadCsvInternshipDatabaseError() {
        $headers = [
            'internship_identifier',
            'company_name',
            'keywords',
            'start_date_internship',
            'type',
            'end_date_internship',
            'internship_subject',
            'address',
            'student_number'
        ];

        $this->runCsvUploadTestDatabaseError('uploadCsvInternship', $headers, [
            '123', //internship_identifier
            'CompanyA', //company_name
            'Tech', //keywords
            '2024-01-01', //start_date_internship
            'Full-time', //type
            '2024-06-01', //end_date_internship
            'Software Development', //internship_subject
            '123 Street', //address
            '1' //student_number
        ]);
    }

    // --------- Test exportation --------- //

    /**
     * Test de l'exportation CSV pour student
     * @return void
     * @throws Exception
     */
    public function testExportToCsvStudentSuccess() {
        $headers = ['student_number', 'student_name', 'student_firstname', 'formation', 'class_group'];
        $data = [
            ['1','Doe','John','Informatique','A']
        ];
        $this->runCsvExportTest('student', $headers, $data);
    }

    /**
     * Test de l'exportation CSV pour teacher
     * @return void
     * @throws Exception
     */
    public function testExportToCsvTeacherSuccess() {
        $headers = ['id_teacher', 'teacher_name', 'teacher_firstname', 'maxi_number_trainees'];
        $data = [
            ['1','Smith','Alice','10']
        ];
        $this->runCsvExportTest('teacher', $headers, $data);
    }

    /**
     * Test de l'exportation CSV pour internship
     * @return void
     * @throws Exception
     */
    public function testExportToCsvInternshipSuccess() {
        $headers = ['internship_identifier', 'company_name', 'keywords', 'start_date_internship', 'type', 'end_date_internship', 'internship_subject', 'address', 'student_number'];
        $data = [
            ['123','CompanyA','Tech','2024-01-01','Full-time','2024-06-01','Software Development','123 Street','1']
        ];
        $this->runCsvExportTest('internship', $headers, $data);
    }

    /**
     * Test pour l'exportation de fichier CSV avec succés
     * @param string $table
     * @param array $headers
     * @param array $data
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    private function runCsvExportTest(string $table, array $headers, array $data): void {
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dashboard = new Dashboard($mockDb);

        //simulation de la connexion à la base données
        $mockDb->method('getConn')->willReturn($mockConn);

        //préparation de la requête
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn($data);

        //création d'un fichier temporaire
        $tempCsvPath = $this->tempDir . '/export_' . $table . '.csv';
        try {
            $output = fopen($tempCsvPath, 'w');
            if ($output === false) {
                throw new \Exception("Impossible d'ouvrir le fichier CSV temporaire");
            }
        } catch (\Exception $e) {
            $this->fail("Erreur lors de la création du fichier CSV : " . $e->getMessage());
        }

        //exécution
        $result = $dashboard->exportToCsvByDepartment($table,$headers);
        $this->assertTrue($result,"L'exportation aurait dû réussir pour la table $table");

        //vérification de l'exportation
        $this->assertFileExists($tempCsvPath,"L'exportation aurait dû réussir pour la table $table");

        //vérification du contenu
        $exportedData = file($tempCsvPath,FILE_IGNORE_NEW_LINES);
        $this->assertNotEmpty($exportedData,"Le fichier CSV ne devrait pas être vide.");

        //vérification des en-têtes
        $this->assertEquals(implode(',',$headers),$exportedData[0],"Les en-têtes du CSV ne correspondent pas");

        //vérification des données
        foreach ($data as $index => $row) {
            $this->assertEquals(implode(',',$row),$exportedData[$index + 1], "Les données ne correspondent pas à celle attendues");
        }
        unlink($tempCsvPath);
    }

    /**
     * Test de l'erreur d'exportation avec une table inconnue
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testExportToCsvUnknownTable() {
        //Mock de la base de données
        $mockDb = $this->createMock(Database::class);
        $dashboard = new Dashboard($mockDb);

        //exécution
        $result = $dashboard->exportToCsvByDepartment('unknown_table',[],$this->tempDir . '/file.csv');
        $this->assertFalse($result,"L'exportation aurait dû échouer avec une table inconnue");
    }

    /**
     * Test de l'erreur d'exportation avec une erreur de base de données
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testExportToCsvDatabaseError(){
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        //simulation de l'erreur dans la base de données
        $mockDb->method('getConn')->willReturn($mockConn);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->will($this->throwException(new PDOException("Erreur d'insertion")));

        $csvFilePath = $this->tempDir . '/export_table.csv';
        $dashboard = new Dashboard($mockDb);

        //exécution
        $result = $dashboard->exportToCsvByDepartment('student', ['id', 'name'], $csvFilePath);

        //vérification
        $this->assertFalse($result,"L'exportation aurait dû échouer suite à une erreur de base données.");
    }

}