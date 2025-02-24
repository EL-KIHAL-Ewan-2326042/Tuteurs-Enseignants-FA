<?php
/**
 * Fichier contenant les test PHPUnit du modèle Department
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

use Blog\Models\Department;
use includes\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du modèle Department
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
class DepartmentTest extends TestCase
{
    private Database $_mockDb;
    private PDO $_mockPdo;
    private PDOStatement $_mockStmt;
    private Department $_department;

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

        $this->_department = new Department($this->_mockDb);
    }

    /**
     * Test pour vérifier la méthode getInternshipPerDepartment
     * (qu'elle renvoie des données cohérentes)
     *
     * @return void
     */
    public function testGetInternshipsPerDepartmentReturnsData(): void
    {
        $mockResults = [
            [
                'internship_identifier' => 1,
                'company_name' => 'Company A',
                'internship_subject' => 'Subject A',
                'address' => 'Address A',
                'student_number' => '12345',
                'type' => 'Full-time',
                'student_name' => 'Doe',
                'student_firstname' => 'John',
                'formation' => 'Formation A',
                'class_group' => 'Group A'
            ],
        ];

        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT internship_identifier'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':department_name', $this->anything());

        $this->_mockStmt->expects($this->once())
            ->method('execute');

        $this->_mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResults);

        $result = $this->_department->getInternshipsPerDepartment('IT');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Company A', $result[0]['company_name']);
    }

    /**
     * Test pour vérifier la méthode getInternshipPerDepartment
     * (qu'elle renvoie false)
     *
     * @return void
     */
    public function testGetInternshipsPerDepartmentReturnsFalse(): void
    {
        $this->_mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT internship_identifier'))
            ->willReturn($this->_mockStmt);

        $this->_mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':department_name', $this->anything());

        $this->_mockStmt->expects($this->once())
            ->method('execute');

        $this->_mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $result = $this->_department->getInternshipsPerDepartment('NonExistentDept');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
