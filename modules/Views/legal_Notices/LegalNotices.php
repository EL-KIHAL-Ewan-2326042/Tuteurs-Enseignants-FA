<?php
/**
 * Fichier contenant la vue de la page 'Mentions Légales'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/mentions_Legales
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\legal_Notices;

/**
 * Classe gérant l'affichage de la page 'Mentions Légales'
 *
 * @category View
 * @package  TutorMap/modules/Views/legal_Notices
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class LegalNotices
{
    /**
     * Affiche la page 'Mentions Légales'
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <div class="column">
                <h3>MENTIONS LÉGALES</h3> <br>
                <h4>ÉDITEUR</h4>
                <p>Directeur de la publication : Mickaël Martin-Nevot.</p>

                <h4>RAISON SOCIALE ET DÉNOMINATION</h4>
                <p>Le directeur de la publication est une personne physique
                désireuse de garder son anonymat. Les coordonnées exactes de
                l'éditeur ont donc été transmises de manière complète à
                l'hébergeur. C'est l'hébergeur qui peut-être tenu de communiquier
                les informations sur l'éditeur, mais uniquement dans le cadre d'une
                procédure judiciaire.</p>

                <h4>CONCEPTION ET REALISATION DU SITE</h4>

                <p> Salenson Léo <br> Courriel : leo.salenson@etu.univ-amu.fr <br>
                    EL KIHAL Ewan <br> Courriel : ewan.el-kihal@etu.univ-amu.fr <br>
                    Alvares Titouan<br>Courriel : titouan.alvares@etu.univ-amu.fr <br>
                Avias Daphné <br>Courriel : daphne.avias@etu.univ-amu.fr <br>
                Kerbadou Islem <br>Courriel : islem.kerbadou@etu.univ-amu.fr <br>
                Pellet Casimir <br>Courriel : casimir.pellet@etu.univ-amu.fr </p>

                <h4>HEBERGEMENT</h4>
                <p>Alwaysdata</p>

                <h4>CONTENU ÉDITORIAL ET MISE A JOUR</h4>
                <p>Les informations mises à disposition sur le site n'ont
                qu'une valeur indicative. Nous rappelon que le contenu du
                site Web peut comporter des erreurs ou oublis, et qu'il est
                susceptible d'être modifié ou mis à jour sans préavis. <br>
                La responsabilité du directeur de la publication ne peut,
                en aucune manière, être engagée quant au contenu des informations
                figurant sur son site ou aux conséquences pouvant résulter
                de leur utilisation ou interprétation. <br>
                Les liens hypertextes de ce site Web pointent vers d'autres
                ressources sur le réseau Internet, pour autant, la responsabilité
                du responsable de la publication ne saurait être engagée au titre
                d'un site tiers atteint via ce présent site Web.</p>

                <h4>PROPRIÉTÉ INTELLECTUELLE</h4>
                <p>L'ensemble de ce site Web relève des législations françaises et
                internationales sur le droit d'auteur et la propriéte intellectuelle.
                Tous les droits de reproduction sont réservés, y compris pour les
                documents iconographiques et photographiques. <br>
                Selon <a href="https://fr.wikipedia.org/wiki/Convention_de_Berne_pour
_la_protection_des_%C5%93uvres_litt%C3%A9raires_et_artistiques">
                        convention de Berne pour la protection
                        des œuvres littéraires et artistiques</a>,
                les oeuvres ayant pour pays d'origine l'un des États contractants,
                c'est-à-dire dont l'auteur est un ressortissant d'un des États de
                l'union, doivent bénéficier dans chacun des autres États contractants
                de la même protection que celle que cet État accorde aux oeuvres
                de ses propres nationaux. <br>
                L'ensemble du site Web et des oeuvres disponibles sont soumises
                à la
                <a href=
                   "https://github.com/AVIAS-Daphne-2326010/
Tuteurs-Enseignants/edit/main/LICENSE">
                    licence MIT</a>.</p>

                <h4>DONNÉES PERSONNELLES</h4>
                <p>Soucieux du respect de la vie privée des utilisateurs du site
                Web, l'éditeur, responsable de traitement, s'engage à ce que la
                collecte et le traitement de ces informations soient effectués
                conformément au RGPD. <br>
                Tout utilisateur dispose, à tout moment et quelle qu'en soit la
                raison, d'un droit d'accès, de modification, de rectification et
                de suppression des données personnelles qu'il aurait indiquées
                lors de l'utilisation du site. <br>
                Pour exercer ses droits, l'utilisateur peut demander la suppression
                de son compte ou des données collectées auprès de l'éditeur
                (également concepteur et réalisateur).</p>

                <h4>PROCEDURE DE NOTIFICATION</h4>
                <p>L'utilisateur qui constaterait des inexactitudes, des
                informations erronées ou des informations de caractère manifestement
                illicite, est tenu d'en informer le responsable
                de la publication afin que celui-ci y mette fin.</p>

                <h4>MODIFICATION DE LA NOTICE LÉGALE</h4>
                <p>Mickaël Martin-Nevot, propriétaire et exploitant du site,
                se réserve le droit de modifir la présente notice à tout moment.
                L'utilisateur s'engage donc à la consulter régulièrement.</p>

                <h4>INFORMATIQUE ET LIBERTÉ</h4>
                <p>Ce site Web comporte des informations nominatives concernant
                des personnels, des étudiants ou des partenaires de
                Mickaël Martin-Nevot. Conformément à la loi n°78-17 du janvier 1978,
                relative à l'Informatique, aux Fichiers et aux Libertés (article 38,
                39, 40), vous disposez d'un droit d'accès, de rectification et de
                suppression des données vous concernant, en ligne sur ce site Web.
                Pour exercer ce droit, vous pouvez vous adresser au directeur
                de la publication.</p>
                </div>
        </main>
        <?php
    }
}