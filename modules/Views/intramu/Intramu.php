<?php
/**
 * Fichier contenant la vue de la page de connexion à l'Intramu
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

class Intramu
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
     * Affiche la page de connexion à l'Intramu
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <section>
                <img
                        src="https://i.imgur.com/AqAvrsS.png"
                        alt="Image de l'université"
                        decoding="async"
                        fetchpriority="low"
                        width="800"
                        height="600"
                >
            </section>


            <section class="form-section">
                <article>
                    <h1>Service d'authentification</h1>
                    <p>Accédez au meilleur service pour les étudiants</p>
                </article>
                <form action="./intramu" method="POST" id="formAssociate" class="container">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="identifier" name="identifier" type="text" class="validate" maxlength="20"
                                   autocomplete="username" required>
                            <label for="identifier">Identifiant</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <input id="password" name="password" type="password" class="validate" maxlength="32"
                                   autocomplete="current-password" required>
                            <label for="password">Mot de passe</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <button class="btn waves-effect waves-light" type="submit" name="login" id="user">
                                Connexion
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($this->errorMessage)) : ?>
                    <div role="alert" aria-live="assertive" class="error-message">
                        <?= htmlspecialchars($this->errorMessage) ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        <?php
    }
}