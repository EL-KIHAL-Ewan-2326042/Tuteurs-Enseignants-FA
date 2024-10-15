<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Blog\Views\AboutUs as AboutUsView;

class AboutUs {

    private Layout $layout;
    private AboutUsView $view;

    /**
     * Constructeur de la classe AboutUs (controller)
     * @param Layout $layout Instance de la classe Layout
     * @param AboutUsView $view Instance de la classe AboutUsView
     */
    public function __construct(Layout $layout, AboutUsView $view) {
        $this->layout = $layout;
        $this->view = $view;
    }

    /**
     * Controlleur de la page a propos
     * @return void
     */
    public function show(): void {

        $title = "A Propos";
        $cssFilePath = '';
        $jsFilePath = '';

        $this->layout->renderTop($title,$cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);

    }
}