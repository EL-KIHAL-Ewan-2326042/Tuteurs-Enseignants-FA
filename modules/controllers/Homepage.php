<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
class Homepage {

    /**
     * Controlleur de la Homepage
     * @return void
     */
    public function show(): void {

        $title = "Accueil";
        $description = "";
        $cssFilePath = '_assets/includes/css/homepage.css';
        $jsFilePath = '';

        $view = new \Blog\Views\homepage();

        $layout = new Layout();
        $layout->renderTop($title, $description, $cssFilePath, $jsFilePath);
        $view->showView();
        $layout->renderBottom();
    }
}