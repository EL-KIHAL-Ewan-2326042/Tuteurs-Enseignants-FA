<?php

namespace Blog\Controllers;

use Blog\Views\Layout;
use Blog\Views\MentionLeg as MentionLegView;

/**
 * Contrôleur de la page mentions légales
 */
class MentionLeg{
    private Layout $layout;
    private MentionLegView $view;

    /**
     * Constructeur de la classe MentionLeg (contrôleur)
     * @param Layout $layout Instance de la classe Layout
     * @param MentionLegView $view Instance de la classe MentionLegView
     */
    public function __construct(Layout $layout, MentionLegView $view){
        $this->layout = $layout;
        $this->view = $view;
    }

    /**
     * Controlleur de la page mentions légales
     * @return void
     */
    public function show(): void {
        $title = "Mentions légales";
        $cssFilePath = '_assets/styles/mentionLeg.css';
        $jsFilePath = '';

        $this->layout->renderTop($title, $cssFilePath);
        $this->view->showView();
        $this->layout->renderBottom($jsFilePath);
    }
}