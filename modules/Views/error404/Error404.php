<?php
/**
 * Fichier contenant la vue de la page 'Erreur 404'
 * apparaissant quand la page recherchée est introuvable
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/error404
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\erreur404;

/**
 * Classe gérant l'affichage de la page 'Erreur 404'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/error404
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
    /**
     * Affiche la page 'Erreur 404'
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <div class="container card-panel white z-depth-3 s12 m6">
                <div class="row s12 m6 center">
                    <div class="col s12 center">
                        <h1 class="row">Oups! Erreur 404</h1>
                        <p class="row">
                            Désolé, la page que vous recherchez est introuvable.
                        </p>
                        <div class="row">
                            <a href="/" class="btn waves-effect waves-light">
                                Retour à la page d'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php
    }
}