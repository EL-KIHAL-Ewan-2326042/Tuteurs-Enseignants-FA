<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Database;

class Dispatcher {
    /**
     * Controlleur du Dispatcher
     * @return void
     */
    public function show(): void {
        $title = "Dispatcher";
        $cssFilePath = '_assets/styles/dispatcher.css';
        $jsFilePath = '_assets/scripts/dispatcher.js';

        $db = Database::getInstance();
        $dispatcherModel = new \Blog\Models\Dispatcher($db);

        $view = new \Blog\Views\Dispatcher($dispatcherModel);

        $layout = new Layout();
        $layout->renderTop($title, $cssFilePath);
        $view->showView();
        $layout->renderBottom($jsFilePath);
    }
}