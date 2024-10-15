<?php
namespace Blog\Controllers;

use Blog\Views\Layout;
use Blog\Views\AboutUs as AboutUsView;

class AboutUs {

    private Layout $layout;
    private AboutUsView $view;

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
        $this->layout->renderBottom($jsFilePath);
        $this->view->showView();

    }
}