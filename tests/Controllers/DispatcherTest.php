<?php
/**
 * Fichier contenant les test PHPUnit du controlleur Dispatcher
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

namespace Controllers;

use Blog\Controllers\Dispatcher;
use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Student;
use Blog\Models\Teacher;
use Blog\Models\User;
use Blog\Views\dispatcher\Dispatcher as DispatcherView;
use Blog\Views\layout\Layout;
use Includes\Database;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du controlleur Dashboard
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
class DispatcherTest extends TestCase
{
    private $_layoutMock;
    private $_dispatcherMock;

    /**
     * Permet d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_layoutMock = $this->createMock(Layout::class);
        $this->_dispatcherMock = new Dispatcher();
    }

    /**
     * Vérifier que les roles non autorisée sont
     * bien redirigés
     *
     * @return void
     */
    public function testShowRedirectsIfNotAdminDep(): void
    {
        $_SESSION['role_name'] = 'Student';

        $this->expectOutputString('');
        $this->_dispatcherMock->show();
    }
}
