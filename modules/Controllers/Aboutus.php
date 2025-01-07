<?php
namespace Blog\Controllers;

use Blog\Views\layout\Layout;

class Aboutus {

    private Layout $layout;

    /**
     * Constructeur de la classe Aboutus (controller)
     * @param Layout $layout Instance de la classe Layout
     */
    public function __construct(Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * Controlleur de la page a propos
     * @return void
     */
    public function show(): void {
        $title = "A Propos";
        $cssFilePath = '';
        $jsFilePath = '';
        $view = new \Blog\Views\Aboutus();

        $this->layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}