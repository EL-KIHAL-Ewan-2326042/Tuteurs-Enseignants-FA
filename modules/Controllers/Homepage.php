<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Blog\Views\Homepage as HomepageView;

class Homepage {
    private Layout $layout;
    private HomepageView $view;
    /**
     * Constructeur de la classe Homepage (controller))
     * @param Layout $layout Instance de la classe Layout
     * @param HomepageView $view Instance de la classe HomepageView
     */
    public function __construct(Layout $layout,HomepageView $view) {
        $this->layout = $layout;
        $this->view = $view;
    }

    /**
     * Controlleur de la homepage
     * @return void
     */
    public function show(): void {

        $title = "Accueil";
        $cssFilePath = '';
        $jsFilePath = '';

        $this->layout->renderTop($title, $cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}