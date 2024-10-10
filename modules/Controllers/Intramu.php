<?php
namespace Blog\Controllers;

use Blog\Views\Layout;

class Intramu {

    /**
     * Controlleur de la homepage
     * @return void
     */
    public function show(): void {

        $title = "Accueil";
        $cssFilePath = '_assets/styles/intramu.css';
        $jsFilePath = '';

        $view = new \Blog\Views\Intramu();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}