<?php

class Autoloader
{

    /**
     * Enregistre l'autoloader
     * @return void
     */
    static function register(): void
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Inclut le fichier de la classe correspondante
     * @param $class
     * @return void
     */
    static function autoload($class): void
    {
        if (str_contains($class, 'Blog')) {
            $replacements = [
                '\\' => '/',
                'Blog/' => '',
            ];

            $filename = str_replace(array_keys($replacements), array_values($replacements), $class);
            $path = 'modules/' . $filename . '.php';

        } else {
            $class = strtoupper(substr($class, 0, 1)) . substr($class, 1);

            if (str_contains($class, 'Exception') !== false) {
                $path = '_assets/includes/exceptions/' . $class . '.php';
            } else {
                $path = '_assets/includes/' . $class . '.php';
            }

        }
        if (file_exists($path)) {
            require $path;
        } else {
            require 'modules/Controllers/Error404.php';
        }
    }


}