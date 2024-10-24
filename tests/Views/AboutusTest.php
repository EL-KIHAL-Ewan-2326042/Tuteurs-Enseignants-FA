<?php

namespace Views;

use Blog\Views\Aboutus;
use PHPUnit\Framework\TestCase;

/**
 * Classe de AboutusTest
 *
 * Test du la vue Aboutus : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class AboutusTest extends TestCase {

    /**
     * Test de la méthode showView() de la vue Aboutus
     * @return void
     */
    public function testShowView(): void {
        //instance de la classe
        $aboutus = new Aboutus();

        //obtention du contenu généré
        ob_start();
        $aboutus->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<main>', $output);
        $this->assertStringContainsString('A Propos', $output);
        $this->assertStringContainsString('Ce site a été développé dans le cadre d\'une Situation d\'Apprentissage', $output);
        $this->assertStringContainsString('Le site propose plusieurs fonctionnalités essentielles, telles que la connexion des tuteurs', $output);
        $this->assertStringContainsString('Notre équipe de développement, composée d\'étudiants en informatique', $output);
        $this->assertStringContainsString('Ce projet s\'inscrit dans notre cursus académique', $output);
        $this->assertStringContainsString('https://imgur.com/AqAvrsS.png', $output);
    }
}