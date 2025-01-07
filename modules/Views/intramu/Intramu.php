<?php
/**
 * Fichier contenant la vue de la page 'Intramu'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/intramu
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\intramu;

/**
 * Classe gérant l'affichage de la page 'Intramu'
 *
 * @category View
 * @package  TutorMap/modules/Views/intramu
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
readonly class Intramu
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param string $errorMessage message d'erreur
     */
    public function __construct(private string $errorMessage)
    {
    }

    /**
     * Vue de la homepage
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <div class="card-panel white z-depth-3">
                <div class="row">
                    <form class="col s12" action="./intramu"
                    method="POST" id="formAssociate">
                        <div class="row">
                            <h5 class="indigo-text center-align">
                                Aix-Marseille Université - Service d'authentification
                            </h5>
                        </div>
                        <div class="row center-align">
                            <p class="red-text"><?php echo $this->errorMessage?></p>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input type="text" id="identifier" name="identifier"
                                maxlength="20" class="validate">
                                <label for="identifier">Identifiant</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input type="password" id="password" name="password"
                                maxlength="32" class="validate">
                                <label for="password">Mot de Passe</label>
                            </div>
                        </div>
                        <div class="row center-align">
                            <button class="btn waves-effect waves-light"
                            type="submit" name="login" id="user">Connexion
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <?php
    }
}