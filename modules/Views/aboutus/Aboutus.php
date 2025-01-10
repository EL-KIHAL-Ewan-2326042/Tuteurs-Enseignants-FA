<?php
/**
 * Fichier contenant la vue de la page 'A propos'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/aboutus
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\aboutus;

/**
 * Classe gérant l'affichage de la page 'A propos'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/aboutus
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Aboutus
{
    /**
     * Vue de la page 'A propos'
     * 
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <div class="row">
                <div class="col s12 m7 l7 push-l5">
                    <img class="responsive-img"
                        src="https://imgur.com/AqAvrsS.png"
                        alt="image de l'université">
                </div>
                <div class="col s12 m5 l5 pull-l7">
                    <h3> A Propos </h3> <br>
                    <p> Ce site a été développé dans le cadre d'une Situation 
                        d'Apprentissage et d'Évaluation (SAE), avec pour objectif 
                        de faciliter la mise en relation entre les étudiants et les 
                        tuteurs. Conçu pour encourager l'entraide académique, notre 
                        plateforme permet aux tuteurs de trouver rapidement un 
                        étudiant qui correspondent à leurs besoins, selon les 
                        matières et les compétences recherchées. <br> </p>

                    <p> Le site propose plusieurs fonctionnalités essentielles, 
                        telles que la connexion des tuteurs, ainsi qu'a un 
                        administrateur, un système de correspondance intelligent 
                        pour aider les tuteurs à choisir l'étudiant
                        adéquat. <br> </p>

                    <p> Notre équipe de développement, composée d'étudiants en 
                        informatique, s'est attelée à créer un outil fonctionnel et 
                        intuitif pour améliorer l'apprentissage collaboratif au sein 
                        de notre établissement.
                        Chaque membre de l'équipe a contribué au projet avec ses 
                        compétences spécifiques en développement web, gestion de 
                        projet et en base de données <br> </p>

                    <p> Ce projet s'inscrit dans notre cursus académique et nous 
                        permet de mettre en pratique les compétences acquises au 
                        cours de nos études en informatique. En plus de l'aspect 
                        technique, il nous permet de comprendre les défis liés à 
                        la conception d'un site web dédié à l'éducation.</p>
                </div>
            </div>
        </main>
        <?php
    }
}