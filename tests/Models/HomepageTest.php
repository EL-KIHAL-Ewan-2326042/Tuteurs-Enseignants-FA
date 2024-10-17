<?php

namespace Models;
use Blog\Models\Homepage;
use Includes\Database;
use PDO;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de HomepageTest
 *
 * Test du modèle Homepage : s'assure que correspondTerms()
 * et getStudentAddress() fonctionne comme prévu
 */
class HomepageTest extends TestCase{
    private $mockDb;
    private $mockConn;

    /**
     * Prépare les mocks pour les tests
     * @return void
     * @throws Exception
     */
    public function setUp(): void {
        //Mock de la base de données
        $this->mockDb = $this->createMock(Database::class);
        $this->mockConn = $this->createMock(PDO::class);
        $this->mockDb->method('getConn')->willReturn($this->mockConn);
    }

    /**
     * Test de la méthode correspondTerms() avec un
     * numéro d'étudiant
     * @return void
     * @throws Exception
     */
    public function testCorrespondTermsWithNumberStudent()
    {
        //données
        $search = "12345";
        $expectedResult = [
            'num_eleve' => '12345', 'nom_eleve' => 'Doe', 'prenom_eleve' => 'John'
        ];

        //configuration du mock
        $query = "
            SELECT num_eleve, nom_eleve, prenom_eleve,
            ts_rank_cd(to_tsvector('french', num_eleve), to_tsquery('french', :searchTerm), 32) AS rank
            FROM eleve
            WHERE num_eleve ILIKE :searchTerm
            ORDER BY num_eleve
            LIMIT 5
        ";

        $stmt = $this->createMock(\PDOStatement::class);
        $this->mockConn->method('prepare')->willReturn($stmt);
        $stmt->method('bindValue')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($expectedResult);

        //instance du modèle
        $homepage = new Homepage($this->mockDb);

        //simulationdes entrées POST
        $_POST['search'] = $search;
        $_POST['searchType'] = 'numeroEtudiant';

        //exécution
        $results = $homepage->correspondTerms();

        //assertion
        $this->assertEquals($expectedResult, $results);
    }
}