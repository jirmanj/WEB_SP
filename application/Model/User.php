<?php
namespace Mini\Model;
use Mini\Core\Model;
/**
 * Class UserManager
 * @package Mini\Model
 *
 *
 */
class User extends Model
{
    /***********************************************************
     * Funkce vrátí uživatelovo článek podle id článku (id_prispevky).
     * @param $param id článku
     * @return pole vysledku
     */
    function yourPost($param){
        $id_clanku = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT id_prispevky, nazev, text, soubor FROM prispevky 
                                     WHERE :id = id_uzivatel AND :id_c = id_prispevky');
        $query->bindParam(':id',$id);
        $query->bindParam(':id_c',$id_clanku);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce vrátí všechny uživatelovo článku.
     * @return pole výsledků
     */
    function yourPosts(){
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT id_prispevky, nazev, posouzeno, soubor FROM prispevky
                                     WHERE :id = id_uzivatel
                                     ORDER BY id_prispevky ASC');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce pro porovnání textu - vrátí text, nebo prázdné pole
     * @param $text text článku
     * @return pole výsledku
     */
    function sameText($text){
        $text = htmlspecialchars($text);
        $query = $this->db->prepare('SELECT text FROM prispevky WHERE text= :text');
        $query->bindParam(':text', $text);
        $query->execute();

        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce zapíše nový článek do databáze
     * @param array $article pole potřebných atributů
     */
    function yourNewArticle($article){
        $id = $_SESSION['user']['id_uzivatel'];
        $nazev = htmlspecialchars($article['nazev']);
        $text = htmlspecialchars($article['text']);
        $query = $this->db->prepare('INSERT INTO prispevky (nazev,text,id_uzivatel) VALUES (:nazev,:text,:id)');
        $query->bindParam(':nazev',$nazev);
        $query->bindParam(':text',$text);
        $query->bindParam(':id',$id);
        $query->execute();

    }

    /***********************************************************
     * Funkce upraví (aktualizuje) konkrétní článek
     * @param $article pole potřebných atributů
     * @param $param id článku (id_prispevky)
     */
    function yourEditedArticle($article, $param){
        $id = $_SESSION['user']['id_uzivatel'];
        $param = htmlspecialchars($param);
        $nazev = htmlspecialchars($article['nazev']);
        $text = htmlspecialchars($article['text']);
        $query = $this->db->prepare('UPDATE prispevky SET nazev = :nazev, text = :text, posouzeno = NULL
                                     WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':nazev',$nazev);
        $query->bindParam(':text',$text);
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
    }

    /***********************************************************
     * Funkce, která porovná jestli je konkrétní článek uživatelovo - vrátí nazev, nebo nic.
     * @param $param id článku (id_prispevky)
     * @return pole výsledku
     */
    function isYourArticle($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT nazev FROM prispevky WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Vymaže soubor z konkrétního článku
     * @param $param id článku (id_prispevky)
     */
    function yourDeletedFile($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('UPDATE prispevky SET soubor = NULL WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
    }

    /***********************************************************
     * Funkce vymaže konkrétní článek
     * @param $param id článku (id_prispevky)
     */
    function yourDeletedArticle($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('DELETE FROM prispevky WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
        $query = $this->db->prepare('DELETE FROM hodnoceni WHERE id_prispevky = :id');
        $query->bindParam(':id',$param);
        $query->execute();
    }

    /***********************************************************
     * Vybere konkrétní soubor
     * @param $param id článku (id_prispevky)
     * @return pole výsledku
     */
    function getFile($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT soubor FROM prispevky WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id_u',$id);
        $query->bindParam(':id',$param);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);}

    /***********************************************************
     * Funkce zjistí, zda se konkrétní soubor již v žádném článku nenachází
     * @param $pole pole se souborem
     * @return pole výsledku
     */
    function isLastLocalFile($pole){
        $query = $this->db->prepare('SELECT nazev FROM prispevky WHERE soubor = :soubor');
        $query->bindParam(':soubor',$pole['soubor']);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);



    }

    /***********************************************************
     * Napíše url souboru do DB
     * @param $article pole potrebných atributů
     * @param $file string url souboru
     */
    function yourFileToArticle($article, $file){
        $nazev = htmlspecialchars($article['nazev']);
        $text = htmlspecialchars($article['text']);
        $query = $this->db->prepare('UPDATE prispevky SET soubor = :file WHERE nazev = :nazev AND text = :text');
        $query->bindParam(':file',$file);
        $query->bindParam(':nazev',$nazev);
        $query->bindParam(':text',$text);
        $query->execute();

    }

    /***********************************************************
     * Funkce vybere několik veřejných příspěvků
     * @return pole výsledků
     */
    function fewPublicArticles(){
        $query = $this->db->prepare('SELECT p.id_prispevky, p.nazev, p.text, p.posouzeno, MID(p.text,1,500) AS ukazka, 
                                      concat(u.jmeno, \' \', u.prijmeni) AS autor
                                      FROM prispevky p, uzivatel u WHERE posouzeno>0 AND u.id_uzivatel = p.id_uzivatel LIMIT 6');
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce vybere jeden veřejný příspěvek
     * @param $id id článku (id_prispevky)
     * @return pole výsledku
     */
    function onePublicArticle($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('SELECT p.id_prispevky, p.nazev, p.text,
                                     concat(u.jmeno, \' \', u.prijmeni) AS autor
                                     FROM prispevky p, uzivatel u WHERE id_prispevky = :id AND u.id_uzivatel = p.id_uzivatel');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }



}



