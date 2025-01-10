<?php

namespace Models;

use Blog\Models\User;
use Includes\Database;
use PDO;
use PDOStatement;
use Exception;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $dbMock;
    private $user;

    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(Database::class);
        $pdoMock = $this->createMock(PDO::class);
        $this->dbMock->method('getConn')->willReturn($pdoMock);

        $this->user = new User($this->dbMock);
    }

    public function testDoLogsExistReturnsTrueForValidCredentials()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(['user_pass' => password_hash('password123', PASSWORD_DEFAULT)]);

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->user->doLogsExist('test_user', 'password123');
        $this->assertTrue($result);
    }

    public function testDoLogsExistReturnsFalseForInvalidCredentials()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(['user_pass' => password_hash('password123', PASSWORD_DEFAULT)]);

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->user->doLogsExist('test_user', 'wrongpassword');
        $this->assertFalse($result);
    }

    public function testGetRolesReturnsRolesArray()
    {
        $_SESSION['identifier'] = 'test_user';
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetchAll')->willReturn(['Admin', 'User']);

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->user->getRoles('test_user');
        $this->assertEquals(['Admin', 'User'], $result);
    }

    public function testGetHighestRoleReturnsCorrectRole()
    {
        $_SESSION['identifier'] = 'test_user';
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetchAll')->willReturn(['Super_admin']);

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->user->getHighestRole('test_user');
        $this->assertEquals('Super_admin', $result);
    }

    public function testSaveCoefficientsReturnsTrueOnSuccess()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $data = [
            ['name_criteria' => 'Criteria1', 'coef' => 2, 'is_checked' => 1],
            ['name_criteria' => 'Criteria2', 'coef' => 3, 'is_checked' => 0]
        ];

        $result = $this->user->saveCoefficients($data, 'test_user', 1);
        $this->assertTrue($result);
    }

    public function testSaveCoefficientsReturnsFalseOnFailure()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willThrowException(new Exception());

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $data = [
            ['name_criteria' => 'Criteria1', 'coef' => 2, 'is_checked' => 1]
        ];

        try {
            $result = $this->user->saveCoefficients($data, 'test_user', 1);
            $this->assertFalse($result);
        } catch (Exception $e) {
            $this->assertEquals(false, $result);
        }
    }

    public function testInsertUserConnectExecutesSuccessfully()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $this->user->insertUserConnect('test_user', 'password123');

        $this->assertTrue(true);
    }

    public function testInsertHasRoleExecutesSuccessfully()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        $this->dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $this->user->insertHasRole('test_user', 'IT Department');

        $this->assertTrue(true);
    }
}