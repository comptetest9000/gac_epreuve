<?php
namespace GAC\Test;

require_once "connection.php";

$BDD = SingletonDB::donner_instance();

$BDD->creer_bdd();
//////////////////////////////////////////////////

$debug = function () {
    global $nombre_insertion;
    if (file_exists("compte")) {
        unlink("compte");
    }
    file_put_contents("compte", $nombre_insertion);
    return print("\n\n\n\n nombre insertion : " . $nombre_insertion . "\n\n\n\n");};
register_shutdown_function($debug);
$BDD = SingletonDB::donner_instance();

$fichier_csv = glob("*.csv")[0];
$fichier = fopen($fichier_csv, "r");

global $nombre_insertion;
$nombre_insertion = 0;
if (file_exists("compte")) {
    $nombre_insertion = intval(file_get_contents("compte"));
    fseek($fichier, $nombre_insertion);
}

$ligne = null;
//debut inutile
fgets($fichier);
fgets($fichier);
//$BDD->creer_bdd();
\ob_start();
if ($BDD->contient_rien()) {
    $BDD->beginTransaction();

    while (($ligne = fgets($fichier)) !== false) {
        $token_membre = \explode(";", $ligne);
        print_r($token_membre);

        $BDD->executer_requete_insertion($token_membre);
        $nombre_insertion++;
        print($nombre_insertion);

    }
    $BDD->commit();

} else {
    $BDD->creer_bdd();
}

\ob_get_contents();

$BDD->close_connection();
