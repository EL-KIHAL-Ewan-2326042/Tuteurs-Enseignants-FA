<?php
/**
 * Fichier contenant les test PHPUnit du controlleur Intramu
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

use Blog\Controllers\Intramu;
use Blog\Views\layout\Layout;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du controlleur Intramu
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
class IntramuTest extends TestCase
{
    private $_layoutMock;
    private $_intramuController;

    /**
     * Permet d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_layoutMock = $this->createMock(Layout::class);
        $this->_intramuController = new Intramu($this->_layoutMock);
    }

    /**
     * On s'assure que le layout est bien affiché
     * lorsque l'utilisateur n'est pas connecté
     *
     * @return void
     */
    public function testShowMethodRendersCorrectViewWhenNotLoggedIn(): void
    {
        $this->_layoutMock->expects($this->once())
            ->method('renderTop')
            ->with(
                $this->equalTo('Connexion'),
                $this->equalTo('_assets/styles/intramu.css')
            );

        $this->_layoutMock->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        $this->_intramuController->show();

        $this->assertTrue(true);
    }
}
