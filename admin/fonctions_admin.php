<?php

//Fonction d'écriture des logs dans un fichier
function addLogEvent($event)
{
    date_default_timezone_set('Europe/Paris');
	$time = date("D, d M Y H:i:s");
    $time = "[".$time."] ";
	
	
	$year_month = date("YF");
	$fichier = __DIR__ . '\\logs\\'.$year_month.'.log';
	
    $event = $time.$event."\n";
 
    file_put_contents($fichier, $event, FILE_APPEND);
}

//Retourne le statut d'une journée selon la table calendrier_reel
function getStatutJournee($num_journee){
	require_once(__DIR__ . '/../modele/connexionSQL.php');
	try
	{
		// Récupération de la connexion
		$bdd = ConnexionBDD::getInstance();
	}
	catch (Exception $e)
	{
		die('Erreur : ' . $e->getMessage());
		echo $e;
	}
	$reqStatutJournee = $bdd->prepare('SELECT statut FROM calendrier_reel WHERE num_journee = :num_journee');
	$reqStatutJournee->execute(array('num_journee' => $num_journee));

	while ($statutJournee = $reqStatutJournee->fetch())
	{
		$statut = $statutJournee['statut'];
	}
	
	$reqStatutJournee->closeCursor();
	return $statut;
}

//Passage du statut d'une journée de 0 à 1 dans calendrier réel
//Passage du score à 0 pour calendrier ligue sur cette meme journée
function initializeJournee($num_journee){
	require_once(__DIR__ . '/../modele/connexionSQL.php');
	try
	{
		// Récupération de la connexion
		$bdd = ConnexionBDD::getInstance();
	}
	catch (Exception $e)
	{
		die('Erreur : ' . $e->getMessage());
		echo $e;
	}
	
	$upd_initializeStatutJournee=$bdd->prepare('UPDATE calendrier_reel SET statut = 1 WHERE num_journee = :num_journee;');
	$upd_initializeScoreJournee=$bdd->prepare('UPDATE calendrier_ligue SET score_dom = 0, score_ext = 0 WHERE num_journee_cal_reel = :num_journee;');

	$upd_initializeStatutJournee->execute(array('num_journee' => $num_journee));
	$upd_initializeStatutJournee->closeCursor();
	addLogEvent('Initialisation du statut à 1 pour la journée '.$num_journee);
	
	$upd_initializeScoreJournee->execute(array('num_journee' => $num_journee));
	$upd_initializeScoreJournee->closeCursor();
	addLogEvent('Initialisation des scores à 0 pour la journée '.$num_journee);
}


function getAutoResultatsJournee($num_journee){
	
	
	
}


?>