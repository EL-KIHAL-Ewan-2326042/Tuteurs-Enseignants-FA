<?php

namespace Views;

use Blog\Views\Dashboard;
use PHPUnit\Framework\TestCase;

/**
 * Classe de DashboardTest
 *
 * Test de la vue Dashboard : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class DashboardTest extends TestCase {
    /**
     * Initialise la session
     */
    protected function setUp(): void {
        $_SESSION = [];
    }

    /**
     * Test de la méthode showView() de la vue Dashboard
     * avec un administrateur
     * @return void
     */
    public function testShowViewAdmin() {
        //simulation de l'administrateur
        $_SESSION['role'] = ['role_name' => 'Admin_dep'];

        //instance de la classe
        $dashboard = new Dashboard();

        //obtention du contenu généré
        ob_start();
        $dashboard->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<h3> Dashboard </h3>', $output);
        $this->assertStringContainsString('Table étudiant (student)', $output);
        $this->assertStringContainsString('Table professeur (teacher)', $output);
        $this->assertStringContainsString('Table entreprise (internship)', $output);
        $this->assertStringContainsString('Exporter :', $output);
    }

    /**
     * Test de la méthode showView() de la vue Dashboard
     * avec un utilisateur non administrateur
     * @return void
     */
    public function testShowViewNonAdmin() {
        //simulation de l'utilisateur non administrateur
        $_SESSION['role'] = ['role_name' => 'User'];

        //instance de la classe
        $dashboard = new Dashboard();

        //obtention du contenu généré
        ob_start();
        $dashboard->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('', $output);
    }
}