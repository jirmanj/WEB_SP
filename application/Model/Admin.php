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
    /***********************************************************
     * Funkce získa autory zatím neposouzených článku
     * @return pole výsledků
     */
    function getAuthorOfArticle(){
        $query = $this->db->prepare('SELECT p.id_prispevky, p.nazev,concat(u.jmeno, \' \', u.prijmeni) AS autor
                                      FROM uzivatel u, prispevky p
                                      WHERE u.id_uzivatel = p.id_uzivatel AND p.posouzeno IS NULL
                                      ORDER BY p.id_prispevky ASC');
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce vrátí pole recenzentů.
     * @return pole výsledků
     */
    function getReviewers(){

        $query = $this->db->prepare('SELECT id_uzivatel,jmeno,prijmeni,concat(jmeno, \' \',prijmeni) AS cele_jmeno
                                      FROM uzivatel
                                      WHERE hodnost = 2');
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce získá všechny recenze i s hodnocením
     * @param $prispevek pole potřebných atributů
     * @return pole výsledků
     */
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

    /***********************************************************
     * Zapíše novou recenzi
     * @param $new_review pole s potřebnými atributy
     * @param $id id článku (id_prispevky)
     */
    function setNewReview($new_review, $id){
        $id_p = htmlspecialchars($id);
        $id_u = htmlspecialchars($new_review['id']);
        $query = $this->db->prepare('INSERT INTO hodnoceni (id_uzivatel,id_prispevky) VALUES (:id_u,:id_p)');
        $query->bindParam(':id_p',$id_p);
        $query->bindParam(':id_u',$id_u);
        $query->execute();
    }

    /***********************************************************
     * Funkce odstraní rezenci
     * @param $id id hodnoceni
     */
    function deleteReview($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('DELETE FROM hodnoceni WHERE id_hodnoceni= :id OR id_prispevky= :id');
        $query->bindParam(':id',$id);
        $query->execute();
    }

    /***********************************************************
     * Funkce zamítne článek
     * @param $id idi článku (id_prispevky)
     */
    function rejectArticle($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('UPDATE prispevky SET posouzeno = 0 WHERE id_prispevky = :id');
        $query->bindParam(':id',$id);
        $query->execute();

    }


    /***********************************************************
     * Funkce získá potřebná čísla pro další práci (průmer)
     * @param $id id článku (id_prispevky)
     * @return pole výsledků
     */
    function getSmallReview($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('SELECT COUNT(id_uzivatel) AS soucet_uzivatelu, SUM((originalita+tema+tech_kvalita+jazyk_kvalita+doporuceni)/5) AS soucet_hodnoceni
                                     FROM hodnoceni WHERE id_prispevky = :id AND doporuceni IS NOT NULL');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce nastaví průměr
     * @param $id id článku (id_příspěvky)
     * @param $prumer průměr
     */
    function setAVG($id, $prumer){
        $id = htmlspecialchars($id);
        $prumer = htmlspecialchars($prumer);
        $query = $this->db->prepare('UPDATE prispevky SET posouzeno = :prumer WHERE id_prispevky = :id');
        $query->bindParam(':id',$id);
        $query->bindParam(':prumer',$prumer);
        $query->execute();

    }

    /***********************************************************
     * Získá všechny uživatele, kteří jsou hodností pod ním
     * @return pole výsledků
     */
    function getOtherUsers(){
        $query = $this->db->prepare('SELECT id_uzivatel, jmeno, prijmeni, hodnost
                                      FROM  uzivatel
                                      WHERE  hodnost >= 2
                                      ORDER BY hodnost DESC ');
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce změní konkrétnímu uživateli hodnost
     * @param $cislo číslo hodnosti
     * @param $id id uživatele
     */
    function changeRank($cislo,$id){
        $id = htmlspecialchars($id);
        $cislo = htmlspecialchars($cislo);
        $query = $this->db->prepare('UPDATE uzivatel SET hodnost = :hodnost WHERE id_uzivatel = :id');
        $query->bindParam(':id',$id);
        $query->bindParam(':hodnost',$cislo);
        $query->execute();
    }

    /***********************************************************
     * Funkce odstraní uživatele + s ním příspěvky a hodnocení
     * @param $id id uživatele
     */
    function deleteUser($id){
        $id = htmlspecialchars($id);
        $query = $this->db->prepare('SELECT id_prispevky FROM prispevky WHERE id_uzivatel= :id');
        $query->bindParam(':id',$id);
        $query->execute();
        $arr = $query->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($arr as $item) {
            $query = $this->db->prepare('DELETE FROM hodnoceni WHERE id_prispevky= :id');
            $query->bindParam(':id',$item['id_prispevky']);
            $query->execute();
        }
        $query = $this->db->prepare('DELETE FROM uzivatel WHERE id_uzivatel= :id');
        $query->bindParam(':id',$id);
        $query->execute();
        $query = $this->db->prepare('DELETE FROM prispevky WHERE id_uzivatel= :id');
        $query->bindParam(':id',$id);
        $query->execute();
    }



}