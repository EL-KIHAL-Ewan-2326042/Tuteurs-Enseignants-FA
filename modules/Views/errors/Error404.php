<?php

namespace Blog\Views\errors;

class Error404 {
    /**
     * Vue de la page mentions légales
     */
    public function showView(){
        ?>
        <main>
            <div class="container card-panel white z-depth-3 s12 m6">
                <div class="row s12 m6 center">
                    <div class="col s12 center">
                        <h1 class="row">Oups! Erreur 404</h1>
                        <p class="row">Désolé, la page que vous recherchez est introuvable.</p>
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