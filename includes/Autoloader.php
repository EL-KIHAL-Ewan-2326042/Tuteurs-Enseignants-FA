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

namespace includes;

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
    public static function register(): void
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Inclut le fichier de la classe correspondante
     *
     * @param string $class la classe à charger
     *
     * @return void
     */
    public static function autoload(string $class): void
    {
        $class = str_replace('\\', '/', $class);

        $baseDir = __DIR__ . '/../modules/';

        if ($class === 'includes\Database') {
            include __DIR__ . '/Database.php';
            return;
        }

        if (str_contains($class, 'Blog')) {
            $class = str_replace('Blog/', '', $class);
            $file = $baseDir . $class . '.php';
        } elseif (str_contains($class, 'Test')) {
            $file = __DIR__ .
                '/../tests/' . str_replace('Test/', '', $class) . '.php';
        } elseif (str_contains($class, 'Exception')) {
            $file = __DIR__ . '/exceptions/' . basename($class) . '.php';
        } else {
            $file = __DIR__ . '/' . basename($class) . '.php';
        }

        if (file_exists($file)) {
            include $file;
        } else {
            include __DIR__ . '/../modules/Controllers/Error404.php';
        }
    }
}
