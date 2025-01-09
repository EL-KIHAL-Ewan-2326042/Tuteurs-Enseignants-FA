<?php
/**
 * Fichier contenant la classe 'RouterException' servant
 * à lever une exception quand le routeur ne trouve aucune route
 *
 * PHP version 8.3
 *
 * @category Exception
 * @package  Assetsincludesexceptions
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

/**
 * Classe levant l'exception si aucune route n'est trouvée par le routeur
 *
 * PHP version 8.3
 *
 * @category Includes
 * @package  Assetsincludes
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class RouterException extends Exception
{
    /**
     * Initialise les attributs passés en paramètre
     *
     * @param string         $message  Message à afficher
     *                                 lorsque l'exception est levée
     * @param int            $code     Code correspondant à l'exception
     * @param Exception|null $previous L'exception précédente qui a été levée
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Récupère une description de l'instance
     *
     * @return string Renvoie une description de
     * l'instance avec la valeur de ses attributs
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}