<?php
require_once('modele/connexionSQL.php');

try
{
	// Récupération de la connexion
	$bdd = ConnexionBDD::getInstance();
}
catch (Exception $e)
{
        die('Erreur : ' . $e->getMessage());
}


$reponse = $bdd->query('SELECT * FROM coach');

while ($donnees = $reponse->fetch())
{
	echo $donnees['id'];
	echo ' ';
	echo $donnees['nom'];
	echo ' ';
	echo $donnees['mail'];
	echo ' // ';
}	

?>