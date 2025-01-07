<?php

namespace Blog\Controllers;

use Blog\Models\Internship;
use Blog\Models\Teacher;
use Blog\Views\Layout;
use Includes\Database;
use Blog\Views\Account as AccountView;

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
        $teacherModel = new Teacher($db);
        $internshipModel = new Internship($db);

        $view = new AccountView($teacherModel, $internshipModel);

        $title = "Compte";
        $cssFilePath = '_assets/styles/account.css';
        $jsFilePath = '_assets/scripts/account.js';

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}