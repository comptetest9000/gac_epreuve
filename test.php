<?php
require_once "config.php";

global $adresse;
global $database;
global $nom;
global $mdp;

$extra_db = ";dbname=" . $database;
print("\n\n\n connection  : mysql:host=" . $adresse . " " . $extra_db . " " . $nom . " " . $mdp . " \n\n\n");

$test = new \PDO('mysql:host=127.0.0.1;dbname=gac', 'root', 'a');
$test = new \PDO('mysql:host=${adresse}' . $extra_db, $nom, $mdp);

print("\n\n\n connection  : mysql:host=" . $adresse . " " . $extra_db . " " . $nom . " " . $mdp . " \n\n\n");
