<?php
/**
 * Fichier contenant les test PHPUnit du modèle Teacher
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

use Includes\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Blog\Models\Teacher;

/**
 * Classe gérant les tests PHPUnit du modèle Teacher
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
class TeacherTest extends TestCase
{
    private $_mockDb;
    private $_mockPdo;
    private $_mockStmt;
    private $_teacher;

    /**
     * Permet de d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_mockPdo = $this->createMock(PDO::class);

        $this->_mockDb = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockDb->method('getConn')->willReturn($this->_mockPdo);

        $this->_mockStmt = $this->createMock(PDOStatement::class);

        $this->_teacher = new Teacher($this->_mockDb);
    }

    /**
     * Vérifier que la méthode getFullName renvoie
     * bien le nom complet
     *
     * @return void
     */
    public function testGetFullNameReturnsData(): void
    {
        $mockResult = ['teacher_name' => 'Smith', 'teacher_firstname' => 'John'];

        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT teacher_name'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':id_teacher', $this->anything());

        $this->_mockStmt->expects($this->once())
            ->method('execute');

        $this->_mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->_teacher->getFullName('1');
        $this->assertIsArray($result);
        $this->assertEquals('Smith', $result['teacher_name']);
        $this->assertEquals('John', $result['teacher_firstname']);
    }

    /**
     * Vérifier que la méthode getFullName renvoie null
     * si un identifiant null est envoyé
     *
     * @return void
     */
    public function testGetFullNameReturnsNullForEmptyIdentifier(): void
    {
        $result = $this->_teacher->getFullName('');
        $this->assertNull($result);
    }

    /**
     * Vérifier que la méthode getAddress renvoie bien
     * l'addresse du professeur
     *
     * @return void
     */
    public function testGetAddressReturnsData(): void
    {
        $mockResult = [['address' => '123 Main St']];

        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT address'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':id_teacher', $this->anything());

        $this->_mockStmt->expects($this->once())
            ->method('execute');

        $this->_mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->_teacher->getAddress('1');
        $this->assertIsArray($result);
        $this->assertEquals('123 Main St', $result[0]['address']);
    }

    /**
     * Vérifier que la méthode createListTeacher
     * renvoie des données
     *
     * @return void
     */
    public function testCreateListTeacherReturnsData(): void
    {
        $_SESSION['role_department'] = ['CS', 'Math'];
        $mockResult = ['1', '2'];

        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT Teacher.Id_teacher'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('execute')
            ->with(['CS', 'Math']);

        $this->_mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($mockResult);

        $result = $this->_teacher->createListTeacher();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(['1', '2'], $result);
    }

    /**
     * Vérifier que la méthode correspondTermsTeacher
     * renvoie bien des données
     *
     * @return void
     */
    public function testCorrespondTermsTeacherReturnsData(): void
    {
        $_POST['search'] = 'Smith';
        $mockResult = [
            ['id_teacher' => '1', 'teacher_name' => 'Smith',
            'teacher_firstname' => 'John']
        ];

        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT id_teacher'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('bindValue')
            ->with(':searchTerm', 'Smith%');

        $this->_mockStmt->expects($this->once())
            ->method('execute');

        $this->_mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->_teacher->correspondTermsTeacher();
        $this->assertIsArray($result);
        $this->assertEquals('Smith', $result[0]['teacher_name']);
    }

    /**
     * Vérifier que la méthode getMaxNumberInternship
     * renvoie bien des bonnes données
     *
     * @return void
     */
    public function testGetMaxNumberInternsReturnsData(): void
    {
        $mockResult = '5';

        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT maxi_number_trainees'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':teacher', $this->anything());

        $this->_mockStmt->expects($this->once())
            ->method('execute');

        $this->_mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($mockResult);

        $result = $this->_teacher->getMaxNumberInterns('1');
        $this->assertIsString($result);
        $this->assertEquals('5', $result);
    }

    /**
     * Vérifier que la methode getDisciplines renvoie
     * bien des données
     *
     * @return void
     */
    public function testGetDisciplinesReturnsData(): void
    {
        $mockResult = ['Math', 'Science'];

        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT discipline_name'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':id', $this->anything());

        $this->_mockStmt->expects($this->once())
            ->method('execute');

        $this->_mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($mockResult);

        $result = $this->_teacher->getDisciplines('1');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(['Math', 'Science'], $result);
    }
}