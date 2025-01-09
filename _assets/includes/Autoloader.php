<?php
/**
 * Fichier contenant l'autoloader de l'application web
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
namespace Includes;

/**
 * Autoloader qui permet de charger les fichiers nécessaires automatiquement
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
class Autoloader
{
    /**
     * Enregistre l'autoloader
     *
     * @return void
     */
    static function register(): void
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Inclut le fichier de la classe correspondante
     *
     * @param string $class la classe à charger
     *
     * @return void
     */
    static function autoload(string $class): void
    {

        if ($class === 'Includes\Database') {
            include '_assets/includes/Database.php';
            return;
        }

        if (str_contains($class, 'Blog')) {
            $replacements = [
                '\\' => '/',
                'Blog/' => '',
            ];

            $filename = str_replace(
                array_keys($replacements),
                array_values($replacements), $class
            );
            $path = 'modules/' . $filename . '.php';
        } elseif (str_contains($class, 'Test')) {
            $replacements = [
                '\\' => '/',
                'Test/' => '',
            ];

            $filename = str_replace(
                array_keys($replacements),
                array_values($replacements), $class
            );
            include 'tests/' . $filename . '.php';
        } else {
            $class = strtoupper(substr($class, 0, 1)) . substr($class, 1);

            if (str_contains($class, 'Exception') !== false) {
                $path = '_assets/includes/exceptions/' . $class . '.php';
            } else {
                $path = '_assets/includes/' . $class . '.php';
            }

        }
        if (file_exists($path)) {
            include $path;
        } else {
            include 'modules/Controllers/Error404.php';
        }
    }


}