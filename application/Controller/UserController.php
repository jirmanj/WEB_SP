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

use Mini\Model\Admin;
use Mini\Model\Reviewer;
use Mini\Model\User;

class UserController extends Controller
{
    private $user;
    /**
     * @var Reviewer instance s určitými metodami pro praci s databazí
     */
    private $reviewer;
    /**
     * @var Admin instance s určitými metodami pro praci s databazí
     */
    private $admin;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
        $this->reviewer = new Reviewer();
        $this->admin = new Admin();

    }

    /*************************************************************
     * Funkce vykreslí hlavní stránku "Služby" pro uživatele s hodností "recenzent","autor".
     */
    public function index()
    {
        if(isset($_SESSION['user'])){
            switch($_SESSION['user']['hodnost']){
                case 2: $before = $this->reviewer->getYourWork();
                        $after = $this->reviewer->getYourReviews();
                        echo $this->twig->render('user_summary.twig', ['before_arr' => $before, 'after_arr' => $after]);
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

    /*************************************************************
     * Funkce vykreslí stránku, kde autor může přidat nový příspěvek.
     */
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

    /*************************************************************
     * Funkce vykreslí stránku, kde může uživatel editovat článek
     */
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
    /*************************************************************
     * Funkce vykreslí stránku, kde může článek vymazat
     */
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
    /*************************************************************
     * Funkce vykreslí stránku, kde reneczent může ohodnotit článek
     */
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
//********************************** twig "1" methods ********************************************
//************************************************************************************************
    /*************************************************************
     * Funkce vykreslí stránku, kde má admin přehled o příspěvcích
     */
    public function articles(){
        $article = $this->admin->getAuthorOfArticle();
        $reviewers = $this->admin->getReviewers();
        $i = 0;
        foreach($article as $value){
            $review[$i]=$this->admin->getReviews($value);
            $i++;
        }
        if(empty($review)){
            echo $this->twig->render('user_articles.twig', ['article' => $article,
                'reviewers' => $reviewers]);
        }else{
            echo $this->twig->render('user_articles.twig', ['article' => $article,
                'reviewers' => $reviewers,
                'review' => $review]);
        }
        $_SESSION['info'] = null;
    }

    /*************************************************************
     * Funkce vykreslí stránku, kde má admin přehled o všech uživatelích
     */
    public function users(){
        $array = $this->admin->getOtherUsers();
        echo $this->twig->render('user_users.twig', ['array' => $array]);
        $_SESSION['info'] = null;
    }



//************************************************************************************************
//******************************* "3" functional methods *****************************************
//************************************************************************************************
    /*************************************************************
     * Funkce vytvoří článek
     */
    function createArticle(){
        if(!isset($_POST['article'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            $new_article = $_POST['article'];
            if(empty($this->user->sameText($new_article['text']))) {
                $this->user->yourNewArticle($new_article);
                $file = $_FILES['file'];
                if($file['size']>0){
                    $this->work_with_file($file,$new_article);
                }else{
                    $this->redirect("Článek úspěšně uložen.","user","index","success");
                }

            }else{
                $this->redirect("Článek neuložen: možná duplikace. Text se již nachází v jiném článku.","user","index","danger");
            }
        }
    }
    /*************************************************************
     * Funkce edituje článek
     */
    function editArticle(){

        if(!isset($_POST['article'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            if(isset($_REQUEST['param'])){
                if(empty($this->user->isYourArticle($_REQUEST['param']))) {
                    $this->redirect("Nenalezen žádný váš článek.","user","index", "danger");
                }else {
                    $new_article = $_POST['article'];
                    $this->user->yourEditedArticle($new_article,$_REQUEST['param']);
                    if(isset($_POST['none'])){
                        $pole = $this->user->getFile($_REQUEST['param']);
                        $this->user->yourDeletedFile($_REQUEST['param']);
                        $pole1 = $this->user->isLastLocalFile($pole);
                        if(empty($pole1)){
                            unlink($pole['soubor']);
                        }
                        $this->redirect("Článek úspěšně změněn.","user","index","success");

                    }else{
                        $file = $_FILES['file'];
                        if($file['size']>0){
                            $this->work_with_file($file,$new_article);
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
    /*************************************************************
     * Funkce maže článek
     */
    function deleteArticle(){
        if(!isset($_POST['article'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            if(isset($_REQUEST['param'])){
                if(empty($this->user->isYourArticle($_REQUEST['param']))) {
                    $this->redirect("Nenalezen žádný váš článek.","user","index", "danger");
                }else {
                    $pole = $this->user->getFile($_REQUEST['param']);
                    $this->user->yourDeletedArticle($_REQUEST['param']);
                    $pole1 = $this->user->isLastLocalFile($pole);
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

    /**
     * Funkce potřebná pro práci se souborem
     * @param $file soubor
     * @param $new_article pole s potřebnými atributy
     */
    private function work_with_file($file, $new_article){

        $target_dir = ROOT . "public".DIRECTORY_SEPARATOR."file".DIRECTORY_SEPARATOR ."".$_SESSION['user']['id_uzivatel'].DIRECTORY_SEPARATOR;
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($file["name"]);
        if (file_exists($target_file)) {
            $this->user->yourFileToArticle($new_article,$target_file);
            $this->redirect("Článek uložen s již existující souborem na serveru. Další úpravy článku jsou možné v Administraci.", "user","index", "success");
            $uploadOk = 0;
        }
        if ($file["size"] > 1000000) {
            $this->redirect("Článek uložen bez souboru, jelikož soubor přesahuje maximální velikost. Další úpravy článku jsou možné v Administraci.","user","index","danger");
        }elseif($file['type']!="application/pdf") {
            $this->redirect("Článek uložen bez souboru, jelikož soubor není v podporovaném formátu (Pouze PDF). Další úpravy článku jsou možné v Administraci.", "user","index", "danger");
        }else{
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $this->user->yourFileToArticle($new_article,$target_file);
                $this->redirect("Článek i soubor úspěšně přidán.","user","index","success");
            } else {
                $this->redirect("Článek uložen bez souboru, nahrávání souboru selhalo. Další úpravy článku jsou možné v Administraci.","user","index","danger");
            }
        }
    }


    /*************************************************************
     * Funkce získa uživatelovo články
     */
    private function getPosts(){
        $pole = $this->user->Yourposts();
        for($i=0;$i<count($pole);$i++){
            $cut = explode(DIRECTORY_SEPARATOR,$pole[$i]['soubor']);
            $pole[$i]['soubor_short'] = end($cut);
        }for($i=0;$i<count($pole);$i++){
            $cut = explode(DIRECTORY_SEPARATOR,$pole[$i]['soubor']);
            $pole[$i]['soubor_short'] = end($cut);
        }
        return $pole;
    }
    /*************************************************************
     * Funkce získa uživatelovo článek
     */
    private function getPost($param){
        $pole = $this->user->yourPost($param);
        $cut = explode(DIRECTORY_SEPARATOR,$pole['soubor']);
        $pole['soubor_short'] = end($cut);
        return $pole;
    }

//************************************************************************************************
//******************************** "2" functional methods ****************************************
//************************************************************************************************
    /*************************************************************
     * Funkce ohodnotí článek
     */
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
//******************************** "1" functional methods ****************************************
//************************************************************************************************
    /*************************************************************
     * Funkce nastaví článek recenzentovi
     */
    function setArticleToReviewer(){
        if(!isset($_POST['recenzent'])){
            $this->redirect("Chyba při přenosu dat.","user","index", "danger");
        }else{
            $new_review = $_POST['recenzent'];
            if(isset($_REQUEST['param'])){
                $this->admin->setNewReview($new_review,$_REQUEST['param']);
                $this->redirect("Uživateli přidán článek k ohodnocení.","user","articles", "success");
            }else {
                $this->redirect("Nic nezadáno.","user","articles", "danger");
            }
        }
    }
    /*************************************************************
     * Funkce vymaže recenzi
     */
    function deleteReview(){
        if($_SESSION['user']['hodnost'] == 1){
            if(isset($_REQUEST['param'])){
                $this->admin->deleteReview($_REQUEST['param']);
                $this->redirect("Recenze úspěšně odebrána.","user","articles", "success");
            }else {
                $this->redirect("Nic nezadáno.","user","articles", "danger");
            }
        }else{
            $this->redirect("Nemáte oprávnění pro tuto akci.","home","index", "danger");
        }
    }
    /*************************************************************
     * Funkce získa zamítne článek
     */
    function rejectArticle(){
        if($_SESSION['user']['hodnost'] == 1){
            if(isset($_REQUEST['param'])){
                $array = $this->admin->getSmallReview($_REQUEST['param']);
                if($array['soucet_uzivatelu'] == 0){
                    $this->redirect("Článek nelze zamítnout, nikdo ho zatím neohodnotil.","user","articles", "danger");
                }else{
                    $this->admin->rejectArticle($_REQUEST['param']);
                    $this->admin->deleteReview($_REQUEST['param']);
                    $this->redirect("Článek byl úspěšně zamítnut.","user","articles", "success");
                }

            }else {
                $this->redirect("Nic nezadáno.","user","articles", "danger");
            }
        }else{
            $this->redirect("Nemáte oprávnění pro tuto akci.","home","index", "danger");
        }
    }
    /*************************************************************
     * Funkce schválí článek a publikuje ho
     */
    function shareArticle(){
        if($_SESSION['user']['hodnost'] == 1){
            if(isset($_REQUEST['param'])){
                $array = $this->admin->getSmallReview($_REQUEST['param']);
                if($array['soucet_uzivatelu'] == 0){
                    $this->redirect("Článek nelze publikovat, nikdo ho zatím neohodnotil.","user","articles", "danger");
                }else{
                    $avg = $array['soucet_hodnoceni']/$array['soucet_uzivatelu'];
                    $this->admin->setAVG($_REQUEST['param'],$avg);
                    $this->redirect("Článek úspěšně publikován.","user","articles", "success");
                }
            }else {
                $this->redirect("Nic nezadáno.","user","articles", "danger");
            }
        }else{
            $this->redirect("Nemáte oprávnění pro tuto akci.","home","index", "danger");
        }
    }
    /*************************************************************
     * Funkce pro úpravu role uživatele
     */
    function editUser(){
        if($_SESSION['user']['hodnost'] == 1){
            if(isset($_REQUEST['param'])){
                if(isset($_POST['radio'])){
                    switch($_POST['radio']){
                        case "Autor": $number = 3;
                                        break;
                        case "Recenzent": $number = 2;
                                            break;
                        case "Zablokovaný": $number = 4;
                        break;
                    }
                    $this->admin->changeRank($number,$_REQUEST['param']);
                    $this->redirect("Hodnost uživatele úspěšně změněna.","user","users", "success");
                }else{
                    $this->redirect("Žádné tlačítko nezadáno.","user","users", "danger");
                }
            }else {
                $this->redirect("Nic nezadáno.","user","users", "danger");
            }
        }else{
            $this->redirect("Nemáte oprávnění pro tuto akci.","home","index", "danger");
        }
    }
    /*************************************************************
     * Funkce vymaže uživatele
     */
    function deleteUser(){
        if($_SESSION['user']['hodnost'] == 1){
            if(isset($_REQUEST['param'])){
                $this->admin->deleteUser($_REQUEST['param']);
                $this->redirect("Uživatel úspěšně vymazán.","user","users", "success");
            }else {
                $this->redirect("Nic nezadáno.","user","users", "danger");
            }
        }else{
            $this->redirect("Nemáte oprávnění pro tuto akci.","home","index", "danger");
        }
    }





//************************************************************************************************
//************************************ complex methods *******************************************
//************************************************************************************************

    /*************************************************************
     * Funkce ohlásí uživatele
     */
    function logout(){
        unset($_SESSION['user']);
        $this->redirect("Úspěšně odhlášeno", "home", "index","success");
    }

}
