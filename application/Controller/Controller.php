<?php
/**
 * Created by PhpStorm.
 * User: Jirmik
 * Date: 08.12.2016
 * Time: 18:27
 */

namespace Mini\Controller;

class Controller{


    protected $twig = null;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(APP . 'view');
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addGlobal('session', $_SESSION);
        $makeUrl = new \Twig_SimpleFunction('makeURL', [$this, 'makeURL']);
        $this->twig->addFunction($makeUrl);
    }

    public function makeURL($page, $action, $param = null){
        if(isset($param)){
            return URL ."index.php?page=".$page."&action=".$action."&param=".$param;
        }else{
            return URL ."index.php?page=".$page."&action=".$action;
        }
    }

    protected function redirect($text , $page, $action, $state){
        $_SESSION['info']['text'] = $text;
        $_SESSION['info']['state'] = $state;
        header('location: ' . $this->makeURL($page,$action));
    }
    protected function redirect_with_url($text, $url, $state){
        $_SESSION['info']['text'] = $text;
        $_SESSION['info']['state'] = $state;
        header('location: ' . $url);
    }


}
