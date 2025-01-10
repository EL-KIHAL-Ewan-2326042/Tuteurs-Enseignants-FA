<?php
/**
 * Fichier contenant les test PHPUnit du modèle Student
 *
 * PHP version 8.3
 *
 * @category Models
 * @package  TutorMap/tests/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
namespace Models;

use Blog\Models\Student;
use Includes\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du modèle Student
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class StudentTest extends TestCase
{
    private Database $_mockDb;

    /**
     * Permet de d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);

        $this->_mockDb = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockDb->method('getConn')->willReturn($this->mockPdo);

        $this->mockStmt = $this->createMock(PDOStatement::class);

        $this->student = new Student($this->_mockDb);
    }

    /**
     * Vérifier que la méthode correspondTerms renvoie
     * des données
     *
     * @return void
     */
    public function testCorrespondTermsStudentReturnsData(): void
    {
        $_POST['search'] = 'Smith';
        $mockResult = [
            ['student_number' => '1', 'student_name' => 'Smith',
            'student_firstname' => 'John', 'company_name' => 'TechCorp',
            'internship_identifier' => 'A123']
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT student.student_number'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':searchTerm', 'Smith%');

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->student->correspondTermsStudent();
        $this->assertIsArray($result);
        $this->assertEquals('Smith', $result[0]['student_name']);
    }

    /**
     * Vérifier que la méthode correspondTerms renvoie
     * des données pour un numéro étudiant
     *
     * @return void
     */
    public function testCorrespondTermsReturnsDataForStudentNumber(): void
    {
        $_POST['search'] = '123';
        $_POST['searchType'] = 'studentNumber';
        $mockResult = [
            ['student_number' => '123', 'student_name' => 'Doe',
            'student_firstname' => 'Jane']
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT student_number'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':searchTerm', '123%');

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->student->correspondTerms();
        $this->assertIsArray($result);
        $this->assertEquals('Doe', $result[0]['student_name']);
    }

    /**
     * Vérifier que la méthode correspondTerms renvoie
     * des données pour un numéro nom étudiant
     *
     * @return void
     */
    public function testCorrespondTermsReturnsDataForName(): void
    {
        $_POST['search'] = 'John Doe';
        $_POST['searchType'] = 'name';
        $mockResult = [
            ['student_number' => '1', 'student_name' => 'Doe',
            'student_firstname' => 'John']
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT student_number'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':searchTerm', '%John Doe%');

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->student->correspondTerms();
        $this->assertIsArray($result);
        $this->assertEquals('John', $result[0]['student_firstname']);
    }

    /**
     * Vérifier que la méthode getDepStudent renvoie
     * des données
     *
     * @return void
     */
    public function testGetDepStudentReturnsData(): void
    {
        $mockResult = [
            ['department_name' => 'Computer Science'],
            ['department_name' => 'Mathematics']
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT department_name'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':student', $this->anything());

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->student->getDepStudent('1');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Computer Science', $result[0]['department_name']);
    }
}
