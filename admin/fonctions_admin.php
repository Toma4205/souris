<?php
/* LISTE DES FONCTIONS

function addLogEvent
function getStatutJournee
function initializeJournee
function scrapMaxi
function setStatutMatch
function setScoreMatch
function getStatutMatch
function maj_table_live_buteur
function nettoyageTableButeurLive
function setButeurLive
function associer_buteur_live_joueur_reel

*/


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


function scrapMaxi($num_journee){
	// ------------ Récupération des données sur le web -----------------------
	$adresseMaxi = 'http://www.maxifoot.fr/resultat-ligue-1.htm';
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,$adresseMaxi);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
	$query = curl_exec($curl_handle);
	curl_close($curl_handle);
	
	$debutTableau = 'id=lastres';
	$pos1 = strstr($query, $debutTableau);
	$finTableau = 'Tous les r';
	$pos2 = strpos($pos1, $finTableau	);
	
	$journeeRecherchee=$num_journee;
	
	//Variables de l'algo
	$journee=0;
	$resultats; // Tableau contenant pour chaque ligne : ville_dom, nb_but_dom, nb_but_dom_sur_penalty, nb_but_ext, ville_ext, nb_but_ext_sur_penalty
	$buteurs; //Tableau contenant pour chaque ligne : ville, nom_buteur
	$nb_buteurs=0; 
	$i_match = 0;
	$statut = 0; //0 pas terminé et 1 terminé
	$equipeDOM;
	$equipeEXT;
	$tab_id_csc = array();
	
	
		$lignes = explode("&lt;tr", htmlspecialchars(substr($pos1,0,$pos2),ENT_HTML401,'ISO-8859-1', true));
		$i = 0;
		foreach ($lignes as $ligne) { 
			$j=0;
			//echo $ligne;
			$lignes1 = explode("&lt;td",$ligne);
			foreach ($lignes1 as $ligne1) { 
				$k=0;
				if($j == 4 || $j == 2){
					$nb_penalty = 0;
				}
				$lignes2 = explode("htm\"&gt;",$ligne1);
				foreach ($lignes2 as $ligne2) {
					$m=0;
					$lignes3 = explode("&lt;/a",$ligne2);
					foreach ($lignes3 as $ligne3) {
						$n=0;
						$lignes4 = explode("br&gt;&lt;span class",$ligne3);
						foreach ($lignes4 as $ligne4) {
							$p=0;
							$lignes5 = explode("&gt;",$ligne4);
							foreach ($lignes5 as $ligne5) {
								//Debug
								echo 'Ligne '.$i.'|'.$j.'|'.$k.'|'.$m.'|'.$n.'|'.$p.' '.$ligne5;
								echo "<br />\n";
								
								if($i>=1 && $j==2 && $k==0 && $m==0 && $n==0&& $p==1 && strpos($ligne5,"journ")>0){ //JOURNEE
									$journee = substr($ligne5,0,2);
									echo 'journee : '.$journee;
								}
								
								if($i>1 && $j==2 && $k==1 && $m==0 && $n==0&& $p==0 && $journee === $journeeRecherchee){ // EQUIPE DOM
									$resultats[$i_match][]=$ligne5;
									$equipeDOM = $ligne5;
									
								}
								
								if($i>1 && $j==2 && $k==1 && $m==1 && $n==1 && $p>=1 && $journee === $journeeRecherchee){ //CALCUL PENALTY DOM
									$pos = strpos($ligne5,", sp");
									$cibletexte = $ligne5;
									if ($pos === false) {
										//pas de penalty
										$pos = strpos($ligne5,", csc");
										if ($pos === false) {
											//pas de csc
										}else{
											$cibletexte = substr($ligne5,0,$pos);
										}
									}else{
										$nb_penalty++;
										$cibletexte = substr($ligne5,0,$pos);
										
									}
									$pos = strpos($ligne5,"'&lt;"); //COLLECTE BUTEUR
									$pos1 = strpos($ligne5,"', sp&lt;"); //COLLECTE BUTEUR penalty
									$pos2 = strpos($ligne5,"', csc&lt;"); //COLLECTE BUTEUR CSC
									if ($pos === false && $pos1 === false && $pos2 === false) {
										//pas de penalty
									}else{
										
										$posEspace = strrpos($cibletexte," "); 
										$lastRow = end($resultats);
										$buteurs[$nb_buteurs][0]= $lastRow[0];
																		
										$buteurs[$nb_buteurs][1]= substr($ligne5,0,$posEspace);
										if($pos1 === false){
											$buteurs[$nb_buteurs][2]= 0; //but sans penalty
										}else{
											$buteurs[$nb_buteurs][2]= 1; //but sur penalty
										}
										if($pos2 === false){
											$buteurs[$nb_buteurs][3]= 0; //but sans csc
											$buteurs[$nb_buteurs][4] = $journee;
											$buteurs[$nb_buteurs][5] = $equipeDOM;
										}else{
											$buteurs[$nb_buteurs][3]= 1; //but sur csc
											$buteurs[$nb_buteurs][4] = $journee;
											$tab_id_csc[] = $nb_buteurs; // On garde le numéro de ligne du joueur pour mettre son equipeEXT
										}									
										$nb_buteurs++;
									}	
									
								}
								
								if($i>1 && $j==4 && $k==1 && $m==1 && $n==1 && $p>=1 && $journee === $journeeRecherchee){ //CALCUL PENALTY EXT
									$pos = strpos($ligne5, ", sp");
									$cibletexte = $ligne5;
									if ($pos === false) {
										//pas de penalty
										$pos = strpos($ligne5,", csc");
										if ($pos === false) {
											//pas de csc
										}else{
											$cibletexte = substr($ligne5,0,$pos);
										}
									}else{
										$nb_penalty++;
										$cibletexte = substr($ligne5,0,$pos);
									}
									$posSpan = strpos($ligne5, "span");
									if ($posSpan === false) {
										//pas de penalty
									}else{
										$resultats[$i_match][]=$nb_penalty;
										
									}
									$pos = strpos($ligne5,"'&lt;"); //COLLECTE BUTEUR
									$pos1 = strpos($ligne5,"', sp&lt;"); //COLLECTE BUTEUR penalty
									$pos2 = strpos($ligne5,"', csc&lt;"); //COLLECTE BUTEUR CSC
									if ($pos === false && $pos1 === false && $pos2 === false) {
										//pas de penalty
									}else{
										$posEspace = strrpos($cibletexte," "); 
										$lastRow = end($resultats);
										$buteurs[$nb_buteurs][0]= $lastRow[4];
										$buteurs[$nb_buteurs][1]= substr($ligne5,0,$posEspace);
										if($pos1 === false){
											$buteurs[$nb_buteurs][2]= 0; //but sans penalty
										}else{
											$buteurs[$nb_buteurs][2]= 1; //but sur penalty
										}
										if($pos2 === false){
											$buteurs[$nb_buteurs][3]= 0; //but sans csc
											$buteurs[$nb_buteurs][4] = $journee;
											$buteurs[$nb_buteurs][5] = $equipeEXT;
											//$tab_id_csc[] = $nb_buteurs; // On garde le numéro de ligne du joueur pour mettre son equipeEXT
											//echo 'AJOUT tab_id_csc';
										}else{
											$buteurs[$nb_buteurs][3]= 1; //but sur csc
											$buteurs[$nb_buteurs][4] = $journee;
											$buteurs[$nb_buteurs][5] = $equipeDOM;
										}
										
										$nb_buteurs++;
									}
								}
								
								if($i>1 && $j==3 && $k==1 && $m==0 && $n==0&& $p==0 && $journee === $journeeRecherchee){ //SCORE DOM et EXT (ajouts penalty DOM)
									$resultats[$i_match][]=substr($ligne5,0,1);
									$resultats[$i_match][]=$nb_penalty;
									$resultats[$i_match][]=substr($ligne5,-1);
									
								}
								if($i>1 && $j==4 && $k==1 && $m==0 && $n==0&& $p==0 && $journee === $journeeRecherchee){
									$resultats[$i_match][]=$ligne5;
									$equipeEXT = $ligne5;
									if(is_array($tab_id_csc)){
										foreach($tab_id_csc as $id_csc){
											$buteurs[$id_csc][5] = $equipeEXT;
										}
									}
									$tab_id_csc = null;									
								}							
								
								$p++;
							}
							$n++;
						}
						$m++;
					}
					$k++;
					
				}
				$j++;
			}
			$i++;
			if($n>1 && $journee === $journeeRecherchee){
				$resultats[$i_match][]=$journee;
				$resultats[$i_match][]=$statut;
				$i_match++;
			}			
		}
	
	
	foreach($resultats as $tab_resultat)
	{
		//VERIF STATUT
		if($tab_resultat[7] == 0){
			setScoreMatch($tab_resultat);
		}elseif($tab_resultat[7] == 1){
			//VERIF Le match vient de se terminer ?
			if(getStatutMatch($tab_resultat[0],$tab_resultat[6])==0)
			{
				setStatutMatch($tab_resultat[0],$tab_resultat[6],1);
			}
		}else{
			//Erreur
		}			
	}
	maj_table_live_buteur($buteurs);
}

//FONCTION POUR METTRE LE STATUT DU MATCH AU NUMERO 0 ou 1
function setStatutMatch($nom_ville_maxi_dom, $journee, $statut)
{
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
	$upd_statut_match=$bdd->prepare('UPDATE resultatsl1_reel rr, nomenclature_equipes_reelles ner SET rr.statut = 1 WHERE rr.equipeDomicile = ner.trigramme AND ner.ville_maxi = :ville_maxi AND SUBSTRING(rr.journee,5,2) = :journee AND rr.statut = :statut;');
	
	$upd_statut_match->execute(array('ville_maxi' => $nom_ville_maxi_dom, 'journee' => $journee, 'statut' => $statut));
	$upd_statut_match->closeCursor();

	addLogEvent('FONCTION setStatutMatch');
	addLogEvent('ville_maxi => '.$nom_ville_maxi_dom.', journee => '.$journee.', statut => '.$statut);
}

//RECUPERER LE STATUT D'UN MATCH
function getStatutMatch($nom_ville_maxi_dom, $journee)
{
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
	$statut = null;
	$req_statut_match=$bdd->prepare('SELECT rr.statut FROM resultatsl1_reel rr, nomenclature_equipes_reelles ner WHERE rr.equipeDomicile = ner.trigramme AND ner.ville_maxi = :ville_maxi AND SUBSTRING(rr.journee,5,2) = :journee;');
	
	$req_statut_match->execute(array('ville_maxi' => $nom_ville_maxi_dom, 'journee' => $journee));
	
	while ($statutsMatch = $req_statut_match->fetch())
	{
		$statut = $statutsMatch['statut'];
	}
	
	$req_statut_match->closeCursor();

	addLogEvent('FONCTION getStatutMatch');
	addLogEvent('ville_maxi => '.$nom_ville_maxi_dom.', journee => '.$journee);
	addLogEvent('STATUT => '.$statut);

	return $statut;
}


//A PARTIR DU TABLEAU DE SCRAP => REMPLIR LA TABLE resultatl1reel 
//FORMAT TABLEAU : NOM_VILLE_MAXI_DOM, NB_BUT_DOM, NB_PENALTY_DOM, NB_BUT_EXT, NOM_VILLE_MAXI_EXT, NB_PENALTY_EXT, JOURNEE, STATUT_MATCH
function setScoreMatch($tab_resultat){
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
	
	
	$upd_score_match=$bdd->prepare('UPDATE resultatsl1_reel rr, nomenclature_equipes_reelles ner SET rr.butDomicile = :butDomicile, rr.winOrLoseDomicile = :winOrLoseDomicile, rr.penaltyDomicile = :penaltyDomicile, rr.butVisiteur = :butVisiteur, rr.winOrLoseVisiteur = :winOrLoseVisiteur, rr.penaltyVisiteur = :penaltyVisiteur WHERE rr.equipeDomicile = ner.trigramme AND ner.ville_maxi = :ville_maxi AND SUBSTRING(rr.journee,5,2) = :journee AND rr.statut = 0;');
	
	if($tab_resultat[1]>$tab_resultat[3]){
		$statusDOM = 'W';
		$statusEXT = 'L';
	}elseif($tab_resultat[1]<$tab_resultat[3]){
		$statusDOM = 'L';
		$statusEXT = 'W';
	}else{
		$statusDOM = 'D';
		$statusEXT = 'D';
	}
	
	$upd_score_match->execute(array('butDomicile' => $tab_resultat[1], 'winOrLoseDomicile' => $statusDOM, 'penaltyDomicile' => $tab_resultat[2],'butVisiteur' => $tab_resultat[3],'winOrLoseVisiteur' => $statusEXT,'penaltyVisiteur' => $tab_resultat[5],'ville_maxi' => $tab_resultat[0],'journee' => $tab_resultat[6]));
	
	$upd_score_match->closeCursor();
	
	addLogEvent('FONCTION UPDATE SCORE EN BASE setScoreMatch');
	addLogEvent('butDomicile => '.$tab_resultat[1].', winOrLoseDomicile => '.$statusDOM.', penaltyDomicile => '.$tab_resultat[2].',butVisiteur => '.$tab_resultat[3].',winOrLoseVisiteur => '.$statusEXT.',penaltyVisiteur => '.$tab_resultat[5].',ville_maxi => '.$tab_resultat[0].',journee => '.$tab_resultat[6]);

}

//FONCTION DE MISE A JOUR DE LA TABLE DES BUTEURS EN LIVE
//FORMAT TABLEAU EN ENTREE : VILLE_MAXI, ID_JOUEUR_MAXI, IS_PENALTY, IS_CSC, JOURNEE
function maj_table_live_buteur($tab_buteurs){
	addLogEvent('FONCTION maj_table_live_buteur');
	
	//On vide la table
	nettoyageTableButeurLive();
	
	print_r($tab_buteurs);
	//On rempli la table avec les infos brutes
	foreach($tab_buteurs as $buteur){
		addLogEvent('INFO BRUTE : '.$buteur[0].' - '.$buteur[1].' PENAL? '.$buteur[2].' CSC? '.$buteur[3].' Journee : '.$buteur[4].' Pour l\'équipe : '.$buteur[5]);
		setButeurLive($buteur);
		associer_buteur_live_joueur_reel();
		//recuperer_buteur_sans_match
	}
	
}

function nettoyageTableButeurLive(){
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
	addLogEvent('FONCTION nettoyageTableButeurLive');
	$trc_nettoyageTableButeurLive=$bdd->prepare('TRUNCATE TABLE buteur_live_journee');
	$trc_nettoyageTableButeurLive->execute();
	$trc_nettoyageTableButeurLive->closeCursor();
}

//AJOUT EN BASE D'UN BUTEUR LIVE
//FORMAT TABLEAU EN ENTREE : VILLE_MAXI, ID_JOUEUR_MAXI, IS_PENALTY, IS_CSC, JOURNEE
function setButeurLive($buteur){
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
	$ins_buteur_live=$bdd->prepare('INSERT INTO buteur_live_journee (
									journee,
									id_joueur_maxi,
									ville_maxi,
									sur_penalty,
									sur_csc)
									VALUES (
									:journee,
									:id_joueur_maxi,
									:ville_maxi,
									:sur_penalty,
									:sur_csc)');

	if($buteur[3] == 1){
		$ins_buteur_live->execute(array('journee' => $buteur[4], 'id_joueur_maxi' => $buteur[1], 'ville_maxi' => $buteur[5], 'sur_penalty' => $buteur[2], 'sur_csc' => $buteur[3]));
	}else{
		$ins_buteur_live->execute(array('journee' => $buteur[4], 'id_joueur_maxi' => $buteur[1], 'ville_maxi' => $buteur[0], 'sur_penalty' => $buteur[2], 'sur_csc' => $buteur[3]));
	}
	
	$ins_buteur_live->closeCursor();
	addLogEvent('FONCTION setButeurLive');
}

//Fait matcher les buteurs_reels avec les id_reel déjà en table
function associer_buteur_live_joueur_reel(){
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
	
	$upd_matching_buteur_id_reel=$bdd->prepare('UPDATE buteur_live_journee blj, joueur_reel jr, nomenclature_equipes_reelles ner
												SET blj.id_joueur_reel = jr.id 
												WHERE blj.id_joueur_reel IS NULL AND jr.equipe = ner.trigramme AND ner.ville_maxi = blj.ville_maxi AND (LOCATE(TRIM(jr.nom),TRIM(blj.id_joueur_maxi))>0 OR LOCATE(RIGHT(TRIM(blj.id_joueur_maxi),4),TRIM(jr.nom))>0)');
	
	$upd_matching_buteur_id_reel->execute();
	$upd_matching_buteur_id_reel->closeCursor();
	addLogEvent('FONCTION associer_buteur_live_joueur_reel');
}


?>