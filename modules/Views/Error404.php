<?php

namespace Blog\Views;

class Error404 {
    /**
     * Vue de la page mentions légales
     */
    public function showView(){
        ?>
        <main>
            <div class="container">
                <div class="row">
                    <div class="col s12">
                        <h1 class="error-message">Oups! Erreur 404</h1>
                        <p class="description">Désolé, la page que vous recherchez est introuvable.</p>
                        <div class="home-link">
                            <a href="/" class="btn waves-effect waves-light red darken-1">
                                Retour à la page d'accueil
                            </a>
                        </div>
                        <img src="https://cdn-icons-png.flaticon.com/512/2748/2748558.png" alt="Erreur 404"
                             class="responsive-img" style="width: 200px; margin-top: 20px;">
                    </div>
                </div>
            </div>
        </main>
        <?php
    }
}