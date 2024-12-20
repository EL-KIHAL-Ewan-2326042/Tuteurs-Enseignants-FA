<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Includes\Database;
use Blog\Views\Account as AccountView;
use Blog\Models\Account as AccountModel;

class Account {
    private Layout $layout;

    /**
     * Constructeur de la classe Account (controller)
     * @param Layout $layout Instance de la classe Layout
     */
    public function __construct(Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * Controlleur de Account.
     * TO DO!!!!
     * @return void
     */
    public function show(): void {

        if (!isset($_SESSION['identifier'])) {
            header('Location: /intramu');
            return;
        }

        $db = Database::getInstance();
        $model = new \Blog\Models\Account($db);
        $view = new \Blog\Views\Account($model);

        $title = "Compte";
        $cssFilePath = '_assets/styles/account.css';
        $jsFilePath = '_assets/scripts/account.js';

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}