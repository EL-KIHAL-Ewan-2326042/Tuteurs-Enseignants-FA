<?php
/**
 * Fichier contenant les test PHPUnit du modèle User
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

use Blog\Models\User;
use Includes\Database;
use PDO;
use PDOStatement;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du modèle User
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
class UserTest extends TestCase
{
    private Database $_dbMock;
    private User $_user;

    /**
     * Permet de d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception|\PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->_dbMock = $this->createMock(Database::class);
        $pdoMock = $this->createMock(PDO::class);
        $this->_dbMock->method('getConn')->willReturn($pdoMock);

        $this->_user = new User($this->_dbMock);
    }

    /**
     * Vérifier que la méthode doLogsExist renvoie true
     * si les identifiant/mdp sont correcte
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDoLogsExistReturnsTrueForValidCredentials()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')
            ->willReturn(
                ['user_pass' => password_hash(
                    'password123', PASSWORD_DEFAULT
                )]
            );

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->_user->doLogsExist('test_user', 'password123');
        $this->assertTrue($result);
    }

    /**
     * Vérifier que la méthode doLogsExist renvoie false
     * * si les identifiant/mdp sont incorrecte
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testDoLogsExistReturnsFalseForInvalidCredentials()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')
            ->willReturn(
                ['user_pass' => password_hash('password123', PASSWORD_DEFAULT)]
            );

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->_user->doLogsExist('test_user', 'wrongpassword');
        $this->assertFalse($result);
    }

    /**
     * Vérifier que la méthode getRolesReturnsRole
     * renvoie bien un array de roles
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testGetRolesReturnsRolesArray()
    {
        $_SESSION['identifier'] = 'test_user';
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetchAll')->willReturn(['Admin', 'User']);

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->_user->getRoles('test_user');
        $this->assertEquals(['Admin', 'User'], $result);
    }

    /**
     * Vérifier que la méthode getHightestRole
     * renvoie bien le bon rôle (le plus haut)
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testGetHighestRoleReturnsCorrectRole()
    {
        $_SESSION['identifier'] = 'test_user';
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetchAll')->willReturn(['Super_admin']);

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $result = $this->_user->getHighestRole('test_user');
        $this->assertEquals('Super_admin', $result);
    }

    /**
     * Vérifier que la méthode saveCoefficient renvoie
     * true si succès
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSaveCoefficientsReturnsTrueOnSuccess()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $data = [
            ['name_criteria' => 'Criteria1', 'coef' => 2, 'is_checked' => 1],
            ['name_criteria' => 'Criteria2', 'coef' => 3, 'is_checked' => 0]
        ];

        $result = $this->_user->saveCoefficients($data, 'test_user', 1);
        $this->assertTrue($result);
    }

    /**
     * Vérifier que la méthode saveCoefficients
     * renvoie false si pas succès
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSaveCoefficientsReturnsFalseOnFailure()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willThrowException(new Exception());

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $data = [
            ['name_criteria' => 'Criteria1', 'coef' => 2, 'is_checked' => 1]
        ];

        try {
            $result = $this->_user->saveCoefficients($data, 'test_user', 1);
            $this->assertFalse($result);
        } catch (Exception $e) {
            $this->assertEquals(false, $result);
        }
    }

    /**
     * Vérifier que la méthode inserUserConnect
     * s'éxècute encombres
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInsertUserConnectExecutesSuccessfully()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $this->_user->insertUserConnect('test_user', 'password123');

        $this->assertTrue(true);
    }

    /**
     * Vérifier que la méthode insertHasRolle
     * s'éxècute correctement
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInsertHasRoleExecutesSuccessfully()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        $this->_dbMock->getConn()->method('prepare')->willReturn($stmtMock);

        $this->_user->insertHasRole('test_user', 'IT Department');

        $this->assertTrue(true);
    }
}