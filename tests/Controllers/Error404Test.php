<?php

namespace Controllers;

use Blog\Controllers\Error404;
use Blog\Views\Error404 as Error404View;
use Blog\Views\layout\Layout;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de Error404Test
 *
 * Test du contrôleur Error404 : s'assure que la méthode show()
 * fonctionne comme prévu
 */
class Error404Test extends TestCase{
    /**
     * Test de la méthode show() du contrôleur Error404
     * @return void
     * @throws Exception
     */
    public function testShow() {
        //Mocks de la classe Layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(Error404View::class);

        //attentes pour le mock du layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('Erreur 404'),$this->equalTo('/_assets/styles/erreur404.css'));

        $mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        //attentes pour le mock de la vue
        $mockView->expects($this->once())
            ->method('showView');

        //instanciation des mocks
        $error404Controller = new Error404($mockLayout,$mockView);
        $error404Controller->show();
        $this->assertTrue(true);
    }
}