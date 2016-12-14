<?php

/**
 * Class HomeController
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Mini\Controller;

use Mini\Model\User;

class HomeController extends Controller
{
    /**
     * @var User instance s určitými metodami pro praci s databazí
     */
    private $user;

    /*************************************************************
     * HomeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }

    /*************************************************************
     * Funkce zajišťující vykreslení hlavní stránky webu
     */
    public function index()
    {
        // load views
        $publicArticles = $this->user->fewPublicArticles();
        for($i=0;$i<count($publicArticles);$i++){
            $publicArticles[$i]['ukazka'] .= "...";
        }
        echo $this->twig->render('home.twig',['articles' => $publicArticles]);
        $_SESSION['info'] = null;
    }

    /*************************************************************
     * Funkce zajišťující vykreslení konkrétního článku
     */
    function article(){
        if(isset($_REQUEST['param'])){
            $array = $this->user->onePublicArticle($_REQUEST['param']);
            echo $this->twig->render('article.twig',['array' => $array]);

        }else {
            $this->redirect("Nic nezadáno.","home","index", "danger");
        }

    }
}
