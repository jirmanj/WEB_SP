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
        $_SESSION['info'] = null;
    }
    public function register(){
        if(!isset($_POST['visitor'])){
            $this->redirect("Chyba při přenosu dat.","register","index", "danger");
        }else{
                $new_visitor = $_POST['visitor'];
                $visitors_email = $new_visitor['email'];
                $visitor = new Visitor();
                $arr = $visitor->compareEmails($visitors_email);
                if(empty($arr)){
                    if($new_visitor['heslo'] == $new_visitor['znovaheslo']){
                        $visitor->addVisitorToDB($new_visitor);
                        $arr = $visitor->compareEmails($visitors_email);
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

    public function login(){
        if(!isset($_POST['visitor'])){
            $this->redirect("Chyba při přenosu dat.","home","index", "danger");
        }else{
            $visitor = new Visitor();
            $visitors_email = $_POST['visitor']['email'];
            $visitors_password = $_POST['visitor']['heslo'];

            $arr = $visitor->compareLogin($visitors_email, $visitors_password);
            if(empty($arr)){
                $this->redirect_with_url("Špatně zadaný email nebo heslo.", $_SERVER['HTTP_REFERER'], "danger");
            }else{
                $_SESSION['user'] = $visitor->getInfoAboutVisitor($visitors_email);
                $this->redirect(null,"home","index",null);
            }
        }
    }

/*    private function redirect($text , $page, $action, $state){
        $_SESSION['info']['text'] = $text;
        $_SESSION['info']['state'] = $state;
        header('location: ' . $this->makeURL($page,$action));
    }
    private function redirect_with_url($text, $url, $state){
        $_SESSION['info']['text'] = $text;
        $_SESSION['info']['state'] = $state;
        header('location: ' . $url);
    } */
}
