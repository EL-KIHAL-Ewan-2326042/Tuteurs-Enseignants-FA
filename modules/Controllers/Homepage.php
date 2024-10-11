<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Database;

class Homepage {

    /**
     * Controlleur de la homepage
     * @return void
     */
    public function show(): void {

        $title = "Accueil";
        $cssFilePath = '_assets/styles/homepage.css';
        $jsFilePath = '';

        $db = new Database();
        $model = new \Blog\Models\Homepage($db);
        $view = new \Blog\Views\Homepage($model);

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}