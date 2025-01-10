<?php

namespace Models;

use Blog\Models\Student;
use Includes\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{
    private $mockDb;
    private $mockPdo;
    private $mockStmt;
    private $student;

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

        // Instance de la classe Student
        $this->student = new Student($this->mockDb);
    }

    public function testCorrespondTermsStudentReturnsData(): void
    {
        $_POST['search'] = 'Smith';
        $mockResult = [
            ['student_number' => '1', 'student_name' => 'Smith', 'student_firstname' => 'John', 'company_name' => 'TechCorp', 'internship_identifier' => 'A123']
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

    public function testCorrespondTermsReturnsDataForStudentNumber(): void
    {
        $_POST['search'] = '123';
        $_POST['searchType'] = 'studentNumber';
        $mockResult = [
            ['student_number' => '123', 'student_name' => 'Doe', 'student_firstname' => 'Jane']
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

    public function testCorrespondTermsReturnsDataForName(): void
    {
        $_POST['search'] = 'John Doe';
        $_POST['searchType'] = 'name';
        $mockResult = [
            ['student_number' => '1', 'student_name' => 'Doe', 'student_firstname' => 'John']
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
