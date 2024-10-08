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
        $jsFilePath = '_assets/scripts/layout.js';
        $cssFilePath = '_assets/styles/layout.css';

        $view = new \Blog\Views\Homepage();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath, $jsFilePath);
        $view->showView();
        $layout->renderBottom();
    }
}