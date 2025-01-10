<?php

namespace Models;

use Includes\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Blog\Models\Teacher;

class TeacherTest extends TestCase
{
    private $mockDb;
    private $mockPdo;
    private $mockStmt;
    private $teacher;

    protected function setUp(): void
    {
        // Mock de la connexion PDO
        $this->mockPdo = $this->createMock(PDO::class);

        // Mock de la classe Database
        $this->mockDb = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDb->method('getConn')->willReturn($this->mockPdo);

        // Mock du statement PDO
        $this->mockStmt = $this->createMock(PDOStatement::class);

        // Instance de la classe Teacher
        $this->teacher = new Teacher($this->mockDb);
    }

    public function testGetFullNameReturnsData(): void
    {
        $mockResult = ['teacher_name' => 'Smith', 'teacher_firstname' => 'John'];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT teacher_name'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':id_teacher', $this->anything());

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->teacher->getFullName('1');
        $this->assertIsArray($result);
        $this->assertEquals('Smith', $result['teacher_name']);
        $this->assertEquals('John', $result['teacher_firstname']);
    }

    public function testGetFullNameReturnsNullForEmptyIdentifier(): void
    {
        $result = $this->teacher->getFullName('');
        $this->assertNull($result);
    }

    public function testGetAddressReturnsData(): void
    {
        $mockResult = [['address' => '123 Main St']];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT address'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':id_teacher', $this->anything());

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResult);

        $result = $this->teacher->getAddress('1');
        $this->assertIsArray($result);
        $this->assertEquals('123 Main St', $result[0]['address']);
    }

    public function testCreateListTeacherReturnsData(): void
    {
        $_SESSION['role_department'] = ['CS', 'Math'];
        $mockResult = ['1', '2'];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT Teacher.Id_teacher'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['CS', 'Math']);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($mockResult);

        $result = $this->teacher->createListTeacher();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(['1', '2'], $result);
    }

    public function testCorrespondTermsTeacherReturnsData(): void
    {
        $_POST['search'] = 'Smith';
        $mockResult = [
            ['id_teacher' => '1', 'teacher_name' => 'Smith', 'teacher_firstname' => 'John']
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT id_teacher'))
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

        $result = $this->teacher->correspondTermsTeacher();
        $this->assertIsArray($result);
        $this->assertEquals('Smith', $result[0]['teacher_name']);
    }

    public function testGetMaxNumberInternsReturnsData(): void
    {
        $mockResult = '5';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT maxi_number_trainees'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':teacher', $this->anything());

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($mockResult);

        $result = $this->teacher->getMaxNumberInterns('1');
        $this->assertIsString($result);
        $this->assertEquals('5', $result);
    }
    public function testGetDisciplinesReturnsData(): void
    {
        $mockResult = ['Math', 'Science'];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT discipline_name'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':id', $this->anything());

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($mockResult);

        $result = $this->teacher->getDisciplines('1');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(['Math', 'Science'], $result);
    }
}