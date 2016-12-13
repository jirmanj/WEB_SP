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

 /*   function AllPosts(){
        $query = $this->db->prepare('SELECT p.nazev,u.prijmeni
                                      FROM uzivatel u, prispevky p, pomocna pom
                                      WHERE pom.id_prispevky = p.id_prispevky
                                      AND u.id_uzivatel = pom.id_uzivatel
                                      ORDER BY p.nazev ASC');
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    } */

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

    function yourPosts(){
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT id_prispevky, nazev, posouzeno, soubor FROM prispevky
                                     WHERE :id = id_uzivatel
                                     ORDER BY id_prispevky ASC');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    function sameText($text){
        $text = htmlspecialchars($text);
        $query = $this->db->prepare('SELECT text FROM prispevky WHERE text= :text');
        $query->bindParam(':text', $text);
        $query->execute();

        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    function yourNewArticle(array $article){
        $id = $_SESSION['user']['id_uzivatel'];
        $nazev = htmlspecialchars($article['nazev']);
        $text = htmlspecialchars($article['text']);
        $query = $this->db->prepare('INSERT INTO prispevky (nazev,text,id_uzivatel) VALUES (:nazev,:text,:id)');
        $query->bindParam(':nazev',$nazev);
        $query->bindParam(':text',$text);
        $query->bindParam(':id',$id);
        $query->execute();

    }

    function yourEditedArticle(array $article, $param){
        $id = $_SESSION['user']['id_uzivatel'];
        $nazev = htmlspecialchars($article['nazev']);
        $text = htmlspecialchars($article['text']);
        $query = $this->db->prepare('UPDATE prispevky SET nazev = :nazev, text = :text 
                                     WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':nazev',$nazev);
        $query->bindParam(':text',$text);
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
    }

    function isYourArticle($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT nazev FROM prispevky WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    function yourDeletedFile($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('UPDATE prispevky SET soubor = NULL WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
    }

    function yourDeletedArticle($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('DELETE FROM prispevky WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id',$param);
        $query->bindParam(':id_u',$id);
        $query->execute();
    }

    function getFile($param){
        $param = htmlspecialchars($param);
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT soubor FROM prispevky WHERE id_prispevky = :id AND id_uzivatel = :id_u');
        $query->bindParam(':id_u',$id);
        $query->bindParam(':id',$param);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);}

    function isLastLocalFile($pole){
        $query = $this->db->prepare('SELECT nazev FROM prispevky WHERE soubor = :soubor');
        $query->bindParam(':soubor',$pole['soubor']);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);



    }

    function yourFileToArticle(array $article, $file){
        $nazev = htmlspecialchars($article['nazev']);
        $text = htmlspecialchars($article['text']);
        $query = $this->db->prepare('UPDATE prispevky SET soubor = :file WHERE nazev = :nazev AND text = :text');
        $query->bindParam(':file',$file);
        $query->bindParam(':nazev',$nazev);
        $query->bindParam(':text',$text);
        $query->execute();

    }



}



