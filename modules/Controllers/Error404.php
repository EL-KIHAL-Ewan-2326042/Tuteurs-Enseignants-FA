<?php
namespace Blog\Controllers;

use Blog\Views\layout\Layout;

/**
 * Classe gérant les échanges de données entre
 * le modèle et la vue de la page 'Erreur 404'
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Error404
{

    private Layout $_layout;

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Layout $layout Instance de la classe Layout
     *                       servant de vue pour la mise en page
     */
    public function __construct(Layout $layout)
    {
        $this->_layout = $layout;
    }

    /**
     * Contrôleur de la page 'Erreur 404'
     *
     * @return void
     */
    public function show(): void
    {
        $title = "Erreur 404";
        $cssFilePath = '/_assets/styles/erreur404.css';
        $jsFilePath = '';
        $view = new \Blog\Views\errors\Error404();

        $this->_layout->renderTop($title, $cssFilePath);
        $view->showView();
        $this->_layout->renderBottom($jsFilePath);
    }
}