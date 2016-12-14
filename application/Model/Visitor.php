<?php
namespace Mini\Model;
use Mini\Core\Model;
/**
 * Class UserManager
 * @package Mini\Model
 *
 *
 */
class Visitor extends Model
{

    /***********************************************************
     * Funkce najde email v tabulce pro daného uživatele - vrati email nebo nic.
     * @param $email email
     * @return pole vysledku
     */
    function compareEmails($email){
        $email = htmlspecialchars($email);
        $query = $this->db->prepare('SELECT email FROM uzivatel WHERE email= :email');
        $query->bindParam(':email', $email);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce najde email a heslo v tabulce pro daného uživatele - vrátí heslo a email, nebo nic.
     * @param $email email
     * @param $heslo heslo
     * @return mixed pole vysledku
     */
    function compareLogin($email, $heslo){
        $email = htmlspecialchars($email);
        $heslo = htmlspecialchars($heslo);
        $query = $this->db->prepare('SELECT email, heslo FROM uzivatel WHERE email= :email AND heslo = :heslo');
        $query->bindParam(':email', $email);
        $query->bindParam(':heslo', $heslo);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);

    }

    /***********************************************************
     * Funkce vloží uživatele do databáze.
     * @param $visitor pole potřebných atributů
     */
    function addVisitorToDB($visitor){
        $jmeno = htmlspecialchars($visitor['jmeno']);
        $prijmeni = htmlspecialchars($visitor['prijmeni']);
        $email = htmlspecialchars($visitor['email']);
        $heslo = htmlspecialchars($visitor['heslo']);
        $hodnost = 3;
        $query = $this->db->prepare('INSERT INTO uzivatel (jmeno,prijmeni,email,heslo,hodnost) VALUES (:jmeno,:prijmeni,:email,:heslo,:hodnost)');
        $query->bindParam(':jmeno',$jmeno);
        $query->bindParam(':prijmeni',$prijmeni);
        $query->bindParam(':email',$email);
        $query->bindParam(':heslo',$heslo);
        $query->bindParam(':hodnost',$hodnost);
        $query->execute();
    }

    /***********************************************************
     * Funkce vrátí informace o jednom daném uživateli
     * @param $email email
     * @return pole vysledku
     */
    function getInfoAboutVisitor($email){
        $email = htmlspecialchars($email);
        $query = $this->db->prepare('SELECT * FROM uzivatel WHERE email= :email');
        $query->bindParam(':email', $email);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /***********************************************************
     * Funkce vratí hodnost uzivatele, kterou momentálně má
     * @param $visitors_email email uživatele
     * @return pole výsledků
     */
    function getRank($email){
        $email = htmlspecialchars($email);
        $query = $this->db->prepare('SELECT hodnost FROM uzivatel WHERE email= :email');
        $query->bindParam(':email', $email);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }






}