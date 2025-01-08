<?php

namespace Views;

use Blog\Views\layout\Layout;
use PHPUnit\Framework\TestCase;

/**
 * Classe de LayoutTest
 *
 * Test de la vue Layout : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class LayoutTest extends TestCase{
    /**
     * Test de la méthode showView() de la vue Intramu
     * pour le header
     * @return void
     */
    public function testRenderTop() {
        //instance de la classe
        $layout = new Layout();
        $title = "Page Title";
        $cssFilePath = "/_assets/styles/custom.css";

        //obtention du contenu généré
        ob_start();
        $layout->renderTop($title, $cssFilePath);
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<title>' . $title . '</title>', $output);
        $this->assertStringContainsString('<link href="' . $cssFilePath . '" rel="stylesheet">', $output);
        $this->assertStringContainsString('<link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">', $output);
        $this->assertStringContainsString('<nav class="navbar">', $output);
        $this->assertStringContainsString('ACCUEIL</a>', $output);
        $this->assertStringContainsString('A PROPOS</a>', $output);
    }
    public function testRenderBottom() {
        //instance de la classe
        $layout = new Layout();
        $jsFilePath = "/_assets/scripts/custom.js";

        //obtention du contenu généré
        ob_start();
        $layout->renderBottom($jsFilePath);
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<footer class="page-footer">', $output);
        $this->assertStringContainsString('&copy; 2024 TutorMap', $output);
        $this->assertStringContainsString('<script src="' . $jsFilePath . '"></script>', $output);
        $this->assertStringContainsString('<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>', $output);

    }

}