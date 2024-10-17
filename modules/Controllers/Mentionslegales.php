<?php

namespace Blog\Controllers;

use Blog\Views\Layout;

class Mentionslegales {

    /**
     * Controlleur de la page mentions légales
     * @return void
     */
    public function show(): void {
        $title = "Mentions légales";
        $cssFilePath = '_assets/styles/mentionLeg.css';
        $jsFilePath = '';

        $view = new \Blog\Views\Mentionslegales();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}