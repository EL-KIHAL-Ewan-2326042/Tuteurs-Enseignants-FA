<?php

namespace Views;

use Blog\Views\mentionslegales\Mentionslegales;
use PHPUnit\Framework\TestCase;

/**
 * Classe de MentionslegalesTest
 *
 * Test de la vue MentionslegalesTest : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class MentionslegalesTest extends TestCase {
    /**
     * Test de la méthode showView() de la vue Mentionslegales
     * @return void
     */
    public function testShowView() {
        //instance de la classe
        $mentionsLegales = new Mentionslegales();

        //obtention du contenu généré
        ob_start();
        $mentionsLegales->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('MENTIONS LÉGALES', $output);
        $this->assertStringContainsString('ÉDITEUR', $output);
        $this->assertStringContainsString('Directeur de la publication : Mickaël Martin-Nevot.', $output);
        $this->assertStringContainsString('RAISON SOCIALE ET DÉNOMINATION', $output);
        $this->assertStringContainsString('CONCEPTION ET REALISATION DU SITE', $output);
        $this->assertStringContainsString('HEBERGEMENT', $output);
        $this->assertStringContainsString('Alwaysdata', $output);
        $this->assertStringContainsString('PROPRIÉTÉ INTELLECTUELLE', $output);
        $this->assertStringContainsString('DONNÉES PERSONNELLES', $output);
        $this->assertStringContainsString('PROCEDURE DE NOTIFICATION', $output);
        $this->assertStringContainsString('MODIFICATION DE LA NOTICE LÉGALE', $output);
    }
}