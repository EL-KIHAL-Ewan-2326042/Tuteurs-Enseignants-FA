<?php
/**
 * Fichier contenant les test PHPUnit du controlleur Dashboard
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

use Blog\Controllers\Dashboard;
use Blog\Models\Model;
use Blog\Views\layout\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Includes\Database;

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
class DashboardTest extends TestCase
{
    private MockObject $_layoutMock;
    private Dashboard $_dashboardController;

    /**
     * Initialisation des objets nécessaires pour les tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_layoutMock = $this->createMock(Layout::class);
        $this->_dashboardController = new Dashboard($this->_layoutMock);
    }

    /**
     * Teste que la méthode show affiche correctement le layout
     *
     * @return void
     */
    public function testShowMethodRendersCorrectLayout(): void
    {
        $_SESSION['role_name'] = 'Admin_dep';

        $this->_layoutMock->expects($this->once())
            ->method('renderTop')
            ->with(
                $this->equalTo('Gestion des données'),
                $this->equalTo('_assets/styles/gestionDonnees.css')
            );

        $this->_layoutMock->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo('_assets/scripts/gestionDonnees.js'));

        ob_start();
        $this->_dashboardController->show();
        $output = ob_get_clean();

        // Assertions
        $this->assertStringContainsString(
            'Gestion des données',
            $output,
        );
    }
}
