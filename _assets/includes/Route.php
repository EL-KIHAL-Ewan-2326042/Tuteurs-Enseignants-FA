<?php
/**
 * Fichier contenant la classe Route
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

/**
 * Fais le lien entre une url et une classe
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
class Route
{

    private $_path;
    private $_callable;
    private $_matches = [];
    private $_params = [];

    /**
     * Constructeur de la classe route
     * Associe un lien à une fonction exécutable
     *
     * @param string                $path     le chemin
     * @param callable|class-string $callable Le contrôleur ou
     *                                        la fonction associée à la route.
     */
    public function __construct(string $path, callable|string $callable)
    {
        $this->_path = trim($path, '/');
        $this->_callable = $callable;
    }

    /**
     * Si l'url donné match, alors elle renvoie vraie(true), sinon faux(false)
     *
     * @param $url l'url fournie par l'utilisateur
     *
     * @return bool
     */
    public function match($url): bool
    {
        $url = trim($url, '/');
        $path = preg_replace('#:(\w+)#', '([^/]+)', $this->_path);
        $regex = "#^$path$#i";

        if (!preg_match($regex, $url, $matches)) {
            return false;
        }
        array_shift($matches);
        $this->_matches = $matches;
        return true;
    }

    /**
     * Appel la fonction associé au lieu avec les paramètres associés
     *
     * @return mixed
     */
    public function call(): mixed
    {
        return call_user_func_array($this->_callable, $this->_params);
    }
}