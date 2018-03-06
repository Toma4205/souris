<?php
/* LISTE DES FONCTIONS

function addLogEvent
function getStatutJournee
function setStatutJournee
function initializeJournee
function scrapMaxi
function setStatutMatch
function setScoreMatch
function getStatutMatchMaxi
function getStatutMatch
function maj_table_live_buteur
function nettoyageTableButeurLive
function setButeurLive
function associer_buteur_live_joueur_reel
function afficher_log_buteur_sans_matching
function get_nb_match_termine_par_journee
function annuler_match_restants
function scrapRoto
function login
function grab_page
function recursive_array_search
function butsEncaissesSanSPenalty
function ecartScore
function testVictoire
function isCleanSheet
function isCleanSheetNul
function buildTableauJournee
function is_Fichier_Roto_A_Telecharger
function set_statut_match_termine_journee
function nettoyageFichierStat
function get_journee_format_long
function get_csv_from_roto
function calculer_notes_joueurs

*/


//Fonction d'écriture des logs dans un fichier
function addLogEvent($event)
{
    date_default_timezone_set('Europe/Paris');
	$time = date("D, d M Y H:i:s");
    $time = "[".$time."] ";
	
	
	$year_month = date("YF");
	$fichier = __DIR__ . '/logs/'.$year_month.'.log';
	
    $event = $time.$event."\n";
 
    file_put_contents($fichier, $event, FILE_APPEND);
}

//Retourne la journee au format YYYYJJ
function get_journee_format_long($journee_short)
{
	addLogEvent('FUNCTION get_journee_format_long');
	date_default_timezone_set('Europe/Paris');
	if(date('n') > 6){
		return date("Y").$journee_short;
	}else{
		return (date("Y")-1).$journee_short;
	}
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
	addLogEvent('FUNCTION getStatutJournee');
	return $statut;
}

//Retourne le statut d'une journée selon la table calendrier_reel
function setStatutJournee($num_journee,$statut){
	addLogEvent('FUNCTION setStatutJournee');
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
	$updStatutJournee = $bdd->prepare('UPDATE calendrier_reel SET statut = :statut WHERE num_journee = :num_journee');
	$updStatutJournee->execute(array('num_journee' => $num_journee, 'statut' => $statut));
	
	$updStatutJournee->closeCursor();
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
	addLogEvent('FUNCTION scrapMaxi');
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
	$resultats = array(); // Tableau contenant pour chaque ligne : ville_dom, nb_but_dom, nb_but_dom_sur_penalty, nb_but_ext, ville_ext, nb_but_ext_sur_penalty
	$buteurs; //Tableau contenant pour chaque ligne : ville, nom_buteur
	$nb_buteurs=0; 
	$i_match = 0;
	$statut = 1; //0 pas terminé et 1 terminé
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
								//addLogEvent('Ligne '.$i.'|'.$j.'|'.$k.'|'.$m.'|'.$n.'|'.$p.' '.$ligne5);
								
								if($i>=1 && $j==2 && $k==0 && $m==0 && $n==0&& $p==1 && strpos($ligne5,"journ")>0){ //JOURNEE
									$journee = substr($ligne5,0,2);
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
	
	
	//Dernier résultat ajouté
	if(!empty($resultats))
	{
		$resultats[$i_match][]=$journeeRecherchee;
		$resultats[$i_match][]=$statut;
	}else{
		addLogEvent('Aucun résultat web MAXI pour la journee '.$journeeRecherchee);
	}
	$time_fin_dernier_match = null;
	foreach($resultats as $tab_resultat)
	{
		addLogEvent('scrapMaxi // '.$tab_resultat[7]);
		if($tab_resultat[7] == 1){
			//VERIF Le match vient de se terminer ?
			addLogEvent('scrapMaxi // condition '.$tab_resultat[0].' et '.$tab_resultat[6]);
			if(getStatutMatchMaxi($tab_resultat[0],$tab_resultat[6])==0){
			
				addLogEvent('scrapMaxi // condition OK');
				setScoreMatch($tab_resultat);
				setStatutMatch($tab_resultat[0],$tab_resultat[6],strtotime("now"),0);
				$time_fin_dernier_match = strtotime("now");
			}
		}			
	}
	//Inutile pour l'instant
	//maj_table_live_buteur($buteurs);
	
	return $time_fin_dernier_match;
}

//
function is_Fichier_Roto_A_Telecharger($journee)
{
	addLogEvent('FONCTION is_Fichier_Roto_A_Telecharger');
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
	$req_matchs_termines_depuis_longtemps=$bdd->prepare('SELECT count(*) AS \'nb_match\' FROM resultatsl1_reel WHERE UNIX_TIMESTAMP()-statut > 600 AND statut > 3 AND SUBSTRING(journee,5,2) = :journee;');
	
	$req_matchs_termines_depuis_longtemps->execute(array('journee' => $journee));
	
	
	while ($nb_match = $req_matchs_termines_depuis_longtemps->fetch())
	{
		if($nb_match['nb_match'] > 0)
		{
			return true;
		}else{
			return false;
		}
	}
	$req_matchs_termines_depuis_longtemps->closeCursor();
}

//FONCTION POUR METTRE LE STATUT DU MATCH AU NUMERO 0 , strtotime, 1, 2 ou 3
//ex_statut prend : 0, 1, 2, 3 ou 4 si on fait référence au strtotime
function setStatutMatch($nom_ville_maxi_dom, $journee, $statut, $ex_statut)
{
	addLogEvent('FONCTION setStatutMatch');
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
	
	if($ex_statut<4)
	{
		$upd_statut_match=$bdd->prepare('UPDATE resultatsl1_reel rr, nomenclature_equipes_reelles ner SET rr.statut = :statut WHERE rr.equipeDomicile = ner.trigramme AND ner.ville_maxi = :ville_maxi AND SUBSTRING(rr.journee,5,2) = :journee AND rr.statut = :ex_statut;');
		$upd_statut_match->execute(array('ville_maxi' => $nom_ville_maxi_dom, 'journee' => $journee, 'statut' => $statut, 'ex_statut' => $ex_statut));
	}else{
		$upd_statut_match=$bdd->prepare('UPDATE resultatsl1_reel rr, nomenclature_equipes_reelles ner SET rr.statut = :statut WHERE rr.equipeDomicile = ner.trigramme AND ner.ville_maxi = :ville_maxi AND SUBSTRING(rr.journee,5,2) = :journee AND rr.statut >= :ex_statut;');
		$upd_statut_match->execute(array('ville_maxi' => $nom_ville_maxi_dom, 'journee' => $journee, 'statut' => $statut, 'ex_statut' => $ex_statut));
	}
	$upd_statut_match->closeCursor();

	addLogEvent('ville_maxi => '.$nom_ville_maxi_dom.', journee => '.$journee.', statut => '.$statut.', ex_statut => '.$ex_statut);
}


//FONCTION POUR CHANGER LE STATUT DE TOUS LES MATCHS D'une journee
function set_statut_match_termine_journee($journee, $statut, $ex_statut)
{
	addLogEvent('FONCTION set_statut_match_journee');
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
	
	if($ex_statut<4)
	{
		$upd_statut_all_match=$bdd->prepare('UPDATE resultatsl1_reel rr SET rr.statut = :statut WHERE SUBSTRING(rr.journee,5,2) = :journee AND rr.statut = :ex_statut;');
		$upd_statut_all_match->execute(array('journee' => $journee, 'statut' => $statut, 'ex_statut' => $ex_statut));
	}else{
		$upd_statut_all_match=$bdd->prepare('UPDATE resultatsl1_reel rr SET rr.statut = :statut WHERE SUBSTRING(rr.journee,5,2) = :journee AND rr.statut > :ex_statut AND UNIX_TIMESTAMP()-statut > 600 ;');
		$upd_statut_all_match->execute(array('journee' => $journee, 'statut' => $statut, 'ex_statut' => $ex_statut));
	}
	$upd_statut_all_match->closeCursor();

	addLogEvent('journee => '.$journee.', statut => '.$statut.', ex_statut => '.$ex_statut);
}

//RECUPERER LE STATUT D'UN MATCH A PARTIR DE LEQUIPE DOMICILE MAXI
function getStatutMatchMaxi($nom_ville_maxi_dom, $journee)
{
	addLogEvent('FONCTION getStatutMatchMaxi');
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

	
	addLogEvent('ville_maxi => '.$nom_ville_maxi_dom.', journee => '.$journee);
	addLogEvent('STATUT => '.$statut);

	return $statut;
}

//RECUPERER LE STATUT D'UN MATCH A PARTIR DU TRIGRAMME EQUIPE
function getStatutMatch($trigramme_dom, $journee)
{
	addLogEvent('FONCTION getStatutMatch');
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
	$req_statut_match=$bdd->prepare('SELECT rr.statut FROM resultatsl1_reel rr WHERE :equipeDomicile IN (rr.equipeDomicile, rr.equipeVisiteur) AND SUBSTRING(rr.journee,5,2) = :journee;');
	
	$req_statut_match->execute(array('equipeDomicile' => $trigramme_dom, 'journee' => $journee));
	
	while ($statutsMatch = $req_statut_match->fetch())
	{
		$statut = $statutsMatch['statut'];
	}
	
	$req_statut_match->closeCursor();

	addLogEvent('Equipe => '.$trigramme_dom.', journee => '.$journee);
	addLogEvent('STATUT => '.$statut);

	return $statut;
}


//A PARTIR DU TABLEAU DE SCRAP => REMPLIR LA TABLE resultatl1reel 
//FORMAT TABLEAU : NOM_VILLE_MAXI_DOM, NB_BUT_DOM, NB_PENALTY_DOM, NB_BUT_EXT, NOM_VILLE_MAXI_EXT, NB_PENALTY_EXT, JOURNEE, STATUT_MATCH
function setScoreMatch($tab_resultat){
	addLogEvent('FONCTION UPDATE SCORE EN BASE setScoreMatch');
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
	
	
	addLogEvent('butDomicile => '.$tab_resultat[1].', winOrLoseDomicile => '.$statusDOM.', penaltyDomicile => '.$tab_resultat[2].',butVisiteur => '.$tab_resultat[3].',winOrLoseVisiteur => '.$statusEXT.',penaltyVisiteur => '.$tab_resultat[5].',ville_maxi => '.$tab_resultat[0].',journee => '.$tab_resultat[6]);

}

//FONCTION DE MISE A JOUR DE LA TABLE DES BUTEURS EN LIVE
//FORMAT TABLEAU EN ENTREE : VILLE_MAXI, ID_JOUEUR_MAXI, IS_PENALTY, IS_CSC, JOURNEE
function maj_table_live_buteur($tab_buteurs){
	addLogEvent('FONCTION maj_table_live_buteur');
	
	//On vide la table
	nettoyageTableButeurLive();
	
	//On rempli la table avec les infos brutes
	foreach($tab_buteurs as $buteur){
		addLogEvent('INFO BRUTE : '.$buteur[0].' - '.$buteur[1].' PENAL? '.$buteur[2].' CSC? '.$buteur[3].' Journee : '.$buteur[4].' Pour l\'équipe : '.$buteur[5]);
		setButeurLive($buteur);
		associer_buteur_live_joueur_reel();
		afficher_log_buteur_sans_matching();
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

//Ecrit dans le fichier LOG le nom_maxi de chaque buteur n'ayant pas trouvé de correspondance dans la table joueur_reel
function afficher_log_buteur_sans_matching(){
	addLogEvent('FONCTION afficher_log_buteur_sans_matching');
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
	$req_buteurs_sans_matching=$bdd->prepare('SELECT id_joueur_maxi FROM buteur_live_journee WHERE id_joueur_reel IS NULL ;');
	$req_buteurs_sans_matching->execute();
	
	while ($buteur_maxi = $req_buteurs_sans_matching->fetch())
	{
		addLogEvent('Aucun joueur reel trouvé pour le buteur maxi : '.$buteur_maxi['id_joueur_maxi']);
	}
	
	$req_buteurs_sans_matching->closeCursor();	
}

//Renvoie le nombre de match ayant un statut à 1 sur une journée déterminée
function get_nb_match_termine_par_journee($num_journee){
	addLogEvent('FONCTION get_nb_match_termine_par_journee');
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
	
	$req_nb_match_termine_journee=$bdd->prepare('SELECT COUNT(*) AS \'nb_match_termine\' FROM resultatsl1_reel WHERE SUBSTRING(journee,5,2) = :journee AND statut = 1;');
	$req_nb_match_termine_journee->execute(array('journee' => $num_journee));
	
	while ($nb_match = $req_nb_match_termine_journee->fetch())
	{
		addLogEvent('Matchs terminés sur la journée '.$num_journee.' : '.$nb_match['nb_match_termine']);
		return $nb_match['nb_match_termine'];
	}
	$req_nb_match_termine_journee->closeCursor();
}


//Annule tous les matchs avec un statut à 1
function annuler_match_restants($num_journee)
{
	addLogEvent('FONCTION annuler_match_restants');
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
	
	//En cours
	//$upd_annule_match_restant_journee=$bdd->prepare('UPDATE resultatsl1_reel SET WHERE SUBSTRING(journee,5,2) = :journee ;');
	$upd_annule_match_restant_journee->execute(array('journee' => $num_journee));
	
	$upd_annule_match_restant_journee->closeCursor();

}

//Récupère le fichier CSV from ROTO
function get_csv_from_roto($num_journee_avec_annee)
{
	addLogEvent('FONCTION get_csv_from_roto');
	$url_part1 = "https://www.rotowire.com/soccer/player_stats.xls?pos=A&league=FRAN&season=";
	$url_option_saison = substr($num_journee_avec_annee,0,4);
	$url_part2 = "&start=";
	$url_option_journee = substr($num_journee_avec_annee,-2);
	$url_part3 = "&end=";
	$url_part4 = "&gp=GP&min=MIN&st=ST&on=ON&off=OFF&y=Y&yr=YR&r=R&g=G&a=A&s=S&sog=SOG&cr=CR&acr=ACR&cc=CC&blk=BLK&int=INT&tkl=TKL&tklw=TKLW&fc=FC&fs=FS&pkg=PKG&pkm=PKM&pkc=PKC&crn=CRN&p=P&ap=AP&acro=ACRO&bcc=BCC&aw=AW&dr=DR&dsp=DSP&dw=DW&cl=CL&ecl=ECL&own=OWN&touch=TOUCH&gc=GC&cs=CS&sv=SV&pkf=PKF&pksv=PKSV&aks=AKS&punch=PUNCH&ibs=IBS&obs=OBS&ibsog=IBSOG&obsog=OBSOG&ibg=IBG&obg=OBG&fks=FKS&fksog=FKSOG&fkg=FKG&tbox=TBOX&fkcr=FKCR&fkacr=FKACR&crncr=CRNCR&br=BR&crnw=CRNW&pksvd=PKSVD&ibsv=IBSV&obsv=OBSV&pk=PK&sa=SA";
		
	$url_definitif = $url_part1.$url_option_saison.$url_part2.$url_option_journee.$url_part3.$url_option_journee.$url_part4;
	
	
	//Téléchargement du fichier journee au format CSV
	//Id et MDP de connexion
	login("https://www.rotowire.com/users/loginnow.htm?link=%2Findex.php","username=xzw32&p1=rotowiremotdepasse&Submit=Login");
	
	$output = grab_page($url_definitif);
	$lignes1 = str_replace("\t\r\n","\n",$output);
	$lignes1 = str_replace("\r","\n",$lignes1);
	$lignes1 = str_replace("\t",";",$lignes1);
	
	$path = __DIR__.'/rotostats/'.$url_option_saison.$url_option_journee.".csv";
	$fp = fopen ($path, 'w');
	fwrite($fp,$lignes1);
	fclose($fp);
	
	//Exploitation automatique du fichier
	scrapRoto($num_journee_avec_annee, $path);
}

//Exploite le fichier CSV ($path) CORRESPONDANT A LA JOURNEE
function scrapRoto($num_journee_avec_annee, $path)
{	
	addLogEvent('FONCTION num_journee_avec_annee');
	$resultatsJournee = buildTableauJournee($num_journee_avec_annee);
	nettoyageFichierStat($path);
		
	//Calcul des statistiques complémentaires
	$erreur_sur_fichier = 1;
	$row=0;
	if (($handle = @fopen($path, "r")) !== FALSE) {
		$erreur_sur_fichier = 0;
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			$num = count($data);
			if($row==0) {
				$tableau[$row][0] = 'IDRECHERCHE';
				$tableau[$row][1] = 'JOURNEE';
				$tableauEnTete[0] = 'IDRECHERCHE';
				$tableauEnTete[1] = 'JOURNEE';
			}else {
				$tableau[$row][0] = str_replace(' ','_',rtrim(ltrim($data[0])).rtrim(ltrim($data[1])).$data[2]);
				$tableau[$row][1] = $num_journee_avec_annee;
			}
			
			for ($c=0; $c < $num; $c++) {
				if($c>6){
					$tableau[$row][] = str_replace(' ','',$data[$c]);
					if($row==0){$tableauEnTete[] = str_replace(' ','',$data[$c]);
				}
			}
		}
			
		if($row==0) {
			$tableau[$row][] = '6ButPrisSansPenal';
			$tableau[$row][] = '5ButPrisSansPenal';
			$tableau[$row][] = '4ButPrisSansPenal';
			$tableau[$row][] = '3ButPrisSansPenal';
			$tableau[$row][] = '2ButPrisSansPenal';
			$tableau[$row][] = '1ButPrisSansPenal';
			$tableau[$row][] = 'CRouge60';
			$tableau[$row][] = 'CRouge75';
			$tableau[$row][] = 'CRouge80';
			$tableau[$row][] = 'CRouge85';
			$tableau[$row][] = 'CentreRate';
			$tableau[$row][] = 'Clean60';
			$tableau[$row][] = 'Clean60D';
			$tableau[$row][] = 'Ecart-5';
			$tableau[$row][] = 'Ecart-4';
			$tableau[$row][] = 'Ecart-3';
			$tableau[$row][] = 'Ecart-2';
			$tableau[$row][] = 'Ecart+2';
			$tableau[$row][] = 'Ecart+3';
			$tableau[$row][] = 'Ecart+4';
			$tableau[$row][] = 'GrossOccazRate';
			$tableau[$row][] = 'MalusDefaite';
			$tableau[$row][] = '15PassOK30';
			$tableau[$row][] = '15PassOK40';
			$tableau[$row][] = '15PassOK50';
			$tableau[$row][] = '15PassOK90';
			$tableau[$row][] = '15PassOK95';
			$tableau[$row][] = '15PassOK100';
			$tableau[$row][] = '25PassOK30';
			$tableau[$row][] = '25PassOK40';
			$tableau[$row][] = '25PassOK50';
			$tableau[$row][] = '25PassOK90';
			$tableau[$row][] = '25PassOK95';
			$tableau[$row][] = '25PassOK100';
			$tableau[$row][] = 'TackleLost';
			$tableau[$row][] = 'TirPasCadre';
			$tableau[$row][] = '80BallonsTouch';
			$tableau[$row][] = '90BallonsTouch';
			$tableau[$row][] = '100BallonsTouch';
			$tableau[$row][] = 'BonusVict';
			$tableau[$row][] = 'CoupFrancRate';
			$tableauEnTete[] = '6ButPrisSansPenal';
			$tableauEnTete[] = '5ButPrisSansPenal';
			$tableauEnTete[] = '4ButPrisSansPenal';
			$tableauEnTete[] = '3ButPrisSansPenal';
			$tableauEnTete[] = '2ButPrisSansPenal';
			$tableauEnTete[] = '1ButPrisSansPenal';
			$tableauEnTete[] = 'CRouge60';
			$tableauEnTete[] = 'CRouge75';
			$tableauEnTete[] = 'CRouge80';
			$tableauEnTete[] = 'CRouge85';
			$tableauEnTete[] = 'CentreRate';
			$tableauEnTete[] = 'Clean60';
			$tableauEnTete[] = 'Clean60D';
			$tableauEnTete[] = 'Ecart-5';
			$tableauEnTete[] = 'Ecart-4';
			$tableauEnTete[] = 'Ecart-3';
			$tableauEnTete[] = 'Ecart-2';
			$tableauEnTete[] = 'Ecart+2';
			$tableauEnTete[] = 'Ecart+3';
			$tableauEnTete[] = 'Ecart+4';
			$tableauEnTete[] = 'GrossOccazRate';
			$tableauEnTete[] = 'MalusDefaite';
			$tableauEnTete[] = '15PassOK30';
			$tableauEnTete[] = '15PassOK40';
			$tableauEnTete[] = '15PassOK50';
			$tableauEnTete[] = '15PassOK90';
			$tableauEnTete[] = '15PassOK95';
			$tableauEnTete[] = '15PassOK100';
			$tableauEnTete[] = '25PassOK30';
			$tableauEnTete[] = '25PassOK40';
			$tableauEnTete[] = '25PassOK50';
			$tableauEnTete[] = '25PassOK90';
			$tableauEnTete[] = '25PassOK95';
			$tableauEnTete[] = '25PassOK100';
			$tableauEnTete[] = 'TackleLost';
			$tableauEnTete[] = 'TirPasCadre';
			$tableauEnTete[] = '80BallonsTouch';
			$tableauEnTete[] = '90BallonsTouch';
			$tableauEnTete[] = '100BallonsTouch';
			$tableauEnTete[] = 'BonusVict';
			$tableauEnTete[] = 'CoupFrancRate';
									
		}else {
			$i = butsEncaissesSanSPenalty($row,$data[2],$resultatsJournee,$data);
			if ($i==1) {
				$tableau[$row][] = 0;//'6ButPrisSansPenal';
				$tableau[$row][] = 0;//'5ButPrisSansPenal';
				$tableau[$row][] = 0;//'4ButPrisSansPenal';
				$tableau[$row][] = 0;//'3ButPrisSansPenal';
				$tableau[$row][] = 0;//'2ButPrisSansPenal';
				$tableau[$row][] = 1;//'1ButPrisSansPenal';
			} elseif ($i == 2) {
				$tableau[$row][] = 0;//'6ButPrisSansPenal';
				$tableau[$row][] = 0;//'5ButPrisSansPenal';
				$tableau[$row][] = 0;//'4ButPrisSansPenal';
				$tableau[$row][] = 0;//'3ButPrisSansPenal';
				$tableau[$row][] = 1;//'2ButPrisSansPenal';
				$tableau[$row][] = 0;//'1ButPrisSansPenal';
			} elseif ($i == 3) {
				$tableau[$row][] = 0;//'6ButPrisSansPenal';
				$tableau[$row][] = 0;//'5ButPrisSansPenal';
				$tableau[$row][] = 0;//'4ButPrisSansPenal';
				$tableau[$row][] = 1;//'3ButPrisSansPenal';
				$tableau[$row][] = 0;//'2ButPrisSansPenal';
				$tableau[$row][] = 0;//'1ButPrisSansPenal';
			} elseif ($i == 4) {
				$tableau[$row][] = 0;//'6ButPrisSansPenal';
				$tableau[$row][] = 0;//'5ButPrisSansPenal';
				$tableau[$row][] = 1;//'4ButPrisSansPenal';
				$tableau[$row][] = 0;//'3ButPrisSansPenal';
				$tableau[$row][] = 0;//'2ButPrisSansPenal';
				$tableau[$row][] = 0;//'1ButPrisSansPenal';
			} elseif ($i == 5) {
				$tableau[$row][] = 0;//'6ButPrisSansPenal';
				$tableau[$row][] = 1;//'5ButPrisSansPenal';
				$tableau[$row][] = 0;//'4ButPrisSansPenal';
				$tableau[$row][] = 0;//'3ButPrisSansPenal';
				$tableau[$row][] = 0;//'2ButPrisSansPenal';
				$tableau[$row][] = 0;//'1ButPrisSansPenal';
			} elseif ($i >= 6) {
				$tableau[$row][] = 1;//'6ButPrisSansPenal';
				$tableau[$row][] = 0;//'5ButPrisSansPenal';
				$tableau[$row][] = 0;//'4ButPrisSansPenal';
				$tableau[$row][] = 0;//'3ButPrisSansPenal';
				$tableau[$row][] = 0;//'2ButPrisSansPenal';
				$tableau[$row][] = 0;//'1ButPrisSansPenal';
			} else {
				$tableau[$row][] = 0;//'6ButPrisSansPenal';
				$tableau[$row][] = 0;//'5ButPrisSansPenal';
				$tableau[$row][] = 0;//'4ButPrisSansPenal';
				$tableau[$row][] = 0;//'3ButPrisSansPenal';
				$tableau[$row][] = 0;//'2ButPrisSansPenal';
				$tableau[$row][] = 0;//'1ButPrisSansPenal';
			}
						
			if($data[14]==1)
			{
					if($data[8]<60){
						$tableau[$row][] = 1;//'CRouge60';
						$tableau[$row][] = 0;//'CRouge75';
						$tableau[$row][] = 0;//'CRouge80';
						$tableau[$row][] = 0;//'CRouge85';
					}elseif($data[8]<75){
						$tableau[$row][] = 0;//'CRouge60';
						$tableau[$row][] = 1;//'CRouge75';
						$tableau[$row][] = 0;//'CRouge80';
						$tableau[$row][] = 0;//'CRouge85';
					}elseif($data[8]<80){
						$tableau[$row][] = 0;//'CRouge60';
						$tableau[$row][] = 0;//'CRouge75';
						$tableau[$row][] = 1;//'CRouge80';
						$tableau[$row][] = 0;//'CRouge85';
					}elseif($data[8]<85){
						$tableau[$row][] = 0;//'CRouge60';
						$tableau[$row][] = 0;//'CRouge75';
						$tableau[$row][] = 0;//'CRouge80';
						$tableau[$row][] = 1;//'CRouge85';
					}else{
						$tableau[$row][] = 0;//'CRouge60';
						$tableau[$row][] = 0;//'CRouge75';
						$tableau[$row][] = 0;//'CRouge80';
						$tableau[$row][] = 0;//'CRouge85';
					}
			}else{
				$tableau[$row][] = 0;//'CRouge60';
				$tableau[$row][] = 0;//'CRouge75';
				$tableau[$row][] = 0;//'CRouge80';
				$tableau[$row][] = 0;//'CRouge85';
			}
			$tableau[$row][] = ($data[21]-$data[22]);//'CentreRate';
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs CleanSheet W : ';
			if(isCleanSheet($row,$data[2],$resultatsJournee,$data)){
				$tableau[$row][] = 1; //'Clean60';
			}else{
				$tableau[$row][] = 0; //'Clean60';
			}
			
			if(isCleanSheetNul($row,$data[2],$resultatsJournee,$data)){
				$tableau[$row][] = 1; //'Clean60D';
			}else{
				$tableau[$row][] = 0; //'Clean60D';
			}
			

			if(ecartScore($row,$data[2],$resultatsJournee,$data)<=-5){
				$tableau[$row][] = 1; //'Ecart-5';
				$tableau[$row][] = 0; //'Ecart-4';
				$tableau[$row][] = 0; //'Ecart-3';
				$tableau[$row][] = 0; //'Ecart-2';
				$tableau[$row][] = 0; //'Ecart+2';
				$tableau[$row][] = 0; //'Ecart+3';
				$tableau[$row][] = 0; //'Ecart+4';
			}elseif(ecartScore($row,$data[2],$resultatsJournee,$data)==-4){
				$tableau[$row][] = 0; //'Ecart-5';
				$tableau[$row][] = 1; //'Ecart-4';
				$tableau[$row][] = 0; //'Ecart-3';
				$tableau[$row][] = 0; //'Ecart-2';
				$tableau[$row][] = 0; //'Ecart+2';
				$tableau[$row][] = 0; //'Ecart+3';
				$tableau[$row][] = 0; //'Ecart+4';
			}elseif(ecartScore($row,$data[2],$resultatsJournee,$data)==-3){
				$tableau[$row][] = 0; //'Ecart-5';
				$tableau[$row][] = 0; //'Ecart-4';
				$tableau[$row][] = 1; //'Ecart-3';
				$tableau[$row][] = 0; //'Ecart-2';
				$tableau[$row][] = 0; //'Ecart+2';
				$tableau[$row][] = 0; //'Ecart+3';
				$tableau[$row][] = 0; //'Ecart+4';
			}elseif(ecartScore($row,$data[2],$resultatsJournee,$data)==-2){
				$tableau[$row][] = 0; //'Ecart-5';
				$tableau[$row][] = 0; //'Ecart-4';
				$tableau[$row][] = 0; //'Ecart-3';
				$tableau[$row][] = 1; //'Ecart-2';
				$tableau[$row][] = 0; //'Ecart+2';
				$tableau[$row][] = 0; //'Ecart+3';
				$tableau[$row][] = 0; //'Ecart+4';
			}elseif(ecartScore($row,$data[2],$resultatsJournee,$data)==2){
				$tableau[$row][] = 0; //'Ecart-5';
				$tableau[$row][] = 0; //'Ecart-4';
				$tableau[$row][] = 0; //'Ecart-3';
				$tableau[$row][] = 0; //'Ecart-2';
				$tableau[$row][] = 1; //'Ecart+2';
				$tableau[$row][] = 0; //'Ecart+3';
				$tableau[$row][] = 0; //'Ecart+4';
			}elseif(ecartScore($row,$data[2],$resultatsJournee,$data)==3){
				$tableau[$row][] = 0; //'Ecart-5';
				$tableau[$row][] = 0; //'Ecart-4';
				$tableau[$row][] = 0; //'Ecart-3';
				$tableau[$row][] = 0; //'Ecart-2';
				$tableau[$row][] = 0; //'Ecart+2';
				$tableau[$row][] = 1; //'Ecart+3';
				$tableau[$row][] = 0; //'Ecart+4';
			}elseif(ecartScore($row,$data[2],$resultatsJournee,$data)>=4){
				$tableau[$row][] = 0; //'Ecart-5';
				$tableau[$row][] = 0; //'Ecart-4';
				$tableau[$row][] = 0; //'Ecart-3';
				$tableau[$row][] = 0; //'Ecart-2';
				$tableau[$row][] = 0; //'Ecart+2';
				$tableau[$row][] = 0; //'Ecart+3';
				$tableau[$row][] = 1; //'Ecart+4';
			}else{
				$tableau[$row][] = 0; //'Ecart-5';
				$tableau[$row][] = 0; //'Ecart-4';
				$tableau[$row][] = 0; //'Ecart-3';
				$tableau[$row][] = 0; //'Ecart-2';
				$tableau[$row][] = 0; //'Ecart+2';
				$tableau[$row][] = 0; //'Ecart+3';
				$tableau[$row][] = 0; //'Ecart+4';
			}
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs GrossOccazRate : ';
			if($data[41]-$data[15]<0){
				$tableau[$row][] = 0 ;
			}else{
				$tableau[$row][] = $data[41]-$data[15] ;
			}
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs MalusDefaite : ';
			if(testVictoire($row,$data[2],$resultatsJournee,$data)=='L'){
				$tableau[$row][] = 1;
			}else{
				$tableau[$row][] = 0;	
			}
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs % passes réussies :';
			if($data[30]>=30){
				if(100*$data[29]/$data[30]<=30){
					$tableau[$row][] = 1 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 1 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
				}elseif(100*$data[29]/$data[30]<=40){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 1 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 1 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
				}elseif(100*$data[29]/$data[30]<50){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 1 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 1 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
				}elseif(100*$data[29]/$data[30]<90){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 1 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 1 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
				}elseif(100*$data[29]/$data[30]<=95){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 1 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 1 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
				}elseif(100*$data[29]/$data[30]<=100){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 1 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 1 ;//'25PassOK100';
				}else{
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
				}
			}elseif($data[30]>=15){
				if(100*$data[29]/$data[30]<=30){
					$tableau[$row][] = 1 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
					
				}elseif(100*$data[29]/$data[30]<=40){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 1 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
					
				}elseif(100*$data[29]/$data[30]<50){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 1 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
					
				}elseif(100*$data[29]/$data[30]<90){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 1 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
		
				}elseif(100*$data[29]/$data[30]<=95){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 1 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
			
				}elseif(100*$data[29]/$data[30]<=100){
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 1 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
					
				}else{
					$tableau[$row][] = 0 ;//'15PassOK30';
					$tableau[$row][] = 0 ;//'15PassOK40';
					$tableau[$row][] = 0 ;//'15PassOK50';
					$tableau[$row][] = 0 ;//'15PassOK90';
					$tableau[$row][] = 0 ;//'15PassOK95';
					$tableau[$row][] = 0 ;//'15PassOK100';
					$tableau[$row][] = 0 ;//'25PassOK30';
					$tableau[$row][] = 0 ;//'25PassOK40';
					$tableau[$row][] = 0 ;//'25PassOK50';
					$tableau[$row][] = 0 ;//'25PassOK90';
					$tableau[$row][] = 0 ;//'25PassOK95';
					$tableau[$row][] = 0 ;//'25PassOK100';
					
				}
			}else{
				$tableau[$row][] = 0 ;//'15PassOK30';
				$tableau[$row][] = 0 ;//'15PassOK40';
				$tableau[$row][] = 0 ;//'15PassOK50';
				$tableau[$row][] = 0 ;//'15PassOK90';
				$tableau[$row][] = 0 ;//'15PassOK95';
				$tableau[$row][] = 0 ;//'15PassOK100';
				$tableau[$row][] = 0 ;//'25PassOK30';
				$tableau[$row][] = 0 ;//'25PassOK40';
				$tableau[$row][] = 0 ;//'25PassOK50';
				$tableau[$row][] = 0 ;//'25PassOK90';
				$tableau[$row][] = 0 ;//'25PassOK95';
				$tableau[$row][] = 0 ;//'25PassOK100';
			
			}
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs tacle lost';
			$tableau[$row][] = $data[25] - $data[26];//'TackleLost';
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs TirPasCadre';
			$tableau[$row][] = $data[18] - $data[19];//'TirPasCadre';
			//echo 'Tirs PAS CADRE : '.$tableau[$row][107].' ////////';
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs ballons touchés';
			if($data[37]>=100){
				$tableau[$row][] = 0 ;//'80BallonsTouch';
				$tableau[$row][] = 0 ;//'90BallonsTouch';
				$tableau[$row][] = 1 ;//'100BallonsTouch';
			}elseif($data[37]>=90){
				$tableau[$row][] = 0 ;//'80BallonsTouch';
				$tableau[$row][] = 1 ;//'90BallonsTouch';
				$tableau[$row][] = 0 ;//'100BallonsTouch';
			}elseif($data[37]>=80){
				$tableau[$row][] = 1 ;//'80BallonsTouch';
				$tableau[$row][] = 0 ;//'90BallonsTouch';
				$tableau[$row][] = 0 ;//'100BallonsTouch';
			}else{
				$tableau[$row][] = 0 ;//'80BallonsTouch';
				$tableau[$row][] = 0 ;//'90BallonsTouch';
				$tableau[$row][] = 0 ;//'100BallonsTouch';
			}
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs BonusVictoire : ';
			if(testVictoire($row,$data[2],$resultatsJournee,$data)=='W'){
				$tableau[$row][] = 1;
			}else{
				$tableau[$row][] = 0;	
			}
			
			//echo '## '.count($tableau[$row]).' ##';
			//echo 'Calculs CoupFrancRate : ';
			$tableau[$row][] = ($data[56]-$data[57]);//'CoupFrancRate';	
		
			}
				
			//echo "<br />\n";
			$row++;
		}
		fclose($handle);
		
	}

	//Import en BDD des statistiques
	if($erreur_sur_fichier == 0){
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
			
			
			$supprimerStatsAncienne = $bdd->prepare('DELETE FROM joueur_stats WHERE journee = :journee');
			$supprimerStatsAncienne->execute (array('journee' => $num_journee_avec_annee));
			$supprimerStatsAncienne->closeCursor();
			$premiereLigne=0;
			
			$req = $bdd->prepare('INSERT IGNORE INTO joueur_stats(
						id,
						journee,
						a_joue,
						minutes,
						titulaire,
						est_rentre,
						est_sorti,
						jaune,
						jaune_rouge,
						rouge,
						but,
						passe_d,
						second_passe_d,
						tir,
						tir_cadre,
						interception,
						centre,
						centre_reussi,
						occasion_creee,
						contre,
						total_tacle,
						tacle_reussi,
						faute_commise,
						faute_subie,
						passe,
						passe_tentee,
						centre_reussi_dans_le_jeu,
						duel_aerien_gagne,
						grosse_occasion_creee,
						ballon_recupere,
						dribble,
						duel_gagne,
						ballon_touche,
						ballon_touche_int_surface,
						tir_int_surface,
						tir_ext_surface,
						tir_cadre_int_surface,
						tir_cadre_ext_surface,
						but_int_surface,
						but_ext_surface,
						ballon_perdu,
						csc,
						penalty_tire,
						penalty_marque,
						penalty_rate,
						penalty_arrete,
						corner_tire,
						corner_centre,
						corner_gagne,
						coup_franc_centre,
						coup_franc_centre_reussi,
						coup_franc_tire,
						coup_franc_cadre,
						coup_franc_marque,
						but_concede,
						cleansheet,
						arret,
						arret_tir_int_surface,
						arret_tir_ext_surface,
						sortie_ext_surface_reussie,
						penalty_concede,
						penalty_subi_gb,
						penalty_arrete_gb,
						degagement,
						degagement_reussi,
						degagement_poing,
						6_buts_ou_plus_pris_sans_penalty,
						5_buts_pris_sans_penalty,
						4_buts_pris_sans_penalty,
						3_buts_pris_sans_penalty,
						2_buts_pris_sans_penalty,
						1_but_pris_sans_penalty,
						rouge_60,
						rouge_75,
						rouge_80,
						rouge_85,
						centre_rate,
						clean_60,
						clean_60D,
						ecart_moins_5,
						ecart_moins_4,
						ecart_moins_3,
						ecart_moins_2,
						ecart_plus_2,
						ecart_plus_3,
						ecart_plus_4,
						grosse_occasion_ratee,
						malus_defaite,
						15_passes_OK_30,
						15_passes_OK_40,
						15_passes_OK_50,
						15_passes_OK_90,
						15_passes_OK_95,
						15_passes_OK_100,
						25_passes_OK_30,
						25_passes_OK_40,
						25_passes_OK_50,
						25_passes_OK_90,
						25_passes_OK_95,
						25_passes_OK_100,
						tacle_rate,
						tir_non_cadre,
						80_ballons_touches,
						90_ballons_touches,
						100_ballons_touches,
						bonus_victoire,
						coup_franc_rate,
						note
						)VALUES(
						:id,
						:journee,
						:a_joue,
						:minutes,
						:titulaire,
						:est_rentre,
						:est_sorti,
						:jaune,
						:jaune_rouge,
						:rouge,
						:but,
						:passe_d,
						:second_passe_d,
						:tir,
						:tir_cadre,
						:interception,
						:centre,
						:centre_reussi,
						:occasion_creee,
						:contre,
						:total_tacle,
						:tacle_reussi,
						:faute_commise,
						:faute_subie,
						:passe,
						:passe_tentee,
						:centre_reussi_dans_le_jeu,
						:duel_aerien_gagne,
						:grosse_occasion_creee,
						:ballon_recupere,
						:dribble,
						:duel_gagne,
						:ballon_touche,
						:ballon_touche_int_surface,
						:tir_int_surface,
						:tir_ext_surface,
						:tir_cadre_int_surface,
						:tir_cadre_ext_surface,
						:but_int_surface,
						:but_ext_surface,
						:ballon_perdu,
						:csc,
						:penalty_tire,
						:penalty_marque,
						:penalty_rate,
						:penalty_arrete,
						:corner_tire,
						:corner_centre,
						:corner_gagne,
						:coup_franc_centre,
						:coup_franc_centre_reussi,
						:coup_franc_tire,
						:coup_franc_cadre,
						:coup_franc_marque,
						:but_concede,
						:cleansheet,
						:arret,
						:arret_tir_int_surface,
						:arret_tir_ext_surface,
						:sortie_ext_surface_reussie,
						:penalty_concede,
						:penalty_subi_gb,
						:penalty_arrete_gb,
						:degagement,
						:degagement_reussi,
						:degagement_poing,
						:6_buts_ou_plus_pris_sans_penalty,
						:5_buts_pris_sans_penalty,
						:4_buts_pris_sans_penalty,
						:3_buts_pris_sans_penalty,
						:2_buts_pris_sans_penalty,
						:1_but_pris_sans_penalty,
						:rouge_60,
						:rouge_75,
						:rouge_80,
						:rouge_85,
						:centre_rate,
						:clean_60,
						:clean_60D,
						:ecart_moins_5,
						:ecart_moins_4,
						:ecart_moins_3,
						:ecart_moins_2,
						:ecart_plus_2,
						:ecart_plus_3,
						:ecart_plus_4,
						:grosse_occasion_ratee,
						:malus_defaite,
						:15_passes_OK_30,
						:15_passes_OK_40,
						:15_passes_OK_50,
						:15_passes_OK_90,
						:15_passes_OK_95,
						:15_passes_OK_100,
						:25_passes_OK_30,
						:25_passes_OK_40,
						:25_passes_OK_50,
						:25_passes_OK_90,
						:25_passes_OK_95,
						:25_passes_OK_100,
						:tacle_rate,
						:tir_non_cadre,
						:80_ballons_touches,
						:90_ballons_touches,
						:100_ballons_touches,
						:bonus_victoire,
						:coup_franc_rate,
						NULL)');
			
			foreach($tableau as $ligneDeStats)
			{
				if($premiereLigne==0){
					$premiereLigne++;
				}else{
					$statut_tmp = getStatutMatch(substr(trim($ligneDeStats[0]),-3),substr($num_journee_avec_annee,-2));
					if($statut_tmp >= 1 && $statut_tmp <= 3) //Insert uniquement si match terminé depuis plus de 10 minutes
					{
						$colonne=0;
						$req->execute(array(
							'id' => $ligneDeStats[$colonne++],
							'journee' => $ligneDeStats[$colonne++],
							'a_joue' => $ligneDeStats[$colonne++],
							'minutes' => $ligneDeStats[$colonne++],
							'titulaire' => $ligneDeStats[$colonne++],
							'est_rentre' => $ligneDeStats[$colonne++],
							'est_sorti' => $ligneDeStats[$colonne++],
							'jaune' => $ligneDeStats[$colonne++],
							'jaune_rouge' => $ligneDeStats[$colonne++],
							'rouge' => abs($ligneDeStats[$colonne++]),
							'but' => $ligneDeStats[$colonne++],
							'passe_d' => $ligneDeStats[$colonne++],
							'second_passe_d' => $ligneDeStats[$colonne++],
							'tir' => $ligneDeStats[$colonne++],
							'tir_cadre' => $ligneDeStats[$colonne++],
							'interception' => $ligneDeStats[$colonne++],
							'centre' => $ligneDeStats[$colonne++],
							'centre_reussi' => $ligneDeStats[$colonne++],
							'occasion_creee' => $ligneDeStats[$colonne++],
							'contre' => $ligneDeStats[$colonne++],
							'total_tacle' => $ligneDeStats[$colonne++],
							'tacle_reussi' => $ligneDeStats[$colonne++],
							'faute_commise' => $ligneDeStats[$colonne++],
							'faute_subie' => $ligneDeStats[$colonne++],
							'passe' => $ligneDeStats[$colonne++],
							'passe_tentee' => $ligneDeStats[$colonne++],
							'centre_reussi_dans_le_jeu' => $ligneDeStats[$colonne++],
							'duel_aerien_gagne' => $ligneDeStats[$colonne++],
							'grosse_occasion_creee' => $ligneDeStats[$colonne++],
							'ballon_recupere' => $ligneDeStats[$colonne++],
							'dribble' => $ligneDeStats[$colonne++],
							'duel_gagne' => $ligneDeStats[$colonne++],
							'ballon_touche' => $ligneDeStats[$colonne++],
							'ballon_touche_int_surface' => $ligneDeStats[$colonne++],
							'tir_int_surface' => $ligneDeStats[$colonne++],
							'tir_ext_surface' => $ligneDeStats[$colonne++],
							'tir_cadre_int_surface' => $ligneDeStats[$colonne++],
							'tir_cadre_ext_surface' => $ligneDeStats[$colonne++],
							'but_int_surface' => $ligneDeStats[$colonne++],
							'but_ext_surface' => $ligneDeStats[$colonne++],
							'ballon_perdu' => $ligneDeStats[$colonne++],
							'csc' => $ligneDeStats[$colonne++],
							'penalty_tire' => $ligneDeStats[$colonne++],
							'penalty_marque' => $ligneDeStats[$colonne++],
							'penalty_rate' => $ligneDeStats[$colonne++],
							'penalty_arrete' => $ligneDeStats[$colonne++],
							'corner_tire' => $ligneDeStats[$colonne++],
							'corner_centre' => $ligneDeStats[$colonne++],
							'corner_gagne' => $ligneDeStats[$colonne++],
							'coup_franc_centre' => $ligneDeStats[$colonne++],
							'coup_franc_centre_reussi' => $ligneDeStats[$colonne++],
							'coup_franc_tire' => $ligneDeStats[$colonne++],
							'coup_franc_cadre' => $ligneDeStats[$colonne++],
							'coup_franc_marque' => $ligneDeStats[$colonne++],
							'but_concede' => $ligneDeStats[$colonne++],
							'cleansheet' => $ligneDeStats[$colonne++],
							'arret' => $ligneDeStats[$colonne++],
							'arret_tir_int_surface' => $ligneDeStats[$colonne++],
							'arret_tir_ext_surface' => $ligneDeStats[$colonne++],
							'sortie_ext_surface_reussie' => $ligneDeStats[$colonne++],
							'penalty_concede' => $ligneDeStats[$colonne++],
							'penalty_subi_gb' => $ligneDeStats[$colonne++],
							'penalty_arrete_gb' => $ligneDeStats[$colonne++],
							'degagement' => $ligneDeStats[$colonne++],
							'degagement_reussi' => $ligneDeStats[$colonne++],
							'degagement_poing' => $ligneDeStats[$colonne++],
							'6_buts_ou_plus_pris_sans_penalty' => $ligneDeStats[$colonne++],
							'5_buts_pris_sans_penalty' => $ligneDeStats[$colonne++],
							'4_buts_pris_sans_penalty' => $ligneDeStats[$colonne++],
							'3_buts_pris_sans_penalty' => $ligneDeStats[$colonne++],
							'2_buts_pris_sans_penalty' => $ligneDeStats[$colonne++],
							'1_but_pris_sans_penalty' => $ligneDeStats[$colonne++],
							'rouge_60' => $ligneDeStats[$colonne++],
							'rouge_75' => $ligneDeStats[$colonne++],
							'rouge_80' => $ligneDeStats[$colonne++],
							'rouge_85' => $ligneDeStats[$colonne++],
							'centre_rate' => $ligneDeStats[$colonne++],
							'clean_60' => $ligneDeStats[$colonne++],
							'clean_60D' => $ligneDeStats[$colonne++],
							'ecart_moins_5' => $ligneDeStats[$colonne++],
							'ecart_moins_4' => $ligneDeStats[$colonne++],
							'ecart_moins_3' => $ligneDeStats[$colonne++],
							'ecart_moins_2' => $ligneDeStats[$colonne++],
							'ecart_plus_2' => $ligneDeStats[$colonne++],
							'ecart_plus_3' => $ligneDeStats[$colonne++],
							'ecart_plus_4' => $ligneDeStats[$colonne++],
							'grosse_occasion_ratee' => $ligneDeStats[$colonne++],
							'malus_defaite' => $ligneDeStats[$colonne++],
							'15_passes_OK_30' => $ligneDeStats[$colonne++],
							'15_passes_OK_40' => $ligneDeStats[$colonne++],
							'15_passes_OK_50' => $ligneDeStats[$colonne++],
							'15_passes_OK_90' => $ligneDeStats[$colonne++],
							'15_passes_OK_95' => $ligneDeStats[$colonne++],
							'15_passes_OK_100' => $ligneDeStats[$colonne++],
							'25_passes_OK_30' => $ligneDeStats[$colonne++],
							'25_passes_OK_40' => $ligneDeStats[$colonne++],
							'25_passes_OK_50' => $ligneDeStats[$colonne++],
							'25_passes_OK_90' => $ligneDeStats[$colonne++],
							'25_passes_OK_95' => $ligneDeStats[$colonne++],
							'25_passes_OK_100' => $ligneDeStats[$colonne++],
							'tacle_rate' => $ligneDeStats[$colonne++],
							'tir_non_cadre' => $ligneDeStats[$colonne++],
							'80_ballons_touches' => $ligneDeStats[$colonne++],
							'90_ballons_touches' => $ligneDeStats[$colonne++],
							'100_ballons_touches' => $ligneDeStats[$colonne++],
							'bonus_victoire' => $ligneDeStats[$colonne++],
							'coup_franc_rate' => $ligneDeStats[$colonne++]
							));
					}else{
						addLogEvent('Ligne de stats sur match non terminé '.$ligneDeStats[0]);
					}
				}
			}
			addLogEvent('INSERT des stats De la Journee en BDD => OK');
			$req->closeCursor();
	//print_r($tableau[1]);
	//var_dump($array);
	}else{
		addLogEvent('Erreur lors Import Stats ROTO - fichier (probablement fichier absent)');
	}
}

//Fonction de Login sur Roto
function login($url,$data){
		$fp = fopen("cookie.txt", "w");
		fclose($fp);
		$login = curl_init();
		curl_setopt($login, CURLOPT_COOKIEJAR, "cookie.txt");
		curl_setopt($login, CURLOPT_COOKIEFILE, "cookie.txt");
		curl_setopt($login, CURLOPT_TIMEOUT, 40000);
		curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($login, CURLOPT_URL, $url);
		curl_setopt($login, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($login, CURLOPT_POST, TRUE);
		curl_setopt($login, CURLOPT_POSTFIELDS, $data);
		ob_start();
		return curl_exec ($login);
		ob_end_clean();
		curl_close ($login);
		unset($login);    
}

//Fonction de récupération d'une page
function grab_page($site){
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_TIMEOUT, 40);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
		curl_setopt($ch, CURLOPT_URL, $site);
		ob_start();
		return curl_exec ($ch);
		ob_end_clean();
		curl_close ($ch);

}

//Cherche la position (n° ligne) d'une needle dans un tableau haystack
function recursive_array_search($needle,$haystack) {
	foreach($haystack as $key=>$value) {
		$current_key=$key;
		if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
			return $current_key;
		}
	}
	return false;
}   

//Renvoie le nb de but encaissés par une équipe sans compter les penaltys
function butsEncaissesSanSPenalty($ligne,$team,$tableauScore,$dataJoueur) {
	$position = recursive_array_search($team,$tableauScore);
	if($tableauScore[$position][0]==$dataJoueur[2]){
			return ($tableauScore[$position][7]-$tableauScore[$position][9]);
	}else{
		
		return ($tableauScore[$position][2]-$tableauScore[$position][4]);
	}
}

//Renvoie l'écart de score d'une équipe
function ecartScore($ligne,$team,$tableauScore,$dataJoueur) {
	$position = recursive_array_search($team,$tableauScore);
	//echo $tableauScore[$position][0].' == '.$dataJoueur[2];
	if($tableauScore[$position][0]==$dataJoueur[2]){
		//echo ' ____ '.($tableauScore[$position][2]-$tableauScore[$position][7]).' ____ ';
		return ($tableauScore[$position][2]-$tableauScore[$position][7]);	
	}else{
		//echo ' ____ '.($tableauScore[$position][7]-$tableauScore[$position][2]).' ____ ';
		return ($tableauScore[$position][7]-$tableauScore[$position][2]);
	}
}

//Renvoie W en cas de victoire, L en cas de défaite, D en cas de nul et ANNULE en cas de match non joué
function testVictoire($ligne,$team,$tableauScore,$dataJoueur) {
	$position = recursive_array_search($team,$tableauScore);
	if($tableauScore[$position][0]==$dataJoueur[2]){
		return $tableauScore[$position][3];
	}else{
		return $tableauScore[$position][8];
	}
}

//Renvoie true si cleansheet et victoire du joueur ayant joué au moins 60 minutes
function isCleanSheet($ligne,$team,$tableauScore,$dataJoueur) {
	if(testVictoire($ligne,$team,$tableauScore,$dataJoueur)=='W' && butsEncaissesSanSPenalty($ligne,$team,$tableauScore,$dataJoueur) == 0 && $dataJoueur[8]>=60){
		return true;
	}else{
		return false;
	}
}


//Renvoie true si cleansheet match NUL du joueur ayant joué au moins 60 minutes
function isCleanSheetNul($ligne,$team,$tableauScore,$dataJoueur) {
	if(testVictoire($ligne,$team,$tableauScore,$dataJoueur)=='D' && butsEncaissesSanSPenalty($ligne,$team,$tableauScore,$dataJoueur) == 0 && $dataJoueur[8]>=60){
		return true;
	}else{
		return false;
	}
}


//Fonction de collecte des résultats de la journéé
//A partir du CSV resultatsL1.csv
function buildTableauJournee($idJournee) {
	addLogEvent('FONCTION buildTableauJournee');
	$resultatsJourneeTab = null;
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
	$req = $bdd->prepare('SELECT * FROM resultatsl1_reel WHERE journee = :journee');
	$req->execute(array('journee' => $idJournee));
	$ligne=0;
	while ($donnees = $req->fetch())
	{
		$resultatsJourneeTab[$ligne][]=$donnees['equipeDomicile'];
		$resultatsJourneeTab[$ligne][]=$donnees['homeDomicile'];
		$resultatsJourneeTab[$ligne][]=$donnees['butDomicile'];
		$resultatsJourneeTab[$ligne][]=$donnees['winOrLoseDomicile'];
		$resultatsJourneeTab[$ligne][]=$donnees['penaltyDomicile'];
		$resultatsJourneeTab[$ligne][]=$donnees['equipeVisiteur'];
		$resultatsJourneeTab[$ligne][]=$donnees['homeVisiteur'];
		$resultatsJourneeTab[$ligne][]=$donnees['butVisiteur'];
		$resultatsJourneeTab[$ligne][]=$donnees['winOrLoseVisiteur'];
		$resultatsJourneeTab[$ligne][]=$donnees['penaltyVisiteur'];
		$ligne++;
	}
	$req->closeCursor();
	
	//print_r($resultatsJourneeTab);
	return $resultatsJourneeTab;
}

//Calcule les notes de tous les joueurs ayant une note à NULL
function calculer_notes_joueurs()
{
	addLogEvent('FONCTION calculer_notes_joueurs');
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
		
		$req = $bdd->query('SELECT t1.position, t2.* FROM joueur_reel t1, joueur_stats t2 WHERE t2.id IN (t1.cle_roto_primaire, t1.cle_roto_secondaire) AND t2.note IS NULL');
		$req_regleCalcul = $bdd->prepare('SELECT * FROM nomenclature_reglescalculnote WHERE position = :position');
		$req_scoreToNote = $bdd->prepare('SELECT note FROM nomenclature_scoretonote WHERE ScoreObtenu = :scoreObtenu AND Position = :position');
		$upd_note = $bdd->prepare('UPDATE joueur_stats SET note = :note WHERE id = :id AND journee = :journee');
		
		while ($donnees = $req->fetch())
		{
			$scoreCalcule = 0;
			if($donnees['a_joue'] == '0'){
				$noteObtenue = 0;
				addLogEvent($donnees['id'].' n\' a pas joué sur la journee '.$donnees['journee'].' et obtient la note de '.$noteObtenue);
				$upd_note->execute(array('note' => $noteObtenue,'id' => $donnees['id'],'journee' => $donnees['journee']));
			}else{
				$req_regleCalcul->execute(array('position' => $donnees['position']));
				while ($tableauReglesCalcul = $req_regleCalcul->fetch())
				{
					$scoreCalcule += $donnees[$tableauReglesCalcul['StatName']] * $tableauReglesCalcul['Ponderation'];
				}
				
				$req_scoreToNote->execute(array('scoreObtenu' => round($scoreCalcule,2),'position' => $donnees['position']));
				$noteObtenue=$req_scoreToNote->fetch(PDO::FETCH_ASSOC);
				addLogEvent($donnees['id'].' sur la journee '.$donnees['journee'].' obtient la note de '.$noteObtenue['note']);
				$upd_note->execute(array('note' => $noteObtenue['note'],'id' => $donnees['id'],'journee' => $donnees['journee']));
			}
			
		}
		$req->closeCursor();
		$req_regleCalcul->closeCursor();
		$req_scoreToNote->closeCursor();
		$upd_note->closeCursor();
}


//RESTE A FAIRE 
function nettoyageFichierStat()
{
	//Virer les doublons sur le même poste
	
}





?>