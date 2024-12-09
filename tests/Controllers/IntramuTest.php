<?php

namespace Controllers;

use Blog\Controllers\Intramu;
use Blog\Views\Layout;
use Blog\Views\Intramu as IntramuView;
use Blog\Models\Intramu as IntramuModel;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de IntramuTest
 *
 * Test du contrôleur Intramu : s'assure que show()
 * fonctionne comme prévu
 */
class IntramuTest extends TestCase{
    /**
     * Test de la méthode show() du contrôleur Intramu
     * @return void
     * @throws Exception
     */
    public function testShow(){
        //initialisation session
        $_SESSION['identifier'] = 'user';

        //Mock des classes layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(IntramuView::class);
        $mockModel = $this->createMock(IntramuModel::class);

        //attentes pour le mock du layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('Connexion'),$this->equalTo('_assets/styles/intramu.css'));

        $mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        //attentes pour le mock de la vue
        $mockView->expects($this->once())
            ->method('showView');

        //instanciation des mocks
        $controller = new Intramu($mockLayout,$mockView,$mockModel);

        //exécution
        $controller->show();

    }

}