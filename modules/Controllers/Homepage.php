<?php

namespace Blog\Controllers;

use Blog\Views\layout\Layout;

class Homepage
{
    private Layout $_layout;
    public function __construct(Layout $layout)
    {
        $this->_layout = $layout;
    }

    public function show(): void {
        $title = "HomePage";
        $cssFilePath = '/_assets/styles/homepage.css';
        $jsFilePath = '/_assets/scripts/homepage.js';

        $this->_layout->renderTop($title, $cssFilePath);
        (new \Blog\Views\homepage\HomePage)->showView();
        $this->_layout->renderBottom($jsFilePath);
    }
}