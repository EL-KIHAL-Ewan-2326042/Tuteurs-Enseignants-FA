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
     */
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
    }

    /**
     * Test de la méthode uploadCsv() avec un fichier CSV inexistant
     * @return void
     * @throws Exception
     */
    public function testUploadCsvFileNotFound(){
        //mock de la base de données
        $mockDb = $this->createMock(Database::class);

        //instance du modèle avec le mock de la base de données
        $dashboard = new Dashboard($mockDb);

        //exécution
        $result = $dashboard->uploadCsv('invalide/path/to/file.csv');
        $this->assertFalse($result,"L'importation du  CSV aurait du échouer car csv inexistant");
    }



    /**
     * Test de la méthode uploadCsv() en simulant une erreur de
     * base de données
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
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
    }
}