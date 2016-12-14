<?php
/**
 * Created by PhpStorm.
 * User: Jirmik
 * Date: 08.12.2016
 * Time: 18:27
 */

namespace Mini\Controller;

class Controller{

    /** @var null Twig - šablonovací systém*/
    protected $twig = null;

    /*************************************************************
     * Controller constructor.
     */
    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(APP . 'view');
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addGlobal('session', $_SESSION);
        $makeUrl = new \Twig_SimpleFunction('makeURL', [$this, 'makeURL']);
        $this->twig->addFunction($makeUrl);
    }

    /***************************************************************
     * Metoda vytváří URL stránky
     * @param $page stránka
     * @param $action akce
     * @param null $param parametr
     * @return string celá url
     */
    public function makeURL($page, $action, $param = null){
        if(isset($param)){
            return URL ."index.php?page=".$page."&action=".$action."&param=".$param;
        }else{
            return URL ."index.php?page=".$page."&action=".$action;
        }
    }

    /***************************************************************
     * Metoda přesměruje uživatele na danou stránku - manuálně.
     * @param $text string text určený do alertu
     * @param $page string stránka
     * @param $action string akce
     * @param $state string stav, v jakém se ma alert zobrazit
     */
    protected function redirect($text , $page, $action, $state){
        $_SESSION['info']['text'] = $text;
        $_SESSION['info']['state'] = $state;
        header('location: ' . $this->makeURL($page,$action));
    }

    /***************************************************************
     * Metoda přesměruje uživatele na danou stránku - automaticky.
     * @param $text string text určený do alertu
     * @param $url string url stránky
     * @param $state string stav, v jakém se ma alert zobrazit
     */
    protected function redirect_with_url($text, $url, $state){
        $_SESSION['info']['text'] = $text;
        $_SESSION['info']['state'] = $state;
        header('location: ' . $url);
    }


}
