<?php
/**
 * Fichier contenant les test PHPUnit du controlleur legalNotices
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
use Blog\Controllers\LegalNotices;
use Blog\Views\layout\Layout;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du controlleur legalNotices
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
class LegalNoticesTest extends TestCase
{
    private $_layoutMock;
    private $_legalNoticesController;

    /**
     * Permet d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_layoutMock = $this->createMock(Layout::class);
        $this->_legalNoticesController = new LegalNotices($this->_layoutMock);
    }

    /**
     * On s'assure que le layout est bien affiché
     *
     * @return void
     */
    public function testShowMethodRendersCorrectView(): void
    {
        $this->_layoutMock->expects($this->once())
            ->method('renderTop')
            ->with(
                $this->equalTo('Mentions légales'),
                $this->equalTo('_assets/styles/legalNotices.css')
            );


        $this->_layoutMock->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        $this->_legalNoticesController->show();

        $this->assertTrue(true);

    }

    /**
     * On s'assure que la méthode show affiche le contenu demandé
     *
     * @return void
     */
    public function testTitleAndCssFilePath(): void
    {
        ob_start();
        $this->_legalNoticesController->show();
        $output = ob_get_clean();

        $this->assertStringContainsString(
            'MENTIONS LÉGALES', $output
        );
    }
}