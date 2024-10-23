<?php

namespace Models;

use Blog\Models\Dashboard;
use Includes\Database;
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
    /**
     * Test de la méthode uploadCsv() avec un fichier CSV valide
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     *
    public function testUploadCsvSuccess(){
        //mock de la base de données
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->createMock(\PDO::class);

        //attentes pour le mock de la base de données
        $mockDb->method('getConn')->willReturn($mockConn);

        //preparation d'une requête simulée
        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockConn->method('prepare')->willReturn($mockStmt);

        //exécution de cette requête
        $mockStmt->method('execute')->willReturn(true);

        //instance du modèle avec le mock de la base de données
        $dashboard = new Dashboard($mockDb);

        //fichier temporaire
        $tempDir = 'path/to/temp';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $csvFilePath = $tempDir . '/file.csv';

        $tempFile = fopen($csvFilePath, 'w');
        if ($tempFile === false) {
            $this->fail("Impossible d'ouvrir le fichier : $csvFilePath");
            return;
        }
        fputcsv($tempFile, ['1', 'Doe', 'John', 'Informatique', 'A']);
        fclose($tempFile);

        try {
            //exécution
            $result = $dashboard->uploadCsv($csvFilePath);
            $this->assertTrue($result,"L'importation aurait dû réussir");
        } finally {
            //nettoyage
            unlink($csvFilePath);
        }
    }*/

    /**
     * Test de la méthode uploadCsv() avec un fichier CSV inexistant
     * @return void
     * @throws Exception
     *
    public function testUploadCsvFileNotFound(){
        //mock de la base de données
        $mockDb = $this->createMock(Database::class);

        //instance du modèle avec le mock de la base de données
        $dashboard = new Dashboard($mockDb);

        //exécution
        $result = $dashboard->uploadCsv('invalide/path/to/file.csv');
        $this->assertFalse($result,"L'importation du  CSV aurait du échouer car csv inexistant");
    }
*/


    /**
     * Test de la méthode uploadCsv() en simulant une erreur de
     * base de données
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     *
    public function testUploadCsvSDatabaseError(){
        //mock de la base de données
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->createMock(\PDO::class);

        //attentes pour le mock de la base de données
        $mockDb->method('getConn')->willReturn($mockConn);

        //preparation d'une requête simulée
        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockConn->method('prepare')->willReturn($mockStmt);

        //exécution de cette requête
        $mockStmt->method('execute')
            ->will($this->throwException(new \PDOException("Erreur d'insertion")));

        //instance du modèle avec le mock de la base de données
        $dashboard = new Dashboard($mockDb);

        //fichier temporaire
        $tempDir = 'path/to/temp';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $csvFilePath = $tempDir . '/file.csv';

        $tempFile = fopen($csvFilePath, 'w');
        if ($tempFile === false) {
            $this->fail("Impossible d'ouvrir le fichier : $csvFilePath");
            return;
        }
        fputcsv($tempFile, ['1', 'Doe', 'John', 'Informatique', 'A']);
        fclose($tempFile);

        try {
            //exécution
            $result = $dashboard->uploadCsv($csvFilePath);
            $this->assertFalse($result,"L'importation aurait dû échouer car csv invalide");
        } finally {
            //nettoyage
            unlink($csvFilePath);
        }
    }*/

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
     * Test d'importation pour les étudiants
     * @return void
     * @throws Exception
     */
    public function testUploadCSvStudentSuccess(): void {
        $this->runCsvUploadTest('uploadCsvStudent', ['1', 'Doe', 'John', 'Informatique', 'A']);
    }

    /**
     * Test d'importation pour les enseignants
     * @return void
     * @throws Exception
     */
    public function testUploadCsvTeacherSuccess() {
        $this->runCsvUploadTest('uploadCsvTeacher', ['1', 'Smith', 'Alice', '5']);
    }

    /**
     * Test d'importation pour les stages/alternances
     * @return void
     * @throws Exception
     */
    public function testUploadCsvInternshipSuccess() {
        $this->runCsvUploadTest('uploadCsvInternship', ['123', 'CompanyName', 'Tech', '2024-01-01', '2024-06-01']);
    }

    /**
     * Test pour l'importation de fichier CSV avec succés
     * @param string $method
     * @param array $row
     * @return void
     * @throws Exception
     */
    private function runCsvUploadTest(string $method, array $row): void {
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->createMock(Database::class);

        //attente pour le mock de la base de données
        $mockDb->method('getConn')->willReturn($mockConn);

        //simulation de la requête
        $mockStmt = $this->createMock(\PDOStatement::class);
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
     * @param array $row
     * @return void
     * @throws Exception
     */
    private function runCsvUploadTestDatabaseError (string $method, array $row): void {
        $mockDb = $this->createMock(Database::class);
        $mockConn = $this->createMock(Database::class);

        //simulation de l'erreur
        $mockDb->method('getConn')->willReturn($mockConn);
        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockConn->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->will($this->throwException(new \PDOException("Erreur d'insertion")));

        //creation d'un fichier CSV temporaire
        $csvFilePath = $this->createTempCsv([$row]);

        //execution
        $dashboard = new Dashboard($mockDb);
        $result = $dashboard->$method($csvFilePath);
        $this->assertFalse($result,"L'importation aurait dû échouer pour $method à cause d'une erreur de base de donnée");
    }

    /**
     * Test de l'erreur
     * @return void
     * @throws Exception
     */
    public function testUploadCsvStudentDatabaseError() {
        $this->runCsvUploadTestDatabaseError('uploadCsvStudent', ['1', 'Doe', 'John', 'Informatique', 'A']);
    }

    public function testUploadCsvTeacherDatabaseError() {
        $this->runCsvUploadTestDatabaseError('uploadCsvTeacher', ['1', 'Smith', 'Alice', '5']);
    }

    public function testUploadCsvInternshipDatabaseError() {
        $this->runCsvUploadTestDatabaseError('uploadCsvInternship', ['123', 'CompanyName', 'Tech', '2024-01-01', '2024-06-01']);
    }


}