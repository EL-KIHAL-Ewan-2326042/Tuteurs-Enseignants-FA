<?php

namespace tests\Controllers;

use Blog\Controllers\AboutUs;
use Blog\Views\Layout;
use Blog\Views\AboutUs as AboutUsView;
use Couchbase\View;
use PHPUnit\Framework\TestCase;

class AboutUsTest extends TestCase {
    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testShow(){
        //Mock de layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(AboutUs::class);

        //layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('A Propos'), $this->equalTo(''));

        $mockView->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        //vue
        $mockView->expects($this->once())
            ->method('showView');

        //mocks dans controleur
        $controller = new AboutUs();

        //executer methode
        $controller->show();
    }
}
