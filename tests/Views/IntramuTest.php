<?php

namespace Views;

use Blog\Views\intramu\Intramu;
use PHPUnit\Framework\TestCase;

/**
 * Classe de IntramuTest
 *
 * Test de la vue Homepage : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class IntramuTest extends TestCase{
    /**
     * Test de la méthode showView() de la vue Intramu
     * avec un message d'erreur
     * @return void
     */
    public function testShowErrorMessage(){
        //Mock de la classe
        $errorMessage = "Informations d'identification invalides";
        $intramu = new Intramu($errorMessage);

        //obtention du contenu généré
        ob_start();
        $intramu->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString($errorMessage, $output);
        $this->assertStringContainsString('Identifiant', $output);
        $this->assertStringContainsString('Mot de Passe', $output);
        $this->assertStringContainsString('Connexion', $output);
    }

    /**
     * Test de la méthode showView() de la vue Intramu
     * sans message d'erreur
     * @return void
     */
    public function testShowNoErrorMessage(){
        //instance de la classe
        $intramu = new Intramu('');

        //obtention du contenu généré
        ob_start();
        $intramu->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringNotContainsString('Informations d\'identification invalides', $output);
        $this->assertStringContainsString('Identifiant', $output);
        $this->assertStringContainsString('Mot de Passe', $output);
        $this->assertStringContainsString('Connexion', $output);

    }
}