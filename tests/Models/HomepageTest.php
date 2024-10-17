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
        $stmt = $this->createMock(\PDOStatement::class);
        $this->mockConn->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('bindValue')->with(':searchTerm',"$search");
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($expectedResult);

        //instance du modèle
        $homepage = new Homepage($this->mockDb);

        //simulation des entrées POST
        $_POST['search'] = $search;
        $_POST['searchType'] = 'numeroEtudiant';

        //exécution
        $results = $homepage->correspondTerms();

        //assertion
        $this->assertEquals($expectedResult, $results);

        //nettoyage
        $_POST = [];
    }

    /**
     * Test de la méthode correspondTerms() avec un nom
     * et un prénom
     * @return void
     * @throws Exception
     */
    public function testCorrespondTermsWithNameAndSurname(){
        //données
        $search = "Doe John";
        $expectedResult = [
            ['num_eleve' => '67890', 'nom_eleve' => 'Doe', 'prenom_eleve' => 'John']
        ];

        //configuration du mock
        $stmt = $this->createMock(\PDOStatement::class);
        $this->mockConn->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('bindValue')->with(':searchTerm', "$search"); // Vérification du bon paramètre
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($expectedResult);

        //instance du modèle
        $homepage = new Homepage($this->mockDb);

        //simulation des entrées POST
        $_POST['search'] = $search;
        $_POST['searchType'] = 'nomEtPrenom';

        //exécution
        $results = $homepage->correspondTerms();

        //assertion
        $this->assertEquals($expectedResult, $results);

        //nettoyage
        $_POST = [];
    }

    /**
     * Test de la méthode correspondTerms() avec une entreprise
     * @return void
     * @throws Exception
     */
    public function testCorrespondTermsWithCompany() {
        //données
        $search = "Tech Corp";
        $expectedResult = [
            ['num_eleve' => '12345', 'nom_eleve' => 'Smith', 'prenom_eleve' => 'Jane', 'nom_entreprise' => 'Tech Corp']
        ];

        //configuration du mock
        $stmt = $this->createMock(\PDOStatement::class);
        $this->mockConn->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('bindValue')->with(':searchTerm', "$search%");
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($expectedResult);

        //instance du modèle
        $homepage = new Homepage($this->mockDb);

        //simulation des entrées POST
        $_POST['search'] = $search;
        $_POST['searchType'] = 'company';

        //exécution
        $results = $homepage->correspondTerms();

        //assertion
        $this->assertEquals($expectedResult, $results);

        //nettoyage
        $_POST = [];
    }

    /**
     * Test de la méthode getStudentAddress() avec un ID valide
     * @return void
     * @throws Exception
     */
    public function testGetStudentAddressWithValidId() {
        //données
        $studentId = "12345";
        $expectedAddress = "123 Rue de l'Université, 75001 Paris";

        //configuration du mock
        $stmt = $this->createMock(\PDOStatement::class);
        $this->mockConn->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())->method('bindValue')->with(':student_number', $studentId);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn($expectedAddress);

        //instance du modèle
        $homepage = new Homepage($this->mockDb);

        //simulation des entrées POST
        $_POST['student_id'] = $studentId;

        //exécution
        $address = $homepage->getStudentAddress($studentId);

        //assertion
        $this->assertEquals($expectedAddress, $address);

        //nettoyage
        $_POST = [];
    }

    /**
     * Test de la méthode getStudentAddress() avec un ID invalide
     * @return void
     */
    public function testGetStudentAddressWithInvalidId() {
        //données
        $studentId = "67890";
        $expectedResult = false;

        //instance du modèle
        $homepage = new Homepage($this->mockDb);

        //simulation des entrées POST
        $_POST['student_id'] = "12345";  // ID différent de celui testé

        //exécution
        $address = $homepage->getStudentAddress($studentId);

        //assertion
        $this->assertEquals($expectedResult, $address);

        //nettoyage
        $_POST = [];
    }
}