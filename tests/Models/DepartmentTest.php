<?php

namespace Models;

use Includes\Database;
use Blog\Models\Department;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class DepartmentTest extends TestCase
{
    private $mockDb;
    private $mockPdo;
    private $mockStmt;
    private $department;

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);

        $this->mockDb = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDb->method('getConn')->willReturn($this->mockPdo);

        $this->mockStmt = $this->createMock(PDOStatement::class);

        $this->department = new Department($this->mockDb);
    }

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

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT internship_identifier'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':department_name', $this->anything());

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockResults);

        $result = $this->department->getInternshipsPerDepartment('IT');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Company A', $result[0]['company_name']);
    }

    public function testGetInternshipsPerDepartmentReturnsFalse(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT internship_identifier'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':department_name', $this->anything());

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $result = $this->department->getInternshipsPerDepartment('NonExistentDept');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
