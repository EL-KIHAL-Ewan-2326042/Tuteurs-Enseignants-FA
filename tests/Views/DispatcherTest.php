<?php

namespace Views;

use Blog\Views\Dispatcher;
use Blog\Models\Dispatcher as DispatcherModel;
use Couchbase\View;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de Dispatcher
 *
 * Test de la vue Dispatcher : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class DispatcherTest extends TestCase{

    private $mockDispatcher;
    private $mockView;

    /**
     * Initialise les mocks
     * @return void
     * @throws Exception
     */
    protected function setUp(): void {
        $this->mockDispatcher = $this->createMock(DispatcherModel::class);
        $this->mockDispatcher
            ->method('getCriteria')
            ->willReturn([
                ['name_criteria' => 'Pédagogie', 'coef' => 3],
                ['name_criteria' => 'Communication', 'coef' => 4],
            ]);
        $this->mockView = new Dispatcher($this->mockDispatcher);
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * @return void
     */
    public function testShowView() {
        //obtention du contenu généré
        ob_start();
        $this->mockView->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<span>Pédagogie</span>', $output);
        $this->assertStringContainsString('<span>Communication</span>', $output);
        $this->assertStringContainsString('<input type="range" id="Pédagogie" min="0" max="5" value="3"', $output);
        $this->assertStringContainsString('<input type="range" id="Communication" min="0" max="5" value="4"', $output);
    }

}