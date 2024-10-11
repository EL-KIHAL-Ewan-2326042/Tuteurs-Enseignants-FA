<?php

namespace Blog\Controllers;

use Blog\Views\Layout;

class MentionLeg{

    /**
     * Controlleur de la page mentions légales
     * @return void
     */
    public function show(): void {
        $title = "Mentions légales";
        $cssFilePath = '_assets/styles/mentionLeg.css';
        $jsFilePath = '';

        $view = new \Blog\Views\MentionLeg();

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}