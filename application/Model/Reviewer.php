<?php
namespace Mini\Model;
use Mini\Core\Model;
/**
 * Class UserManager
 * @package Mini\Model
 *
 *
 */
class Reviewer extends Model
{
    /***********************************************************
     * Funkce získá všechny články, které nebyli ohodnocený a jsou pro daného recenzenta
     * @return pole výsledků
     */
    function getYourWork(){
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT h.id_hodnoceni, p.nazev
                                      FROM  prispevky p, hodnoceni h, uzivatel u
                                      WHERE h.id_prispevky = p.id_prispevky
                                      AND   h.id_uzivatel = :id
                                      AND   u.id_uzivatel = h.id_uzivatel
                                      AND   h.doporuceni IS NULL
                                      ORDER BY p.nazev ASC');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce získá konkrétní článek
     * @param $param id hodnocení
     * @return pole výsledku
     */
    function getAuthorsPost($param){
        $id_hodnoceni = $param;
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT h.id_hodnoceni, p.nazev, p.text FROM prispevky p, hodnoceni h
                                     WHERE p.id_prispevky = h.id_prispevky AND :id = h.id_uzivatel AND :id_c = h.id_hodnoceni');
        $query->bindParam(':id',$id);
        $query->bindParam(':id_c',$id_hodnoceni);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Uložení nové rezence
     * @param $new_review pole potřebných atributů nové recenze
     * @param $param id hodnoceni
     */
    function setReview($new_review, $param){
        $param = htmlspecialchars($param);
        $originalita = htmlspecialchars($new_review['originalita']);
        $tema = htmlspecialchars($new_review['tema']);
        $tech_kvalita = htmlspecialchars($new_review['tech_kvalita']);
        $jazyk_kvalita = htmlspecialchars($new_review['jazyk_kvalita']);
        $doporuceni = htmlspecialchars($new_review['doporuceni']);
        if(empty($new_review['poznamka'])){
            $query = $this->db->prepare('UPDATE hodnoceni SET originalita = :originalita, tema = :tema
                                    ,tech_kvalita = :tech_kvalita, jazyk_kvalita = :jazyk_kvalita
                                    , doporuceni = :doporuceni WHERE id_hodnoceni = :id');
        }else{
            $query = $this->db->prepare('UPDATE hodnoceni SET originalita = :originalita, tema = :tema
                                    ,tech_kvalita = :tech_kvalita, jazyk_kvalita = :jazyk_kvalita
                                    , doporuceni = :doporuceni, poznamka = :poznamka WHERE id_hodnoceni = :id');
            $poznamka = htmlspecialchars($new_review['poznamka']);
            $query->bindParam(':poznamka',$poznamka);
        }
        $query->bindParam(':originalita',$originalita);
        $query->bindParam(':tema',$tema);
        $query->bindParam(':tech_kvalita',$tech_kvalita);
        $query->bindParam(':jazyk_kvalita',$jazyk_kvalita);
        $query->bindParam(':doporuceni',$doporuceni);
        $query->bindParam(':id',$param);
        $query->execute();


    }

    /***********************************************************
     * Získání doposud ohodnocených článků, avšak adminem nepotvrzených
     * @return pole výsledků
     */
    function getYourReviews(){
        $id = $_SESSION['user']['id_uzivatel'];
        $query = $this->db->prepare('SELECT h.id_hodnoceni, p.nazev
                                      FROM  prispevky p, hodnoceni h, uzivatel u
                                      WHERE h.id_prispevky = p.id_prispevky
                                      AND   h.id_uzivatel = :id
                                      AND   u.id_uzivatel = h.id_uzivatel
                                      AND   h.doporuceni IS NOT NULL
                                      AND   p.posouzeno IS NULL
                                      AND   p.id_uzivatel IS NOT NULL
                                      ORDER BY p.nazev ASC');
        $query->bindParam(':id',$id);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

}