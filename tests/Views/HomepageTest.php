<?php

namespace Views;

use Blog\Views\Homepage;
use PHPUnit\Framework\TestCase;

/**
 * Classe de HomepageTest
 *
 * Test de la vue Homepage : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class HomepageTest extends TestCase{
    private Homepage $view;

    /**
     * Initialise la vue Homepage
     */
    protected function setUp(): void{
        $this->view = new Homepage();
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * sans étudiant sélectionné
     * @return void
     */
    public function testShowViewNoStudentSelected() {
        //simulation d'une session avec aucun étudiant sélectionné
        $_SESSION = [];

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringNotContainsString('<h4 class="left-align"> Résultat pour: ', $output);
        $this->assertStringContainsString('Cet étudiant n\'a pas de stage ...', $output);
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * avec un étudiant sans adresse
     * @return void
     */
    public function testShowViewStudentSelectedNoAddress(){
        //simulation d'une session avec un étudiant sans adresse
        $_SESSION['selected_student'] = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => ''
        ];

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<h4 class="left-align"> Résultat pour: John Doe</h4>', $output);
        $this->assertStringContainsString("Cet étudiant n'a pas de stage ...", $output);
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * avec un étudiant ayant une adresse
     * @return void
     */
    public function testShowViewStudentSelectedAddress(){
        //simulation d'une session avec un étudiant ayant une adrresse
        $_SESSION['selected_student'] = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => '123 Main St, Marseille'
        ];

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<h4 class="left-align"> Résultat pour: John Doe</h4>', $output);
        $this->assertStringContainsString('<div id="map"></div>', $output);
    }
}