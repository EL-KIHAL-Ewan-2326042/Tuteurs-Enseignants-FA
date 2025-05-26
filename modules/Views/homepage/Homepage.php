<?php

namespace Blog\Views\homepage;


class HomePage
{
    public function __construct(
    ) {
    }

    public function showView(): void
    {
        ?>
        <main>
            <section class="decoration">
                <!-- 4 ronds -->
                <span class="rond" style="
    top: 15%;
    left: 8%;
    width: 7vw;
    height: 7vw;
    color: var(--couleur-corail);
  "></span>

                <span class="carre" style="
    top: 10%;
    right: 12%;
    width: 3vw;
    height: 3vw;
    rotate: 10deg;
    color: var(--couleur-orange);
  "></span>

                <span class="rond" style="
    bottom: 45%;
    right: 10%;
    width: 6vw;
    height: 6vw;
    color: var(--couleur-or);
  "></span>

                <!-- 3 carrés -->
                <span class="rond" style="
    top: 12%;
    right: 38%;
    width: 5vw;
    height: 5vw;
    color: var(--couleur-or);
  "></span>

                <span class="carre" style="
    bottom: 30%;
    left: 13%;
    width: 7vw;
    height: 7vw;
    rotate: 20deg;
    color: var(--couleur-or);
  "></span>

                <span class="carre" style="
    top: 70%;
    right: 22%;
    width: 6vw;
    height: 6vw;
    color: var(--couleur-corail);
  "></span>
            </section>



            <section class="accroche">
                <div>
                    <h1>
                        Répartiteur intelligent de tuteurs enseignants
                    </h1>
                    <p>
                        Simplifiez la gestion des stages et alternances en associant chaque étudiant à son tuteur idéal, selon vos besoins.
                    </p>

                </div>
                <div>
                    <a href="/ask">Tout les étudiants</a>
                    <a href="/account">Explorer</a>
                </div>
            </section>
        </main>
        <?php
    }
}