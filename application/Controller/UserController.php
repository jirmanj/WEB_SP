<?php

/**
 * Class UserController
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Mini\Controller;

use Mini\Model\Reviewer;
use Mini\Model\User;

class UserController extends Controller
{
    private $reviewer;
    /**
     * PAGE: index
     * This method handles what happens when you move to http://yourproject/home/index (which is the default page btw)
     */

    public function __construct()
    {
        parent::__construct();
        $this->reviewer = new Reviewer();
    }


    public function index()
    {
        if(isset($_SESSION['user'])){
            switch($_SESSION['user']['hodnost']){
                case 1: break;
                case 2: $pole = $this->reviewer->getYourWork();
                        echo $this->twig->render('user_summary.twig', ['pole' => $pole]);
                        break;
                case 3: $pole = $this->getPosts();
                        echo $this->twig->render('user_summary.twig', ['pole' => $pole]);
                        break;
            }
            $_SESSION['info'] = null;
        }else{
            $this->redirect("Nejste přihlášený.","home","index","danger");
        }
    }






//************************************************************************************************
//********************************** twig "3" methods ********************************************
//************************************************************************************************
    public function create(){
        if(isset($_SESSION['user'])){
            if($_SESSION['user']['hodnost'] == 3){
                echo $this->twig->render('user_newArticle.twig');
                $_SESSION['info'] = null;
            }else{
                $this->redirect("Nemáte povolení sem jít","home","index","danger");
            }
        }else {
            $this->redirect("Nejste přihlášený.", "home", "index", "danger");
        }
    }

    public function edit(){
        if(isset($_SESSION['user'])){
            if($_SESSION['user']['hodnost'] == 3){
                if(isset($_REQUEST['param'])){
                    $pole = $this->getPost($_REQUEST['param']);
                    echo $this->twig->render('user_editArticle.twig', ['pole' => $pole]);
                }else{
                    $this->redirect(null,"error","index",null);
                }
                $_SESSION['info'] = null;
            }
        }
    }

    public function delete(){
        if(isset($_SESSION['user'])) {
            if ($_SESSION['user']['hodnost'] == 3) {
                if(isset($_REQUEST['param'])){
                    $pole = $this->getPost($_REQUEST['param']);
                    echo $this->twig->render('user_deleteArticle.twig', ['pole' => $pole]);
                }else{
                    $this->redirect(null,"error","index",null);
                }
                $_SESSION['info'] = null;
            }
        }
    }


//************************************************************************************************
//********************************** twig "2" methods ********************************************
//************************************************************************************************
    public function evaluate(){
        if(isset($_SESSION['user'])) {
            if ($_SESSION['user']['hodnost'] == 2) {
                if(isset($_REQUEST['param'])){
                    $pole = $this->reviewer->getAuthorsPost($_REQUEST['param']);
                    echo $this->twig->render('user_reviewArticle.twig', ['pole' => $pole]);
                }else{
                    $this->redirect(null,"error","index",null);
                }
                $_SESSION['info'] = null;
            }
        }
    }



//************************************************************************************************
//******************************* "3" functional methods *****************************************
//************************************************************************************************
    function createArticle(){
        if(!isset($_POST['article'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            $new_article = $_POST['article'];
            $user = new User();
            if(empty($user->sameText($new_article['text']))) {
                $user->yourNewArticle($new_article);
                $file = $_FILES['file'];
                if($file['size']>0){
                    $this->work_with_file($file,$user,$new_article);
                }else{
                    $this->redirect("Článek úspěšně uložen.","user","index","success");
                }

            }else{
                $this->redirect("Článek neuložen: možná duplikace. Text se již nachází v jiném článku.","user","index","danger");
            }
        }
    }

    function editArticle(){

        if(!isset($_POST['article'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            if(isset($_REQUEST['param'])){
                $user = new User();
                if(empty($user->isYourArticle($_REQUEST['param']))) {
                    $this->redirect("Nenalezen žádný váš článek.","user","index", "danger");
                }else {
                    $new_article = $_POST['article'];
                    $user->yourEditedArticle($new_article,$_REQUEST['param']);
                    if(isset($_POST['none'])){
                        $pole = $user->getFile($_REQUEST['param']);
                        $user->yourDeletedFile($_REQUEST['param']);
                        $pole1 = $user->isLastLocalFile($pole);
                        if(empty($pole1)){
                            unlink($pole['soubor']);
                        }
                        $this->redirect("Článek úspěšně změněn.","user","index","success");

                    }else{
                        $file = $_FILES['file'];
                        if($file['size']>0){
                            $this->work_with_file($file,$user,$new_article);
                        }else{
                            $this->redirect("Článek úspěšně změněn.","user","index","success");
                        }
                    }

                }


            }else {
                $this->redirect("Žádný zadaný článek.","user","index", "danger");
            }
        }
    }

    function deleteArticle(){
        if(!isset($_POST['article'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            if(isset($_REQUEST['param'])){
                $user = new User();
                if(empty($user->isYourArticle($_REQUEST['param']))) {
                    $this->redirect("Nenalezen žádný váš článek.","user","index", "danger");
                }else {
                    $pole = $user->getFile($_REQUEST['param']);
                    $user->yourDeletedArticle($_REQUEST['param']);
                    $pole1 = $user->isLastLocalFile($pole);
                    if(empty($pole1)){
                        unlink($pole['soubor']);
                    }
                    $this->redirect("Článek vymazán.","user","index","success");
                }


            }else {
                $this->redirect("Žádný zadaný článek.","user","index", "danger");
            }
        }
    }
    private function work_with_file($file, $user, $new_article){

        $target_dir = ROOT . "public".DIRECTORY_SEPARATOR."file".DIRECTORY_SEPARATOR ."".$_SESSION['user']['id_uzivatel'].DIRECTORY_SEPARATOR;
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($file["name"]);
        if (file_exists($target_file)) {
            $user->yourFileToArticle($new_article,$target_file);
            $this->redirect("Článek uložen s již existující souborem na serveru. Další úpravy článku jsou možné v Administraci.", "user","index", "success");
            $uploadOk = 0;
        }
        if ($file["size"] > 1000000) {
            $this->redirect("Článek uložen bez souboru, jelikož soubor přesahuje maximální velikost. Další úpravy článku jsou možné v Administraci.","user","index","danger");
        }elseif($file['type']!="application/pdf") {
            $this->redirect("Článek uložen bez souboru, jelikož soubor není v podporovaném formátu (Pouze PDF). Další úpravy článku jsou možné v Administraci.", "user","index", "danger");
        }else{
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $user->yourFileToArticle($new_article,$target_file);
                $this->redirect("Článek i soubor úspěšně přidán.","user","index","success");
            } else {
                $this->redirect("Článek uložen bez souboru, nahrávání souboru selhalo. Další úpravy článku jsou možné v Administraci.","user","index","danger");
            }
        }
    }



    private function getPosts(){
        $user = new User();
        $pole = $user->Yourposts();
        for($i=0;$i<count($pole);$i++){
            $cut = explode(DIRECTORY_SEPARATOR,$pole[$i]['soubor']);
            $pole[$i]['soubor_short'] = end($cut);
        }
        return $pole;
    }
    private function getPost($param){
        $user = new User();
        $pole = $user->yourPost($param);
        $cut = explode(DIRECTORY_SEPARATOR,$pole['soubor']);
        $pole['soubor_short'] = end($cut);
        return $pole;
    }

//************************************************************************************************
//******************************** "2" functional methods ****************************************
//************************************************************************************************
    function reviewArticle(){
        if(!isset($_POST['review'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            $new_review = $_POST['review'];
            if(isset($_REQUEST['param'])){
                $this->reviewer->setReview($new_review,$_REQUEST['param']);
                $this->redirect("Článek ohodnocen.","user","index", "success");
            }else {
                $this->redirect("Žádný zadaný článek.","user","index", "danger");
            }
        }
    }





//************************************************************************************************
//************************************ complex methods *******************************************
//************************************************************************************************


    function logout(){
        unset($_SESSION['user']);
        $this->redirect("Úspěšně odhlášeno", "home", "index","success");
    }

}
