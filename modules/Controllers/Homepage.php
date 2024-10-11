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
        $cssFilePath = '/_assets/styles/homepage.css';
        $jsFilePath = '/_assets/scripts/homepage.js';

        $view = new \Blog\Views\Homepage();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}