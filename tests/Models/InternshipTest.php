<?php
/**
 * Fichier contenant les test PHPUnit du modèle Internship
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

use Blog\Models\Internship;
use Includes\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du modèle Internship
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
class InternshipTest extends TestCase
{
    private Database $_dbMock;
    private Internship $_internship;

    /**
     * Permet de d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_dbmock = $this->createMock(Database::class);
        $this->_internship = new Internship($this->_dbmock);
    }

    /**
     * Test que la méthode getInternship renvoie bien les bonnes données
     *
     * @return void
     * @throws Exception
     */
    public function testGetInternshipsReturnsCorrectData(): void
    {
        $studentNumber = '12345';
        $expectedData = [
            [
                'id_teacher' => 'T001',
                'student_number' => '12345',
                'Start_date_internship' => '2023-01-01',
                'End_date_internship' => '2023-06-01',
            ],
        ];

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute');
        $pdoStatementMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT id_teacher'))
            ->willReturn($pdoStatementMock);

        $this->_dbmock->expects($this->once())
            ->method('getConn')
            ->willReturn($pdoMock);

        $result = $this->_internship->getInternships($studentNumber);
        $this->assertSame($expectedData, $result);
    }

    /**
     * Tester que la methode getInternshipTeacherCount renvoie bien le bon résultat
     *
     * @return void
     */
    public function testGetInternshipTeacherCountsCorrectly(): void
    {
        $internshipData = [
            ['id_teacher' => 'T001', 'start_date_internship' => '2023-01-01'],
            ['id_teacher' => 'T002', 'start_date_internship' => '2023-06-01'],
            ['id_teacher' => 'T001', 'start_date_internship' => '2024-01-01'],
        ];
        $teacherId = 'T001';
        $year = '';

        $result = $this->_internship->getInternshipTeacher(
            $internshipData, $teacherId, $year
        );

        $this->assertSame(2, $result);
        $this->assertSame('2023', $year);
    }

    /**
     * Tester que la methode getCountInternsPerTypeCounts renvoie le bon résultat
     *
     * @return void
     */
    public function testGetCountInternsPerTypeCountsCorrectly(): void
    {
        $interns = [
            ['type' => 'internship'],
            ['type' => 'alternance'],
            ['type' => 'internship'],
            ['type' => 'alternance'],
            ['type' => 'internship'],
        ];

        $internshipCount = 0;
        $alternanceCount = 0;

        $this->_internship->getCountInternsPerType(
            $interns, $internshipCount, $alternanceCount
        );

        $this->assertSame(3, $internshipCount);
        $this->assertSame(2, $alternanceCount);
    }

    /**
     * Tester qu'envoyer un tableau vide à la methode getCountInternByType
     * ne la fasse pas planter
     *
     * @return void
     */
    public function testGetCountInternsPerTypeWithEmptyArray(): void
    {
        $interns = [];

        $internshipCount = 0;
        $alternanceCount = 0;

        $this->_internship->getCountInternsPerType(
            $interns, $internshipCount, $alternanceCount
        );

        $this->assertSame(0, $internshipCount);
        $this->assertSame(0, $alternanceCount);
    }

    /**
     * Tester qu'envoyer autre chose que alternance et internship
     * à la methode getCountInternByType ne la fasse pas planter
     *
     * @return void
     */
    public function testGetCountInternsPerTypeWithNoInternshipOrAlternance(): void
    {
        $interns = [
            ['type' => 'full-time'],
            ['type' => 'part-time'],
            ['type' => 'temporary'],
        ];

        $internshipCount = 0;
        $alternanceCount = 0;

        $this->_internship->getCountInternsPerType(
            $interns, $internshipCount, $alternanceCount
        );

        $this->assertSame(0, $internshipCount);
        $this->assertSame(0, $alternanceCount);
    }
}
