<?php

namespace Controllers;

use Blog\Controllers\Homepage;
use Blog\Views\Layout;
use Blog\Views\Homepage as HomepageView;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de HomepageTest
 *
 * Test du contrôleur Homepage : s'assure que show()
 * fonctionne comme prévu
 */
class HomepageTest extends TestCase{
    /**
     * Test de la méthode show() du contrôleur Homepage
     * @return void
     * @throws Exception
     */
    public function testShow(){
        //Mock des classes layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(HomepageView::class);

        //attentes pour le mock du layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('Accueil'),$this->equalTo('/_assets/styles/homepage.css'));

        $mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo('/_assets/scripts/homepage.js'));

        //attentes pour le mock de la vue
        $mockView->expects($this->once())
            ->method('showView');

        //instanciation des mocks
        $controller = new Homepage($mockLayout, $mockView);

        //simulation de session
        $_SESSION['identifier'] = 'test_user';

        //exécution
        $controller->show();

    }
}