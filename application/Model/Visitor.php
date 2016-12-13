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

 /*   function vypisUzivatele(){
        $query = $this->db->prepare('SELECT * FROM uzivatel');
        $query->execute();
        return $query->fetchAll();
    } */

    function compareEmails($email){
        $email = htmlspecialchars($email);
        $query = $this->db->prepare('SELECT email FROM uzivatel WHERE email= :email');
        $query->bindParam(':email', $email);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    function compareLogin($email, $heslo){
        $email = htmlspecialchars($email);
        $heslo = htmlspecialchars($heslo);
        $query = $this->db->prepare('SELECT email, heslo FROM uzivatel WHERE email= :email AND heslo = :heslo');
        $query->bindParam(':email', $email);
        $query->bindParam(':heslo', $heslo);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);

    }

    function addVisitorToDB(array $visitor){
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

    function getInfoAboutVisitor($email){
        $email = htmlspecialchars($email);
        $query = $this->db->prepare('SELECT * FROM uzivatel WHERE email= :email');
        $query->bindParam(':email', $email);
        $query->execute();
        return $query->fetch(\PDO::FETCH_ASSOC);
    }







}