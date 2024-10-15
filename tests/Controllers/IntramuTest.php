<?php

namespace Controllers;

use Blog\Controllers\Intramu;
use Includes\Database;
use Blog\Views\Layout;
use Blog\Views\Intramu as IntramuView;
use PHPUnit\Framework\TestCase;

/**
 * Classe de IntramuTest
 *
 * Test du contrôleur Intramu : s'assure que show()
 * fonctionne comme prévu
 */
class IntramuTest extends TestCase{
    public function testShow(){
        //Mock des classes layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(IntramuView::class);

        //mockLayout
    }

}