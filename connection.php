<?php

namespace GAC\Test;

require_once "config.php";

function date_fr_to_us($date, $separateur = "-")
{
    $date_liste = explode("/", $date);
    $nouvelle_date = $date_liste[2] . $separateur . $date_liste[1] . $separateur . $date_liste[0];
    return $nouvelle_date;
}

function date_us_to_fr($date)
{
    return date_fr_to_us($date, "/");
}

function heure_to_datetime($heure)
{
    $heure_liste = explode($heure, ":");
    $t = new \DateTime();
    $t->setTime(\intval($heure_liste[0]), \intval($heure_liste[1]), \intval($heure_liste[2]));
    return $t->format('Y-m-d H:i:s');
}

class SingletonDB
{
    private $connection = null;
    private static $instance;

    public static function donner_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new SingletonDB();
        }

        if (self::$instance->test_etat_bdd()) {
            self::$instance->init_db();
        }

        return self::$instance;
    }

    public function init_db()
    {
        global $database;

        $this->test_etat_bdd(";dbname={$database}");

    }

    private function __construct()
    {

    }

    public function test_etat_bdd($extra_db = "")
    {

        global $adresse;
        global $database;
        global $nom;
        global $mdp;

        try {

            $this->connection = new \PDO('mysql:host=' . $adresse . $extra_db, $nom, $mdp);

            return true;
        } catch (\Exception $e) {
            print("\n<br/>\n<br/>\n<br/> connection  : mysql:host=" . $adresse . " " . $extra_db . " " . $nom . " " . $mdp . " \n<br/>\n<br/>\n<br/>");

            return false;
        }

    }

    public function creer_bdd()
    {
        global $database;
        global $table;
        $req = $this->connection->prepare("create database {$database}  DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_general_ci;");
        $req->execute();
        $req2 = <<<HEREDOC
        use  {$database};
        create table {$table}(
        id INT NOT NULL auto_increment,
        Compte_facturé INT,
        N_Facture INT ,
        N_abonné INT,
        Date DATE,
        Heure DATETIME,
        Durée_volume_réel FLOAT,
        Durée_volume_facturé FLOAT,
        Type VARCHAR(120),
        primary key (id));
HEREDOC;

        print_r($req2);
        $stmt = $this->connection->prepare($req2);
        $stmt->execute();
    }

    public function preparer_requete($string_requete = "", $param = [])
    {
        return $req_obj;
    }

    public function executer_requete($req_obj = null)
    {

    }

    public function executer_requete_insertion($param)
    {
        global $table;
        global $database;

        $insert_valid = false;
        //                                                                      0              1         2        3          4                 5                    6
        //      $stmt = $this->connection->prepare("use {$database};insert into {$table} (Compte_facturé,N_Facture,N_abonné,Date,Heure,Durée_volume_réel,Durée_volume_facturé,Type) VALUES(?,?,?,'?','?','?','?','?');");

        $param[3] = date_fr_to_us($param[3]);
        $param[4] = heure_to_datetime($param[4]);
        $stmt = $this->connection->prepare("use {$database};insert into {$table} (Compte_facturé,N_Facture,N_abonné,Date,Heure,Durée_volume_réel,Durée_volume_facturé,Type) VALUES(     {$param[0]} ,{$param[1]},{$param[2]},'{$param[3]}','{$param[4]}','{$param[5]}','{$param[6]}','{$param[7]}');");

        if ($stmt->execute($param)) {
            $insert_valid = true;
        }

        return $insert_valid;
    }

    public function contient_rien()
    {
        global $table;
        global $database;

        $retour_valide = false;

        $requete = <<<HEREDOC
        use {$database};
        SELECT  COUNT(*)
        FROM {$table}  ;
HEREDOC;

        $request = $this->connection->prepare($requete);

        $stmt_resultat = $request->execute();
        if (intval($request->fetch(\PDO::FETCH_ASSOC)) > 1) {
            $retour_valide = true;
        }

        return !$retour_valide;
    }

    public function executer_requete_nombre_appel()
    {
        //1
        global $table;
        global $database;
        $date = date_us_to_fr('15/02/2012');
        $requete = <<<HEREDOC
        SELECT  COUNT(*)
        FROM {$table}
        WHERE  Date >= '{$date}' AND type LIKE '%appel%' AND type  NOT LIKE '%interne%' ;
HEREDOC;

        print("\n<br/>\n<br/>\n<br/>");
        print_r($requete);
        print("\n<br/>\n<br/>\n<br/>");

        $request = $this->connection->prepare($requete);

        $stmt_resultat = $request->execute();
        return $request->fetchAll()[0][0];
    }

    public function executer_requete_top_data_facturé()
    {
        //2
        global $table;
        global $database;

        $requete = <<<HEREDOC
        SELECT  SUM(gac_table.Durée_volume_réel)
        FROM {$table}
        INNER JOIN  {$table}  as ng ON   ng.N_abonné = {$table}.N_abonné
        INNER JOIN  {$table}  as ng2 ON   ng2.N_Facture = ng.N_Facture
        WHERE HOUR(ng2.Heure) > 8 and HOUR(ng2.Heure) < 18
        ORDER BY gac_table.Durée_volume_réel DESC
        LIMIT 10;
HEREDOC;
        print("\n<br/>\n<br/>\n<br/>");
        print_r($requete);
        print("\n<br/>\n<br/>\n<br/>");
        $request = $this->connection->prepare($requete);

        $stmt_resultat = $request->execute();
        return $request->fetchAll()[0][0];
    }

    public function executer_requete_total_sms()
    {
        //3
        global $table;
        global $database;

        $requete = <<< HEREDOC
        SELECT  N_abonné,COUNT(*)
        FROM {$table}
        WHERE type LIKE "%envoi de sms depuis le mobile%"
        GROUP BY N_abonné;
HEREDOC;

        print("\n<br/>\n<br/>\n<br/>");
        print_r($requete);
        print("\n<br/>\n<br/>\n<br/>");

        $request = $this->connection->prepare($requete);

        $stmt_resultat = $request->execute();
        return $request->fetchAll();
    }

    public function executer_requete_total_sms_total()
    {
        //3bis
        global $table;
        global $database;

        $requete = <<< HEREDOC
        SELECT  COUNT(*)
        FROM {$table}
        WHERE type LIKE "%envoi de sms depuis le mobile%"
HEREDOC;

        print("\n<br/>\n<br/>\n<br/>");
        print_r($requete);
        print("\n<br/>\n<br/>\n<br/>");

        $request = $this->connection->prepare($requete);

        $stmt_resultat = $request->execute();
        return $request->fetchAll();
    }

    public function close_connection()
    {
        $this->connection = null;
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }
}
