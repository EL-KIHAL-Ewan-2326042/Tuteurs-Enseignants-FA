<?php

namespace Controllers;

use Blog\Controllers\Mentionslegales;
use Blog\Views\Layout;
use Blog\Views\Mentionslegales as MentionLegView;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de MentionslegalesTest
 *
 * Test du contrôler Mentionslegales : s'assure que show()
 * fonctionne comme prévu
 */
class MentionslegalesTest extends TestCase {
    /**
     * Test de la méthode show() du contrôleur Mentionslegales
     * @return void
     * @throws Exception
     */
    public function testShow(){
        //Mock des classes layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(MentionLegView::class);

        //attente pour le mock du layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('Mentions légales'),$this->equalTo('_assets/styles/mentionLeg.css'));

        $mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        //attente pour le mock de la vue
        $mockView->expects($this->once())
            ->method('showView');

        //instanciation des mocks
        $controller = new Mentionslegales($mockLayout,$mockView);

        //exécution
        $controller->show();
    }
}