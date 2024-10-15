<?php

namespace Test\Controllers;

use Blog\Controllers\AboutUs;
use Blog\Views\Layout;
use Blog\Views\AboutUs as AboutUsView;
use PHPUnit\Framework\TestCase;

/**
 * Classe de AboutUsTest
 *
 * Test du contrôleur AboutUs : s'assure que la méthode show()
 * fonctionne comme prévu
 */
class AboutUsTest extends TestCase {
    /**
     * Test de la méthode show() du contrôleur AboutUs)
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
        $controller = new AboutUs($mockLayout,$mockView);

        //exécution
        $controller->show();
    }
}
