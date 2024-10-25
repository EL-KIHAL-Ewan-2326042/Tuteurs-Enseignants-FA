<?php


namespace Blog\Controllers;

use Blog\Views\Layout;
use Blog\Views\Error404 as Error404View;

class Error404 {

    private Layout $layout;
    private Error404View $view;

    /**
     * Constructeur de la classe Error404 (controller)
     * @param Layout $layout Instance de la classe Layout
     * @param Error404View $view Instance de la classe Error404View
     */
    public function __construct(Layout $layout, Error404View $view) {
        $this->layout = $layout;
        $this->view = $view;
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

        $this->layout->renderTop($title, $cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}