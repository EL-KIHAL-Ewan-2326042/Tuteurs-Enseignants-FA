<?php
namespace Blog\Controllers;

use Blog\Views\Layout;

class Aboutus {

    /**
     * Controlleur de la page a propos
     * @return void
     */
    public function show(): void {

        $title = "A Propos";
        $cssFilePath = '';
        $jsFilePath = '';

        $view = new \Blog\Views\Aboutus();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}