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

use Mini\Model\Visitor;

class RegisterController extends Controller
{
    /**
     * PAGE: index
     * This method handles what happens when you move to http://yourproject/home/index (which is the default page btw)
     */

    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        echo $this->twig->render('register.twig');
        $_SESSION['hlaska'] = null;
    }
    public function registrovat(){
        if(!isset($_POST['nu'])){
            $this->premistit("Něco se nevydařilo..","register","index", "danger");
        }else{
                $uzivatel = new Visitor();
                $arr = $uzivatel->zjistiShoduEmailu($_POST['nu']['email']);
                if(empty($arr)){
                    if($_POST['nu']['heslo'] == $_POST['nu']['znovaheslo']){
                        $uzivatel->pridejUzivatele($_POST['nu']);
                        $arr = $uzivatel->zjistiShoduEmailu($_POST['nu']['email']);
                        if(empty($arr)){
                            $this->premistit("Nepodařilo se uživatele přidat..","register","index", "danger");
                        }else{
                            $this->premistit("Uživatel úspěšně přidán!","home","index","success");
                        }
                    }else{
                        $this->premistit("Hesla nejsou shodná, zkuste to znovu.","register","index", "danger");
                    }
                }else{
                    $this->premistit("Jiný uživatel již používá email: ".$_POST['nu']['email'].".","register","index","danger");

                }
        }

    }
    private function premistit($hlaska ,$page, $action, $stav){
        $_SESSION['hlaska']['text'] = $hlaska;
        $_SESSION['hlaska']['stav'] = $stav;
        header('location: ' . $this->makeURL($page,$action));
    }
    private function premistit_url($hlaska, $url, $stav){
        $_SESSION['hlaska']['text'] = $hlaska;
        $_SESSION['hlaska']['stav'] = $stav;
        header('location: ' . $url);
    }

    public function prihlasit(){
        if(!isset($_POST['su'])){
                $this->premistit("Něco se pokazilo..","home","index", "danger");
        }else{
            $uzivatel = new Visitor();
            $arr = $uzivatel->zjistiPrihlaseni($_POST['su']['email'], $_POST['su']['heslo']);
            if(empty($arr)){
                $this->premistit_url("Špatně zadaný email nebo heslo.", $_SERVER['HTTP_REFERER'], "danger");
            }else{
                $this->premistit(null,"user","index");
                $_SESSION['user'] = $uzivatel;
            }
        }
    }
}
