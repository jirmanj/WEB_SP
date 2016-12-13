<?php
namespace Mini\Model;
use Mini\Core\Model;
/**
 * Class UserManager
 * @package Mini\Model
 *
 *
 */
class Admin extends Model
{

    function getAuthorOfArticle(){
        $query = $this->db->prepare('SELECT p.id_prispevky, p.nazev,concat(u.jmeno, \' \', u.prijmeni) AS autor
                                      FROM uzivatel u, prispevky p
                                      WHERE u.id_uzivatel = p.id_uzivatel AND p.posouzeno IS NULL
                                      ORDER BY p.id_prispevky ASC');
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    function getReviewers(){

        $query = $this->db->prepare('SELECT id_uzivatel,jmeno,prijmeni,concat(jmeno, \' \',prijmeni) AS cele_jmeno
                                      FROM uzivatel
                                      WHERE hodnost = 2');
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    function getReviews($prispevek){
        $id = htmlspecialchars($prispevek['id_prispevky']);
        $query = $this->db->prepare('SELECT h.id_hodnoceni, concat(u.jmeno, \' \', u.prijmeni) AS recenzent,p.id_prispevky,
                                      h.originalita,h.tema,h.tech_kvalita,h.jazyk_kvalita, h.doporuceni
                                      FROM hodnoceni h, prispevky p, uzivatel u
                                      WHERE :id = p.id_prispevky AND p.id_prispevky = h.id_prispevky AND u.id_uzivatel 
                                      = h.id_uzivatel 
                                      ORDER BY h.id_prispevky');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    function setNewReview($new_review, $id){
        $id_p = htmlspecialchars($id);
        $id_u = $new_review['id'];
        $query = $this->db->prepare('INSERT INTO hodnoceni (id_uzivatel,id_prispevky) VALUES (:id_u,:id_p)');
        $query->bindParam(':id_p',$id_p);
        $query->bindParam(':id_u',$id_u);
        $query->execute();
    }

    function deleteReview($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('DELETE FROM hodnoceni WHERE id_hodnoceni= :id OR id_prispevky= :id');
        $query->bindParam(':id',$id);
        $query->execute();
    }

    function rejectArticle($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('UPDATE prispevky SET posouzeno = 0 WHERE id_prispevky = :id');
        $query->bindParam(':id',$id);
        $query->execute();

    }



    function getSmallReview($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('SELECT COUNT(id_uzivatel) AS soucet_uzivatelu, SUM((originalita+tema+tech_kvalita+jazyk_kvalita+doporuceni)/5) AS soucet_hodnoceni
                                     FROM hodnoceni WHERE id_prispevky = :id AND doporuceni IS NOT NULL');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    function setAVG($id, $prumer){
        $id = htmlspecialchars($id);
        $prumer = htmlspecialchars($prumer);
        $query = $this->db->prepare('UPDATE prispevky SET posouzeno = :prumer WHERE id_prispevky = :id');
        $query->bindParam(':id',$id);
        $query->bindParam(':prumer',$prumer);
        $query->execute();

    }

    function getOtherUsers(){
        $query = $this->db->prepare('SELECT jmeno, prijmeni, hodnost
                                      FROM  uzivatel
                                      WHERE  hodnost >= 2
                                      ORDER BY hodnost DESC ');
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
 /*  function getAll(){
       $query = $this->db->prepare('SELECT
                                    p.id_uzivatel,p.nazev,h.originalita,h.tema,h.tech_kvalita,h.jazyk_kvalita, h.doporuceni
                                    
                                    FROM hodnoceni h RIGHT JOIN prispevky p 
                                    ON p.id_prispevky = h.id_prispevky
                                    ORDER BY p.id_prispevky ASC
                                    ');
       $query->execute();
       return $query->fetchAll(\PDO::FETCH_ASSOC);

   } */


  /*  function getReviewsForArticle($id_prispevky){
        $query = $this->db->prepare('SELECT u.jmeno, u.prijmeni, concat(u.jmeno, \' \', u.prijmeni) AS recenzent
                                      FROM uzivatel u, prispevky p
                                      WHERE p.id_prispevky = :id
                                      AND u.hodnost = 2
                                      AND u.id_uzivatel NOT IN (SELECT h.id_uzivatel FROM hodnoceni h WHERE :id = h.id_prispevky);
                                      ORDER BY p.nazev ASC');
        $query->bindParam(':id',$id_prispevky);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);

    }
    function getTry($id_prispevky){
        $query = $this->db->prepare('SELECT h.id_uzivatel FROM hodnoceni h WHERE :id = h.id_prispevky');
        $query->bindParam(':id',$id_prispevky);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    } */

}