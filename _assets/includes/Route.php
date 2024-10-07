<?php
class Route {

    private $path;
    private $callable;
    private $matches = [];
    private $params = [];

    /**
     * Constructeur de la classe Route
     * Associe un lien à une fonction exécutable
     * @param $path
     * @param $callable
     */
    public function __construct($path, $callable) {
        $this->path = trim($path, '/');
        $this->callable = $callable;
    }

    /**
     * Si l'url donné match, alors elle renvoie vraie(true), sinon faux(false)
     * @param $url
     * @return bool
     */
    public function match($url){
        $url = trim($url, '/');
        $path = preg_replace('#:(\w+)#', '([^/]+)', $this->path);
        $regex = "#^$path$#i";

        if (!preg_match($regex, $url, $matches)) {
            return false;
        }
        array_shift($matches);
        $this->matches = $matches;
        return true;
    }

    /**
     * Appel la fonction associé au lieu avec les paramètres associés
     * @return mixed
     */
    public function call(){
        return call_user_func_array($this->callable, $this->params);
    }
}