<?php
namespace Blog\Controllers;

use Blog\Views\Layout;

class Homepage {

    /**
     * Controlleur de la homepage
     * @return void
     */
    public function show(): void {

        $title = "Accueil";
        $jsFilePath = '';

        $view = new \Blog\Views\Homepage();

        $layout = new Layout();
        $layout->renderTop($title, $jsFilePath);
        $view->showView();
        $layout->renderBottom();
    }
}