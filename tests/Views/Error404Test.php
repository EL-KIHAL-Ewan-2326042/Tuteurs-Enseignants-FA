<?php

namespace Views;

use Blog\Views\erreur404\Error404;
use PHPUnit\Framework\TestCase;

/**
 * Classe de Error404Test
 *
 * Test de la vue Error404 : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class Error404Test extends TestCase {
    public function testError404() {
        //instance de la classe
        $error404 = new Error404();

        //obtention du contenu généré
        ob_start();
        $error404->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('Oups! Erreur 404', $output);
        $this->assertStringContainsString('Désolé, la page que vous recherchez est introuvable.', $output);
        $this->assertStringContainsString('Retour à la page d\'accueil', $output);
        $this->assertStringContainsString('<a href="/" class="btn waves-effect waves-light">', $output);
    }
}