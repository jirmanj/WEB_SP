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
     * @var Visitor instance s určitými metodami pro praci s databazí
     */
    private $visitor;
    /**
     * RegisterController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->visitor = new Visitor();
    }

    /*************************************************************
     * Funkce zajišťující výkres hlavní stránky registování
     */
    public function index()
    {
        echo $this->twig->render('register.twig');
        $_SESSION['info'] = null;
    }

    /*************************************************************
     * Funkce zjistí různé varianty chyb při registraci -
     * pokud je vše v pořádku, zapíše uživatele do db
     */
    public function register(){
        if(!isset($_POST['visitor'])){
            $this->redirect("Chyba při přenosu dat.","register","index", "danger");
        }else{
                $new_visitor = $_POST['visitor'];
                $visitors_email = $new_visitor['email'];
                $arr = $this->visitor->compareEmails($visitors_email);
                if(empty($arr)){
                    if($new_visitor['heslo'] == $new_visitor['znovaheslo']){
                        $this->visitor->addVisitorToDB($new_visitor);
                        $arr = $this->visitor->compareEmails($visitors_email);
                        if(empty($arr)){
                            $this->redirect("Nepodařilo se uživatele zaregistrovat.","register","index", "danger");
                        }else{
                            $this->redirect("Uživatel úspěšně zaregistrován!","home","index","success");
                        }
                    }else{
                        $this->redirect("Hesla nejsou shodná, zkuste to znovu.","register","index", "danger");
                    }
                }else{
                    $this->redirect("Jiný uživatel již používá email: ".$visitors_email.".","register","index","danger");

                }
        }

    }

    /*************************************************************
     * Fce pro přihlášení - zkouma různé chybu a pokud nenajde, přihlásí uživatele
     */
    public function login(){
        if(!isset($_POST['visitor'])){
            $this->redirect("Chyba při přenosu dat.","home","index", "danger");
        }else{
            $visitors_email = $_POST['visitor']['email'];
            $visitors_password = $_POST['visitor']['heslo'];

            $arr = $this->visitor->compareLogin($visitors_email, $visitors_password);
            $rank = $this->visitor->getRank($visitors_email)['hodnost'];
            if($rank<4){
                if(empty($arr)){
                    $this->redirect_with_url("Špatně zadaný email nebo heslo.", $_SERVER['HTTP_REFERER'], "danger");
                }else{
                    $_SESSION['user'] = $this->visitor->getInfoAboutVisitor($visitors_email);
                    $this->redirect(null,"home","index",null);
                }
            }else{
                $this->redirect_with_url("Uživatel byl zablokován.", $_SERVER['HTTP_REFERER'], "danger");
            }

        }
    }
}
