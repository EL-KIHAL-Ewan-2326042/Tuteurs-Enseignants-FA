<?php


namespace Blog\Controllers;

use Blog\Views\Layout;

class Error404
{

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

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}