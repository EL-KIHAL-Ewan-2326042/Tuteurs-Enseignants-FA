<?php

namespace Blog\Controllers;

use Blog\Views\layout\Layout;

/**
 * Contrôleur de la page mentions légales
 */
class Mentionslegales{
    private Layout $layout;

    /**
     * Constructeur de la classe Mentionslegales (contrôleur)
     * @param Layout $layout Instance de la classe Layout
     */
    public function __construct(Layout $layout){
        $this->layout = $layout;
    }

    /**
     * Controlleur de la page mentions légales
     * @return void
     */
    public function show(): void {
        $title = "Mentions légales";
        $cssFilePath = '_assets/styles/mentionLeg.css';
        $jsFilePath = '';
        $view = new \Blog\Views\mentions_legales\Mentionslegales();

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}