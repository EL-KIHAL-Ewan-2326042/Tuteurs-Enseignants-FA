<?php


namespace Blog\Controllers;

use Blog\Views\layout\Layout;

class Error404 {

    private Layout $layout;

    /**
     * Constructeur de la classe Error404 (controller)
     * @param Layout $layout Instance de la classe Layout
     */
    public function __construct(Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * Controlleur de la page Erreur 404
     * @return void
     */
    public function show(): void
    {
        $title = "Erreur 404";
        $cssFilePath = '/_assets/styles/erreur404.css';
        $jsFilePath = '';
        $view = new \Blog\Views\Error404();

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}