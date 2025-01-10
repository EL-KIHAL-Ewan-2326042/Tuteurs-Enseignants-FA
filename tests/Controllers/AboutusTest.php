<?php
/**
 * Fichier contenant les test PHPUnit du controlleur AboutUs
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

use Blog\Controllers\Aboutus;
use Blog\Views\layout\Layout;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du controlleur AboutUs
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
class AboutusTest extends TestCase
{
    private $_layoutMock;
    private $_aboutusController;

    /**
     * Permet de d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_layoutMock = $this->createMock(Layout::class);

        $this->_aboutusController = new Aboutus($this->_layoutMock);
    }

    /**
     * Vérifier que la méthode show appelle bien le layout
     *
     * @return void
     */
    public function testShowCallsLayout()
    {
        $this->_layoutMock->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('A Propos'), $this->equalTo(''));

        $this->_layoutMock->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        $this->_aboutusController->show();

        $this->assertTrue(true);
    }
}
