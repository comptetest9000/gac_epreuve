<?php
namespace GAC\Test;

require_once "connection.php";

$BDD = SingletonDB::donner_instance();

echo "<hr> ";
//test des requetes demandés !
echo "1.nombre Retrouver la durée totale réelle des appels effectués après le 16/02/2012 (inclus) .  ??? \n:  [" . $BDD->executer_requete_nombre_appel() . " ] \n\n";

echo "<hr> ";
echo "2.Retrouver le TOP 10 des volumes data facturés en dehors de la tranche horaire 8h00-18h00, par abonné ??? \n: " . $BDD->executer_requete_top_data_facturé() . "\n\n";

echo "<hr> ";
$res = $BDD->executer_requete_total_sms();
echo "3.Retrouver la quantité totale de SMS envoyés par l'ensemble des abonnés ??? \n: " . count($BDD->executer_requete_total_sms()) . "\n\n";
echo "<table>";
foreach ($res as $ligne) {
    echo "<tr>";
    echo "<td>" . $ligne[0] . "</td><td>" . $ligne[1] . "</td>";
    echo "</tr>";
}
echo "</table>";
$BDD->close_connection();
