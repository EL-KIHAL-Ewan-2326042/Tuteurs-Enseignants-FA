<?php

namespace Test\Controllers;

use Blog\Controllers\Aboutus;
use Blog\Views\Layout;
use Blog\Views\Aboutus as AboutUsView;
use PHPUnit\Framework\TestCase;

/**
 * Classe de AboutusTest
 *
 * Test du contrôleur Aboutus : s'assure que la méthode show()
 * fonctionne comme prévu
 */
class AboutusTest extends TestCase {
    /**
     * Test de la méthode show() du contrôleur Aboutus
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testShow(){
        //Mock des classes layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(AboutUsView::class);

        //attentes pour le mock du layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('A Propos'), $this->equalTo(''));

        $mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        //attentes pour le mock de la vue
        $mockView->expects($this->once())
            ->method('showView');

        //instanciation des mocks
        $controller = new Aboutus($mockLayout,$mockView);

        //exécution
        $controller->show();
    }
}
