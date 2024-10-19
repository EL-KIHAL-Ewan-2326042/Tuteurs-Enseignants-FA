<?php

namespace Models;

use Blog\Models\Intramu;
use Includes\Database;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de IntramuTest
 *
 * Test du modèle Intramu : s'assure que doLogsExist(),
 * getRole() et fetchAll() fonctionne comme prévu
 */
class IntramuTest extends TestCase {
    private $dbMock;
    private $intramu;

    /**
     * Prépare les mocks pour les tests
     * @return void
     * @throws Exception
     */
    public function setUp(): void {
        //Mock de la base de données
        $this->dbMock = $this->createMock(Database::class);
        $this->intramu = new Intramu($this->dbMock);
    }

    /**
     * Test de la méthode doLogsExist() avec un
     * bon mot de passe
     * @return void
     * @throws Exception
     */
    public function testDoLogsExistTrueWhenCredentialsCorrect(){
        //Mock du Statement et de la connexion
        $stmt = $this->createMock(\PDOStatement::class);
        $pdo = $this->createMock(\PDO::class);

        //configuration
        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->dbMock->expects($this->once())
            ->method('getConn')
            ->willReturn($pdo);

        $stmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['user_pass' => password_hash('password123',PASSWORD_DEFAULT)]);

        //exécution
        $result = $this->intramu->doLogsExist('username','password123');
        $this->assertTrue($result);

        //nettoyage
        $_SESSION = [];
    }

    /**
     * Test de la méthode doLogsExist() avec un
     * mauvais mot de passe
     * @return void
     * @throws Exception
     */
    public function testDoLogsExistsFalseWhenCredentialsIncorrect(){
        //Mock de Statement et de la connexion
        $stmt = $this->createMock(\PDOStatement::class);
        $pdo = $this->createMock(\PDO::class);

        //configuration
        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->dbMock->expects($this->once())
            ->method('getConn')
            ->willReturn($pdo);

        $stmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['user_pass' => password_hash('password123',PASSWORD_DEFAULT)]);

        //exécution
        $result = $this->intramu->doLogsExist('username','wrongPassword');
        $this->assertFalse($result);

        //nettoyage
        $_SESSION = [];
    }

    /**
     * Test de la méthode getRole() avec une
     * session valide
     * @return void
     * @throws Exception
     */
    public function testGetRoleWhenSessionValid(){
        //simulation de la session
        $_SESSION['identifier'] = 'valid_user';

        //Mock de Statement et de la connexion
        $stmt = $this->createMock(\PDOStatement::class);
        $pdo = $this->createMock(\PDO::class);

        //configuration
        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->dbMock->expects($this->once())
            ->method('getConn')
            ->willReturn($pdo);

        $stmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['role_name'=>'admin']);

        //exécution
        $result = $this->intramu->getRole('valid_user');
        $this->assertEquals(['role_name'=>'admin'], $result);

        //nettoyage
        $_SESSION = [];
    }

    /**
     * Test de la méthode fetchAll() avec une
     * session valide
     * @return void
     * @throws Exception
     */
    public function testFetchAllWhenSessionValid(){
        //simulation de la session
        $_SESSION['identifier'] = 'valid_teacher';

        //Mock de Statement et de la connexion
        $stmt = $this->createMock(\PDOStatement::class);
        $pdo = $this->createMock(\PDO::class);

        //configuration
        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $this->dbMock->expects($this->once())
            ->method('getConn')
            ->willReturn($pdo);

        $stmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['id_teacher'=>'valid_teacher','name'=>'John Doe']);

        //exécution
        $result = $this->intramu->fetchAll('valid_teacher');
        $this->assertEquals(['id_teacher'=>'valid_teacher','name'=>'John Doe'],$result);

        //nettoyage
        $_SESSION = [];
    }
}