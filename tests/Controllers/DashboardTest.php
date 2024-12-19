<?php

namespace Controllers;

use Blog\Controllers\dashboard;
use Includes\Database;
use Blog\Views\Layout;
use Blog\Views\dashboard as DashboardView;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de DashboardTest
 *
 * Test du contrôleur Dashboard : s'assure que show()
 * fonctionne comme prévu
 */
class DashboardTest extends TestCase {

    /**
     * Test la présence de la database
     * @return void
     */
    public function testDatabaseClassExists(){
        $this->assertTrue(class_exists('Includes\Database'), 'La classe n existe pas');
    }

    /**
     * Test de l'instance de la database
     * @return void
     */
    public function testDatabaseInstance(){
        $db = new Database();
        $this->assertInstanceOf(Database::class, $db);
    }

    /**
     * Test de la méthode show() du contrôleur Dashboard
     * et simule le téléchargement d'un fichier csv
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testShow(){
        //Mock des classes layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(DashboardView::class);

        //attentes pour le mock du layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('Dashboard'), $this->equalTo('_assets/styles/dashboard.css'));

        $mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        //attentes pour le mock de la vue
        $mockView->expects($this->once())
            ->method('showView');

        //instanciation des mocks
        $controller = new dashboard($mockLayout,$mockView);

        //simulation d'une requête POST avec un fichier CSV (student par exemple)
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_FILES['csv_file_student'] = ['tmp_name' => 'path/to/temp/file.csv'];

        //exécution
        $controller->show();
    }


}
