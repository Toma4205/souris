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
function calculer_confrontations_journee
function get_ligues_concernees_journee
function raz_table_joueur_compo_equipe_sur_journee
function mise_a_jour_stat_classement

*/

require_once(__DIR__ . '/../modele/connexionSQL.php');
require_once(__DIR__ . '/../modele/joueurequipe/joueurEquipe.php');
require_once(__DIR__ . '/../modele/compoequipe/compoEquipe.php');
require_once(__DIR__ . '/../modele/compoequipe/joueurCompoEquipe.php');
require_once(__DIR__ . '/../controleur/constantesAppli.php');

/////////////////CONNEXION BASE
///// Utilisation du mot clé "global" pour avoir accès à la bdd dans toutes les fonctions
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
		addLogEvent($e);
	}

//////////////////

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
	global $bdd;
	$reqStatutJournee = $bdd->prepare('SELECT statut FROM calendrier_reel WHERE num_journee = :num_journee');
	$reqStatutJournee->execute(array('num_journee' => $num_journee));

	return $reqStatutJournee->fetchColumn();
}

//Retourne le statut d'une journée selon la table calendrier_reel
function setStatutJournee($num_journee,$statut){
	addLogEvent('FUNCTION setStatutJournee');
	global $bdd;
	$updStatutJournee = $bdd->prepare('UPDATE calendrier_reel SET statut = :statut WHERE num_journee = :num_journee');
	$updStatutJournee->execute(array('num_journee' => $num_journee, 'statut' => $statut));

	$updStatutJournee->closeCursor();
}

function getEquipeSansCompo($numJournee)
{
	global $bdd;

  $q = $bdd->prepare('SELECT cl.id_equipe_dom as id FROM calendrier_ligue cl
    WHERE cl.num_journee_cal_reel = :num AND NOT EXISTS (
      SELECT ce.id FROM compo_equipe ce
      WHERE ce.id_equipe = cl.id_equipe_dom AND ce.id_cal_ligue = cl.id)');
  $q->execute([':num' => $numJournee]);

  $equipes = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $equipes[] = $donnees['id'];
  }
  $q->closeCursor();

  $q = $bdd->prepare('SELECT cl.id_equipe_ext as id FROM calendrier_ligue cl
    WHERE cl.num_journee_cal_reel = :num AND NOT EXISTS (
      SELECT ce.id FROM compo_equipe ce
      WHERE ce.id_equipe = cl.id_equipe_ext AND ce.id_cal_ligue = cl.id)');
  $q->execute([':num' => $numJournee]);

  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $equipes[] = $donnees['id'];
  }
  $q->closeCursor();

  return $equipes;
}

function getCompoEquipePrecedente($idEquipe, $numJournee)
{
	global $bdd;

  $q = $bdd->prepare('SELECT ce.* FROM compo_equipe ce
    WHERE ce.id_cal_ligue = (SELECT cl.id FROM calendrier_ligue cl
      WHERE cl.num_journee_cal_reel = :num
      AND (cl.id_equipe_dom = :id OR cl.id_equipe_ext = :id))');
  $q->execute([':num' => $numJournee, ':id' => $idEquipe]);

  $donnees = $q->fetch(PDO::FETCH_ASSOC);
  $q->closeCursor();

  // Si la compo n'est pas trouvée
  if (is_bool($donnees))
  {
    return null;
  }
  else
  {
    return new CompoEquipe($donnees);
  }
}

function getTitulairesByCompo($idCompo)
{
	global $bdd;

  $q = $bdd->prepare('SELECT * FROM joueur_compo_equipe
    WHERE numero < 12
    AND id_compo = :id');
  $q->execute([':id' => $idCompo]);

  $joueurs = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $joueurs[] = new JoueurCompoEquipe($donnees);
  }
  $q->closeCursor();

  return $joueurs;
}

function getCalLigueByEquipeEtJournee($idEquipe, $numJournee)
{
	global $bdd;

  $q = $bdd->prepare('SELECT id FROM calendrier_ligue
    WHERE num_journee_cal_reel = :num
    AND (id_equipe_dom = :id OR id_equipe_ext = :id)');
  $q->execute([':num' => $numJournee, ':id' => $idEquipe]);

  return $q->fetchColumn();
}

function creerCompoCommePrecedente($compo, $idEquipe, $idCalLigue, $joueurs)
{
	global $bdd;

  $q = $bdd->prepare('INSERT INTO compo_equipe(id_cal_ligue, id_equipe, code_tactique)
      VALUES(:calLigue, :equipe, :tactique)');
  $q->bindValue(':calLigue', $idCalLigue);
  $q->bindValue(':equipe', $idEquipe);
  $q->bindValue(':tactique', $compo->codeTactique());

  $q->execute();

  $idCompo = $bdd->lastInsertId();

  foreach($joueurs as $cle => $joueur)
  {
    $q = $bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine)
        VALUES(:idCompo, :idJoueur, :num, :cap)');
    $q->bindValue(':idCompo', $idCompo);
    $q->bindValue(':idJoueur', $joueur->idJoueurReel());
    $q->bindValue(':num', $joueur->numero());
    $q->bindValue(':cap', $joueur->capitaine());

    $q->execute();
  }

  return $idCompo;
}

function getJoueurEquipeByEquipe($idEquipe)
{
	global $bdd;

  $q = $bdd->prepare('SELECT je.*, j.position, j.id
    FROM joueur_equipe je
    JOIN joueur_reel j ON je.id_joueur_reel = j.id
    WHERE id_equipe = :id');
  $q->execute([':id' => $idEquipe]);

  $joueurs = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $joueurs[] = new JoueurEquipe($donnees);
  }
  $q->closeCursor();

  return $joueurs;
}

function creerPremiereCompo($idEquipe, $idCalLigue, $joueurs)
{
	global $bdd;

  $q = $bdd->prepare('INSERT INTO compo_equipe(id_cal_ligue, id_equipe, code_tactique)
      VALUES(:calLigue, :equipe, :tactique)');
  $q->bindValue(':calLigue', $idCalLigue);
  $q->bindValue(':equipe', $idEquipe);
  $q->bindValue(':tactique', ConstantesAppli::TACTIQUE_DEFAUT);

  $q->execute();

  $idCompo = $bdd->lastInsertId();

  $nbGB = 0;
  $nbDef = 0;
  $nbMil = 0;
  $nbAtt = 0;
  foreach($joueurs as $cle => $joueur)
  {
    $numero = 1;
    if ($joueur->position() == ConstantesAppli::GARDIEN && $nbGB < 1) {
      $nbGB++;
    } elseif ($joueur->position() == ConstantesAppli::DEFENSEUR && $nbDef < 4) {
      $nbDef++;
      $numero += $nbDef;
    } elseif ($joueur->position() == ConstantesAppli::MILIEU && $nbMil < 4) {
      $nbMil++;
      $numero += 4 + $nbMil;
    } elseif ($joueur->position() == ConstantesAppli::ATTAQUANT && $nbAtt < 2) {
      $nbAtt++;
      $numero += 8 + $nbAtt;
    } else {
      $numero = 0;
    }

    if ($numero > 0) {
      $q = $bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine)
          VALUES(:idCompo, :idJoueur, :num, 0)');
      $q->bindValue(':idCompo', $idCompo);
      $q->bindValue(':idJoueur', $joueur->id());
      $q->bindValue(':num', $numero);

      $q->execute();
    }
  }

  return $idCompo;
}

//Passage du statut d'une journée de 0 à 1 dans calendrier réel
//Passage du score à 0 pour calendrier ligue sur cette meme journée
//Création des compo non saisies
function initializeJournee($numJournee){
	global $bdd;

	$equipes = getEquipeSansCompo($numJournee);
  if (sizeof($equipes) > 0) {

		addLogEvent(sizeof($equipes) . ' compo manquantes.');

    foreach($equipes as $cle => $idEquipe)
    {
      $idCalLigue = getCalLigueByEquipeEtJournee($idEquipe, $numJournee);
      $compo = getCompoEquipePrecedente($idEquipe, $numJournee - 1);

      if ($compo != null) {
        $joueurs = getTitulairesByCompo($compo->id());
        $idCompo = creerCompoCommePrecedente($compo, $idEquipe, $idCalLigue, $joueurs);
      } else {
        $joueurs = getJoueurEquipeByEquipe($idEquipe);
        $idCompo = creerPremiereCompo($idEquipe, $idCalLigue, $joueurs);
      }

			addLogEvent('Création compo' . $idCompo . ' pour le match ' . $idCalLigue . ' pour l\'équipe ' . $idEquipe);
    }
  } else {
		addLogEvent('Aucune compo manquante.');
  }

  // On passe le statut de la journée à "en cours" = 1
  $q = $bdd->prepare('UPDATE calendrier_reel SET statut = 1 WHERE num_journee = :num');
  $q->execute([':num' => $numJournee]);

  // On initialise les scores des matchs à 0-0
  $q = $bdd->prepare('UPDATE calendrier_ligue SET score_dom = 0, score_ext = 0
    WHERE num_journee_cal_reel = :num');
  $q->execute([':num' => $numJournee]);

	addLogEvent('Initialisation de la journée réussie.');
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
	global $bdd;
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
	global $bdd;

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
	global $bdd;

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
	global $bdd;
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
	global $bdd;
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
	global $bdd;


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
	global $bdd;
	addLogEvent('FONCTION nettoyageTableButeurLive');
	$trc_nettoyageTableButeurLive=$bdd->prepare('TRUNCATE TABLE buteur_live_journee');
	$trc_nettoyageTableButeurLive->execute();
	$trc_nettoyageTableButeurLive->closeCursor();
}

//AJOUT EN BASE D'UN BUTEUR LIVE
//FORMAT TABLEAU EN ENTREE : VILLE_MAXI, ID_JOUEUR_MAXI, IS_PENALTY, IS_CSC, JOURNEE
function setButeurLive($buteur){
	global $bdd;
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
	global $bdd;

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
	global $bdd;
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
	global $bdd;

	$req_nb_match_termine_journee=$bdd->prepare('SELECT COUNT(*) AS \'nb_match_termine\' FROM resultatsl1_reel WHERE SUBSTRING(journee,5,2) = :journee AND statut = 1;');
	$req_nb_match_termine_journee->execute(array('journee' => $num_journee));

	while ($nb_match = $req_nb_match_termine_journee->fetch())
	{
		addLogEvent('Matchs terminés sur la journée '.$num_journee.' : '.$nb_match['nb_match_termine']);
		return $nb_match['nb_match_termine'];
	}
	$req_nb_match_termine_journee->closeCursor();
}


//Annule tous les matchs avec un statut encore à 0 sur une journee déterminée
function annuler_match_restants($num_journee)
{
	addLogEvent('FONCTION annuler_match_restants');
	global $bdd;

	$upd_annule_match_restant_journee=$bdd->prepare('UPDATE resultatsl1_reel SET butDomicile = 0, winOrLoseDomicile = \'ANNULE\', penaltyDomicile = 0, butVisiteur = 0, winOrLoseVisiteur = \'ANNULE\', penaltyVisiteur = 0 WHERE SUBSTRING(journee,5,2) = :journee AND statut = 0;');
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
		global $bdd;


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
	global $bdd;
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
	global $bdd;

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

function getNumJourneeEnCours($bdd)
{
  $q = $bdd->prepare('SELECT num_journee FROM calendrier_reel WHERE statut = 1');
  $q->execute();
  return $q->fetchColumn();
}

function majButeurTempJourneeEnCours($numJournee)
{
	global $bdd;

  $journeeStat = get_journee_format_long($numJournee);
  $joueurs = [];
  $q = $bdd->prepare('SELECT id, but, csc
    FROM joueur_stats
    WHERE journee = :journee AND (but > 0 OR csc > 0)');
  $q->execute([':journee' => $journeeStat]);

  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $q2 = $bdd->prepare('UPDATE joueur_compo_equipe jce
      SET jce.nb_but_reel = :nbBut, jce.nb_csc = :nbCsc
      WHERE jce.numero < 12
      AND jce.id_joueur_reel = (
        SELECT id
        FROM joueur_reel
        WHERE cle_roto_primaire = :cle
      )
      AND jce.id_compo IN (
        SELECT id
        FROM compo_equipe
        WHERE id_cal_ligue IN (
          SELECT id
          FROM calendrier_ligue
          WHERE num_journee_cal_reel = :numJournee
        )
      )');
    $q2->bindValue(':nbBut', $donnees['but']);
    $q2->bindValue(':nbCsc', $donnees['csc']);
    $q2->bindValue(':cle', $donnees['id']);
    $q2->bindValue(':numJournee', $numJournee);

    $q2->execute();
  }
  $q->closeCursor();
}

function majScoreTempJourneeEnCours($numJournee)
{
	global $bdd;

    $q = $bdd->prepare('UPDATE calendrier_ligue cl
      SET cl.score_dom = (
        SELECT COALESCE(SUM(jce.nb_but_reel),0)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_dom
        AND ce.id_cal_ligue = cl.id
      ),
      cl.score_ext = (
        SELECT COALESCE(SUM(jce.nb_but_reel),0)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_ext
        AND ce.id_cal_ligue = cl.id
      )
      WHERE cl.num_journee_cal_reel = :numJournee');
    $q->bindValue(':numJournee', $numJournee);

    $q->execute();

    // Prise en compte des CSC
    $q = $bdd->prepare('UPDATE calendrier_ligue cl
      SET cl.score_dom = (cl.score_dom + (
        SELECT COALESCE(SUM(jce.nb_csc),0)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_ext
        AND ce.id_cal_ligue = cl.id
      )),
      cl.score_ext = (cl.score_ext + (
        SELECT COALESCE(SUM(jce.nb_csc),0)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_dom
        AND ce.id_cal_ligue = cl.id
      ))
      WHERE cl.num_journee_cal_reel = :numJournee');
      $q->bindValue(':numJournee', $numJournee);

      $q->execute();
}

function maj_scores_journee_en_cours($numJournee)
{
	 majButeurTempJourneeEnCours($numJournee);
	 majScoreTempJourneeEnCours($numJournee);
	 addLogEvent('Mise à jour des buteurs et des scores OK.');
}

//Récupère les effectifs concernés sur une ligue et sur une journée - Renvoie sous forme de tableau
function get_effectifs_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_effectifs_ligue_journee');
	global $bdd;

	$req_effectifs = $bdd->prepare('SELECT t1.id, t3.id_compo, t2.id_equipe, t1.id_equipe_dom, t1.id_equipe_ext, t3.id_joueur_reel, t4.cle_roto_primaire, t3.capitaine, t4.position, t3.numero , t2.code_tactique, t2.code_bonus_malus AS \'code_bonus_malus_equipe\', t3.numero_remplacement, t3.id_joueur_reel_remplacant, t3.note_min_remplacement FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel;');
	$req_effectifs->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_effectifs->fetchAll();
	$req_effectifs->closeCursor();
	return $tab_effectif;
}

//Récupère les effectifs concernés sur une ligue et sur une journée - Renvoie sous forme de tableau
function get_effectifs_titulaires_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_effectifs_titulaires_ligue_journee');
	global $bdd;

	$req_effectifnote = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t1.id_equipe_dom, t1.id_equipe_ext, t3.id_joueur_reel, t4.cle_roto_primaire, t3.capitaine, t4.position, t3.numero , t3.note, t3.note_bonus, t2.code_bonus_malus AS \'code_bonus_malus_equipe\', t3.numero_remplacement, t3.id_joueur_reel_remplacant, t3.note_min_remplacement FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 ;');
	$req_effectifnote->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_effectifnote->fetchAll();
	$req_effectifnote->closeCursor();
	return $tab_effectif;
}

//Récupère les remplaçant concernés sur une ligue, sur une equipe et sur une journée - Renvoie sous forme de tableau
function get_effectifs_remplacant_ligue_journee_equipe($constante_num_journee_cal_reel,$constanteConfrontationLigue,$id_equipe)
{
	addLogEvent('FONCTION get_effectifs_remplacant_ligue_journee_equipe');
	global $bdd;

	$req_remplacant = $bdd->prepare('SELECT  t3.id_joueur_reel, t4.cle_roto_primaire,  t4.position, t3.numero, t3.note, t3.note_bonus, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t2.id_equipe = :id_equipe AND t3.numero_definitif IS NULL ORDER By t3.numero ;');
	$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue, 'id_equipe' => $id_equipe));

	$tab_effectif = $req_remplacant->fetchAll();
	$req_remplacant->closeCursor();
	return $tab_effectif;
}

//Récupère les effectifs titulaires n'ayant pas été remplacés sur une ligue et sur une journée - Renvoie sous forme de tableau
function get_effectifs_non_remplace_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_effectifs_non_remplace_ligue_journee');
	global $bdd;

	$req_effectif_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel, t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NULL ORDER BY id_compo, t3.numero ASC ;');
	$req_effectif_nonRemplace->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_effectif_nonRemplace->fetchAll();
	$req_effectif_nonRemplace->closeCursor();
	return $tab_effectif;
}



//Récupère les confrontations d'une journée sur une ligue - Renvoie sous forme de tableau
function get_confrontations_par_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_confrontations_par_journee');
	global $bdd;

	$req_listeConfrontationParJournee = $bdd->prepare('SELECT id_cal_ligue, t2.id
				FROM calendrier_ligue t1, compo_equipe t2
				WHERE t1.id_ligue = :id_ligue AND t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t1.id_equipe_dom = t2.id_equipe
				UNION SELECT id_cal_ligue, t2.id
				FROM calendrier_ligue t1, compo_equipe t2
				WHERE t1.id_ligue = :id_ligue AND t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t1.id_equipe_ext = t2.id_equipe ORDER BY id_cal_ligue ;');

	$req_listeConfrontationParJournee->execute(array('id_ligue' => $constanteConfrontationLigue, 'num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$tab_confrontation = $req_listeConfrontationParJournee->fetchAll();
	$req_listeConfrontationParJournee->closeCursor();

	return $tab_confrontation;
}

//Récupère les effectifs titulaires n'ayant pas été remplacés sur une ligue et sur une journée - Renvoie sous forme de tableau
function get_attaquants_non_remplace_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_attaquants_non_remplace_ligue_journee');
	global $bdd;

	$req_attaquant_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel, t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.note_bonus, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NULL AND t4.position = \'Forward\' ORDER BY id_compo, t3.numero ASC ;');
	$req_attaquant_nonRemplace->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_attaquant_nonRemplace->fetchAll();
	$req_attaquant_nonRemplace->closeCursor();
	return $tab_effectif;
}

//Récupère joueurs avec remplacement tactique actif - Renvoie sous forme de tableau
function get_joueurAvecRemplacementTactiqueActif($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_joueurAvecRemplacementTactiqueActif');
	global $bdd;

	$req_joueurAvecRemplacementTactiqueActif= $bdd->prepare('SELECT DISTINCT t3.id_joueur_reel, t3.id_compo, t4.cle_roto_primaire, t3.numero, t3.id_joueur_reel_remplacant FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL AND t3.note < t3.note_min_remplacement AND t3.id_joueur_reel_remplacant IN (SELECT t3.id_joueur_reel FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL) ;');

	//constante 17 pour le test uniquement
	$req_joueurAvecRemplacementTactiqueActif->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_joueurAvecRemplacementTactiqueActif->fetchAll();
	$req_joueurAvecRemplacementTactiqueActif->closeCursor();
	return $tab_effectif;
}


//Récupère tous les joueurs titulaires, ayant joué mais n'ayant pas encore de numéro définitif - Renvoie sous forme de tableau
function get_joueurRestants($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_joueurRestants');
	global $bdd;

	$req_joueursRestants= $bdd->prepare('SELECT t3.id_compo, t3.id_joueur_reel, t4.cle_roto_primaire, t3.note, t3.numero, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL ;');

	$req_joueursRestants->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_joueursRestants->fetchAll();
	$req_joueursRestants->closeCursor();
	return $tab_effectif;
}

//Requete qui renvoie la liste des équipes ayant joué sur la journée ainsi que les malus appliquées
function get_effectif_malus_bonus($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_effectif_malus_bonus');
	global $bdd;

	$req_malus_bonus= $bdd->prepare('SELECT t2.id, t1.id_equipe, t2.id_equipe_dom, t2.id_equipe_ext, t1.code_bonus_malus
			FROM compo_equipe t1, calendrier_ligue t2 WHERE t2.id_ligue = :id_ligue AND t2.num_journee_cal_reel = :num_journee_cal_reel AND t2.id = t1.id_cal_ligue ;');

	$req_malus_bonus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));
	$tab_effectif = $req_malus_bonus->fetchAll();
	$req_malus_bonus->closeCursor();
	return $tab_effectif;
}

//Récupère la note d'un joueur sur une journee - Renvoie sous forme de tableau
function get_note_joueur_journee($id_joueur_reel, $constanteJourneeReelle, $id_compo)
{
	addLogEvent('FONCTION get_note_joueur_journee');
	global $bdd;

	$req_noteDuJoueurJournee = $bdd->prepare('SELECT t1.note, t2.id_compo
		FROM joueur_stats t1, joueur_compo_equipe t2, joueur_reel t3
		WHERE t2.id_joueur_reel = :id_joueur_reel AND t1.journee = :journee AND t2.id_joueur_reel = t3.id AND t1.id IN (t3.cle_roto_primaire, t3.cle_roto_secondaire) AND t2.id_compo = :id_compo;');
	$req_noteDuJoueurJournee->execute(array('id_joueur_reel' => $id_joueur_reel, 'journee' => $constanteJourneeReelle, 'id_compo' => $id_compo));

	$tab_note = $req_noteDuJoueurJournee->fetchAll();
	$req_noteDuJoueurJournee->closeCursor();
	return $tab_note;
}

//Vérifie si le capitaine a gagné ou perdu  - Renvoie sous forme de tableau //Renvoie 0 si NUL, 1 si Defaite et 2 si Victoire
function get_victoire_ou_defaite_capitaine($id_joueur_reel, $constanteJourneeReelle)
{
	addLogEvent('FONCTION get_victoire_ou_defaite_capitaine');
	global $bdd;

	$req_victoireOuDefaiteCapitaine = $bdd->prepare('SELECT t3.malus_defaite + 2*t3.bonus_victoire AS \'victoireOuDefaite\' FROM joueur_reel t1, joueur_stats t3 WHERE t1.id = :id AND t3.id IN (t1.cle_roto_primaire, t1.cle_roto_secondaire) AND t3.journee = :journee ;');
	$req_victoireOuDefaiteCapitaine->execute(array('id' => $id_joueur_reel, 'journee' => $constanteJourneeReelle));

	$tab_victoire_ou_defaite = $req_victoireOuDefaiteCapitaine->fetchAll();
	$req_victoireOuDefaiteCapitaine->closeCursor();
	return $tab_victoire_ou_defaite;
}

//Récupère le nombre de défenseur à partir du code tactique  - Renvoie sous forme de tableau
function get_nb_defenseur($code_tactique)
{
	addLogEvent('FONCTION get_nb_defenseur');
	global $bdd;

	$req_nbDefenseur = $bdd->prepare('SELECT nb_def FROM nomenclature_tactique WHERE code = :code_tactique ;');
	$req_nbDefenseur->execute(array('code_tactique' => $code_tactique));

	$tab_nb_defenseur = $req_nbDefenseur->fetchAll();
	$req_nbDefenseur->closeCursor();
	return $tab_nb_defenseur;
}

//Change la note d'un joueur dans une compo
function update_note_joueur_compo($note,$id_compo,$id_joueur_reel)
{
	addLogEvent('FONCTION update_note_joueur_compo');
	global $bdd;

	$upd_noteJoueurCompo = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_noteJoueurCompo->execute(array('note' => $note, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_noteJoueurCompo->closeCursor();
}

//Change le bonus perçu par un joueur dans une compo
function update_note_bonus_joueur_compo($note,$id_compo,$id_joueur_reel)
{
	addLogEvent('FONCTION update_note_bonus_joueur_compo');
	global $bdd;

	$upd_noteBonus = $bdd->prepare('UPDATE joueur_compo_equipe SET note_bonus = :note WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_noteBonus->execute(array('note' => $note, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_noteBonus->closeCursor();
}

//Change le numero définitif d'un joueur en fonction de la compo, et l'id_joueur_reel
function update_numero_definitif($numero_definitif,$id_compo,$id_joueur_reel)
{
	addLogEvent('FONCTION update_numero_definitif');
	global $bdd;

			//On update le numéro définitif
	$upd_numeroDefinitif = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = :numero_definitif WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_numeroDefinitif->execute(array('numero_definitif' => $numero_definitif, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_numeroDefinitif->closeCursor();
}

//Change la note du gardien fonction de la compo
function update_note_gardien($note,$id_compo)
{
	addLogEvent('FONCTION update_note_gardien');
	global $bdd;

	//MALUS FUMIGENE Update note gardien
	$upd_note_gardien = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND numero_definitif = 1 ;');
	$upd_note_gardien->execute(array('note' => $note, 'id_compo' => $id_compo));
	$upd_note_gardien->closeCursor();
}


//Change la note du joueur ciblé par le bonus Famille
function update_note_famille($note,$constante_num_journee_cal_reel, $id_joueur_reel)
{
	addLogEvent('FONCTION update_note_famille');
	global $bdd;

	//BONUS FAMILLE STADE
	$upd_noteFamille = $bdd->prepare('UPDATE compo_equipe t1, calendrier_ligue t2, joueur_compo_equipe t3 SET t3.note = :note WHERE t2.num_journee_cal_reel =  :num_journee_cal_reel AND t2.id = t1.id_cal_ligue AND t1.id = t3.id_compo AND t3.numero_definitif IS NOT NULL AND t3.id_joueur_reel = :id_joueur_reel and t3.note <= 9 ;');
	$upd_noteFamille->execute(array('note' => $note,'num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_joueur_reel' => $id_joueur_reel));
	$upd_noteFamille->closeCursor();
}



//Requete note gardien d'une équipe
function get_note_gardien_equipe($id_equipe, $constante_num_journee_cal_reel)
{
	addLogEvent('FONCTION get_note_gardien_equipe');
	global $bdd;

	$req_note_gardien = $bdd->prepare('SELECT t2.id_compo, t2.note, t2.note_bonus, t2.id_joueur_reel FROM compo_equipe t1, joueur_compo_equipe t2, calendrier_ligue t3 WHERE t1.id_equipe = :id_equipe AND t2.id_compo = t1.id AND t2.numero_definitif = 1 AND t3.id = t1.id_cal_ligue AND t1.id = t2.id_compo AND t3.num_journee_cal_reel = :num_journee_cal_reel ;');
	$req_note_gardien->execute(array('id_equipe' => $id_equipe,'num_journee_cal_reel' => $constante_num_journee_cal_reel));

	$tab_note = $req_note_gardien->fetchAll();
	$req_note_gardien->closeCursor();
	return $tab_note;
}


//Récupere les infos du joueur ciblé par un bonus/malus
function get_joueur_concerne_bonus($constante_num_journee_cal_reel, $id_equipe, $id_cal_ligue)
{
	addLogEvent('FONCTION get_joueur_concerne_bonus');
	global $bdd;
	//Requete Joueur Concerné par le bonus
	$req_joueur_bonus = $bdd->prepare('SELECT t1.id_joueur_reel_equipe, t3.note, t3.note_bonus, t3.id_compo FROM bonus_malus t1, compo_equipe t4, calendrier_ligue t2, joueur_compo_equipe t3  WHERE t2.num_journee_cal_reel = :num_journee_cal_reel AND t2.id = t4.id_cal_ligue AND t4.id = t3.id_compo AND t3.numero_definitif IS NOT NULL AND t3.id_joueur_reel = t1.id_joueur_reel_equipe AND t1.id_equipe = :id_equipe AND t1.id_cal_ligue = :id_cal_ligue ;');
	$req_joueur_bonus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel,'id_equipe' => $id_equipe,'id_cal_ligue' => $id_cal_ligue));

	$tab_joueur = $req_joueur_bonus->fetchAll();
	$req_joueur_bonus->closeCursor();
	return $tab_joueur;
}

function get_buteurs_impactes_malus_dinarb($constante_num_journee_cal_reel, $idLigue)
{
	addLogEvent('FONCTION get_buteurs_impactes_malus_dinarb');
	global $bdd;

	$req_buteurs_impactes_par_malus_dinarb = $bdd->prepare('SELECT IF(t5.id_equipe = cl.id_equipe_dom,
		cl.id_equipe_ext, cl.id_equipe_dom) AS \'id_adversaire\', cl.id, t4.id_compo, t4.id_joueur_reel, t4.nb_but_reel
		FROM joueur_compo_equipe t4, compo_equipe t5, calendrier_ligue cl
		WHERE cl.id = t5.id_cal_ligue AND t5.id = t4.id_compo AND t4.numero_definitif IS NOT NULL
		AND t4.nb_but_reel > 0 AND cl.num_journee_cal_reel = :num_journee_cal_reel AND cl.id_ligue = :idLigue
		AND t5.id_equipe IN(
			SELECT t1.id_equipe_dom
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3
			WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :idLigue AND t1.id = t2.id_cal_ligue
			AND t2.code_bonus_malus = \'DIN_ARB\' AND t3.id_compo = t2.id AND t1.id_equipe_dom != t2.id_equipe
			UNION SELECT t1.id_equipe_ext
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3
			WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :idLigue AND t1.id = t2.id_cal_ligue
			AND t2.code_bonus_malus = \'DIN_ARB\' AND t3.id_compo = t2.id AND t1.id_equipe_ext != t2.id_equipe);');
	$req_buteurs_impactes_par_malus_dinarb->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'idLigue' => $idLigue));
	$tab_joueur = $req_buteurs_impactes_par_malus_dinarb->fetchAll();
	$req_buteurs_impactes_par_malus_dinarb->closeCursor();
	return $tab_joueur;
}


//Remise à Null de tous les numéros définitifs d'une compo
function remise_a_null_numero_definitif_compo($id_compo)
{
	addLogEvent('FONCTION remise_a_null_numero_definitif_compo');
	global $bdd;

	$upd_remiseANullDesNumerosDefinitifs = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = NULL WHERE id_compo = :id_compo ;');
	$upd_remiseANullDesNumerosDefinitifs->execute(array('id_compo' => $id_compo));
	$upd_remiseANullDesNumerosDefinitifs->closeCursor();
}

//Remise à Null de tous les buts reels d'une compo
function remise_a_null_buts_reels_compo($id_compo)
{
	addLogEvent('FONCTION remise_a_null_buts_reels_compo');
	global $bdd;

	$upd_remiseANullDesButsReels = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = NULL WHERE id_compo = :id_compo ;');
	$upd_remiseANullDesButsReels->execute(array('id_compo' => $id_compo));
	$upd_remiseANullDesButsReels->closeCursor();
}

//On change le nb de but virtuel d'un joueur selon son id, sa compo et sa journee
function update_but_virtuel($but,$id_compo, $id_joueur_reel)
{
	addLogEvent('FONCTION update_but_virtuel');
	global $bdd;

	$upd_butVirtuel = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_virtuel = :nb_but_virtuel WHERE joueur_compo_equipe.id_compo = :id_compo AND joueur_compo_equipe.id_joueur_reel = :id_joueur_reel;');
	$upd_butVirtuel->execute(array('nb_but_virtuel' => $but, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_butVirtuel->closeCursor();
}

//Ajoute en table BONUS MALUS le joueur impacté par le malus DIN_ARB
function update_buteur_impacte_malus_dinarb($id_joueur_reel_adverse,$id_equipe,$id_cal_ligue)
{
	addLogEvent('FONCTION update_buteur_impacte_malus_dinarb');
	global $bdd;
	$upd_buteur_impacte_par_malus_dinarb = $bdd->prepare('UPDATE bonus_malus SET id_joueur_reel_adverse = :id_joueur_reel_adverse WHERE id_equipe = :id_equipe AND id_cal_ligue = :id_cal_ligue;');
	$upd_buteur_impacte_par_malus_dinarb->execute(array('id_joueur_reel_adverse' => $id_joueur_reel_adverse,'id_equipe' => $id_equipe, 'id_cal_ligue' => $id_cal_ligue));
	$upd_buteur_impacte_par_malus_dinarb->closeCursor();
}


function modification_but_reel_joueur($nb_but_reel, $id_compo, $id_joueur_reel)
{
	addLogEvent('FONCTION modification_but_reel_joueur');
	global $bdd;
	$upd_nb_but_buteur = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = :nb_but_reel WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_nb_but_buteur->execute(array('nb_but_reel' => $nb_but_reel, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_nb_but_buteur->closeCursor();
}

//On change le nb de but reel d'un joueur selon son id, sa compo et sa journee A PARTIR DES STATS
function updateButReelDuJoueur($id_compo, $journee, $id_joueur_reel)
{
	addLogEvent('FONCTION remise_a_null_numero_definitif_compo');
	global $bdd;

	//On compte le nombre de but réel d'un joueur sur une journée
	$req_nbButReel=$bdd->prepare('SELECT t3.but FROM joueur_compo_equipe t1, joueur_reel t2, joueur_stats t3 WHERE t1.id_joueur_reel = t2.id AND t3.id IN (t2.cle_roto_primaire, t2.cle_roto_secondaire) AND t3.journee = :journee AND t1.id_joueur_reel = :id_joueur_reel AND t1.id_compo = :id_compo');

	//On update un but réel
	$upd_butReel= $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = :nb_but_reel WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');

	//On regarde le nombre de but réel marqué par ce joueur sur cette journée
	$req_nbButReel->execute(array('journee' => $journee, 'id_joueur_reel' => $id_joueur_reel, 'id_compo' => $id_compo));
	$lignesNbButReel = $req_nbButReel->fetchAll();
	if (count($lignesNbButReel) > 1) {
		//Erreur, il ne doit y avoir qu'une seule ligne par joueur par journée
		addLogEvent('Erreur le joueur : '.$id_joueur_reel.' a plusieurs lignes de stat sur la journée : '.$journee);
	}else{
		foreach ($lignesNbButReel as $ligneNbButReel) {
			if($ligneNbButReel['but']>0){
				//Ce joueur a marqué au moins un but durant cette journée
				addLogEvent($id_joueur_reel.' a marqué '.$ligneNbButReel['but'].' but(s) réel(s)');
				$upd_butReel->execute(array('nb_but_reel' => $ligneNbButReel['but'], 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
				$upd_butReel->closeCursor();
			}
		}
	}
	$req_nbButReel->closeCursor();
}

// Calcul les buts et buts virtuels des confrontations ayant lieu sur la journée
// $ligue_unique : permet de faire tourner le script uniquement sur cette ligue, si sa valeur est null alors on cherche sur toutes les ligues
// $maj_stats_classement : permet de définir si la maj du classement et des stats doivent être faites.
function calculer_confrontations_journee($constante_num_journee_cal_reel, $ligue_unique, $maj_stats_classement)
{
	addLogEvent('FONCTION calculer_confrontations_journee');
	global $bdd;

	$constanteJourneeReelle = get_journee_format_long($constante_num_journee_cal_reel);
	$req_ligues_concernees = $bdd->prepare('SELECT distinct id_ligue FROM calendrier_ligue WHERE num_journee_cal_reel = :num_journee_cal_reel;');

	$ligue_concernee = array();
	if(is_null($ligue_unique))
	{
		$ligues_concernees = get_ligues_concernees_journee($constante_num_journee_cal_reel, $req_ligues_concernees);
	}else{
		$ligues_concernees['id_ligue'] = $ligue_unique;
	}

	raz_table_joueur_compo_equipe_sur_journee($constante_num_journee_cal_reel,$ligue_unique);

	if (count($ligues_concernees) == 0 || empty($ligues_concernees)) {
		addLogEvent('Aucune ligue sur la journee '.$constante_num_journee_cal_reel);
	} else {
		foreach ($ligues_concernees as $ligue_concernee)
		{
			$constanteConfrontationLigue = $ligue_concernee['id_ligue'];
			addLogEvent( ' **************************** LIGUE n°'.$constanteConfrontationLigue.' ********************************');
			addLogEvent( ' ************************ CALCUL ET UPDATE DES NOTES **************************');
		/*
			Boucle pour
						- update des notes obtenues après-bonus dans la table joueur_compo_equipe
						- cumul des buts réels
						- calculs des moyennes par ligne
						- détection des buts virtuels
						- update des scores de la confrontation
		*/

			$tab_effectif = get_effectifs_ligue_journee($constante_num_journee_cal_reel, $constanteConfrontationLigue);
			foreach($tab_effectif as $donnees)
			{
				$note = 0; //Note de base pour un joueur absent
				$note_bonus = 0; //Bonus de base
				$rows = get_note_joueur_journee($donnees['id_joueur_reel'],$constanteJourneeReelle,$donnees['id_compo']);
				if (count($rows) == 0) {
					addLogEvent(  $donnees['cle_roto_primaire'].' n\'a pas joué sur la journée '.$constanteJourneeReelle);
				} else {
					foreach ($rows as $row) {
						if ($row['note'] == 0){
							$note = 0;
							addLogEvent($donnees['cle_roto_primaire'].' n\'est pas rentré '.$constanteJourneeReelle);
						}else{
							$note = $row['note'];

							//test ajout bonus capitaine
							$LignesVictoireOuPas = get_victoire_ou_defaite_capitaine($donnees['id_joueur_reel'],$constanteJourneeReelle);
							foreach ($LignesVictoireOuPas as $victoireOuPas){
								if($victoireOuPas['victoireOuDefaite'] == 2 && $donnees['capitaine'] == 1){
									//Le joueur est capitaine et son équipe a gagné => BONUS
									$note += 0.5;
									$note_bonus = 0.5;
									addLogEvent( 'Capitaine Victoire');
								}else{
									if($victoireOuPas['victoireOuDefaite'] == 1 && $donnees['capitaine'] == 1){
										//Le joueur est capitaine et son équipe a perdu => MALUS
										$note -= 1;
										$note_bonus = -1;
										addLogEvent(' Capitaine Defaite ');
									}
								}
							}

							//ajout bonus defense
							$LignesNbDefenseur = get_nb_defenseur($donnees['code_tactique']);
							foreach ($LignesNbDefenseur as $nbDefenseur){
								if($nbDefenseur['nb_def'] == 5 && $donnees['position'] == 'Defender' && $donnees['numero'] <= 11){
									//Defense à 5, les défenseurs titulaires prennent un bonus
									$note += 1;
									$note_bonus += 1;
									addLogEvent( 'Défense à 5');
								}else{
									if($nbDefenseur['nb_def'] == 4 && $donnees['position'] == 'Defender' && $donnees['numero'] <= 11){
										//Defense à 5, les défenseurs titulaires prennent un bonus
										$note += 0.5;
										$note_bonus += 0.5;
										addLogEvent( 'Défense à 4');
									}
								}
							}

							//ajout bonus/malus (A FAIRE)
							if($donnees['code_bonus_malus_equipe'] == 'CON_ZZ'){
								$note += 0.5;
								$note_bonus += 0.5;
							}

							//Vérification des plafonds
							if($note > 10){
								$note = 10;
							}else{
								if($note < 0.5){
									$note = 0.5;
								}
							}

							/*  UPDATE DES NOTES DANS LA TABLE */
							update_note_joueur_compo($note, $donnees['id_compo'], $donnees['id_joueur_reel']);
							addLogEvent($donnees['cle_roto_primaire'].' a eu la note de '.$note.' sur la journée '.$constanteJourneeReelle);
							update_note_bonus_joueur_compo($note_bonus, $donnees['id_compo'], $donnees['id_joueur_reel']);
							addLogEvent( 'MAJ note bonus '.$note_bonus);
						}
					}
				}
			}

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 1 **************************');

			//On compte le nombre de but réel d'un joueur sur une journée
			$req_nbButReel=$bdd->prepare('SELECT t3.but FROM joueur_compo_equipe t1, joueur_reel t2, joueur_stats t3 WHERE t1.id_joueur_reel = t2.id AND t3.id IN (t2.cle_roto_primaire, t2.cle_roto_secondaire) AND t3.journee = :journee AND t1.id_joueur_reel = :id_joueur_reel');

			//On update un but réel
			$upd_butReel= $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = :nb_but_reel WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');

			$derniereCompoMAJ = 0;
			//################## Première boucle ############################
			//Ici on passe en revue tous les joueurs titulaires dans les compos
			// On update le numéro définitif des joueurs ayant joués et qui n'ont pas de remplacement tactique
			// On effectue les remplacements poste pour poste
			// On acte le fait que défenseurs absents n'ayant pas de remplaçant seront définitivement absents
			// On update les buts réels marqués par les joueurs ayant reçu un numéro définitif dans la compo

			$tab_effectif = get_effectifs_titulaires_ligue_journee($constante_num_journee_cal_reel, $constanteConfrontationLigue);
			foreach($tab_effectif as $donnees)
			{
				if($derniereCompoMAJ != $donnees['id_compo']){
					$derniereCompoMAJ = $donnees['id_compo'];
					remise_a_null_numero_definitif_compo($donnees['id_compo']);
					remise_a_null_buts_reels_compo($donnees['id_compo']);
				}

				if($donnees['note'] == 0 || is_null($donnees['note'])){
					$estRemplace = 0;
					$lignesRemplacant = get_effectifs_remplacant_ligue_journee_equipe($constante_num_journee_cal_reel, $constanteConfrontationLigue,$donnees['id_equipe']);
					if (count($lignesRemplacant) == 0) {
						//Aucun remplaçant, le joueur reste dans la compo
						addLogEvent( 'Aucun remplaçant, le joueur '.$donnees['cle_roto_primaire'].' reste dans la compo');
						//On update. Le numéro définitif devient le numéro initialement prévu
						update_numero_definitif($donnees['numero'],$donnees['id_compo'],$donnees['id_joueur_reel']);
					} else {
						foreach ($lignesRemplacant as $ligneRemplacant) {
							if($ligneRemplacant['position'] == $donnees['position'] && $ligneRemplacant['note'] > 0  && $estRemplace == 0){
								//Il existe un remplacement poste pour poste
								$estRemplace = 1;
								addLogEvent('Remplacement de '.$donnees['cle_roto_primaire'].' par le même poste '.$ligneRemplacant['cle_roto_primaire']);
								//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
								update_numero_definitif($donnees['numero'],$donnees['id_compo'],$ligneRemplacant['id_joueur_reel']);
								//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
								updateButReelDuJoueur($donnees['id_compo'], $constanteJourneeReelle, $ligneRemplacant['id_joueur_reel']);
								//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
								update_numero_definitif(0,$donnees['id_compo'],$donnees['id_joueur_reel']);
							}
						}
						if($donnees['position'] == 'Defender' && $estRemplace == 0){
								//Si il n'existe pas de remplacement poste pour poste pour un défenseur alors le joueur ne peut pas être remplacé
								addLogEvent( 'Aucun défenseur remplaçant, le joueur '.$donnees['cle_roto_primaire'].' reste dans la compo');
								//On update. Le numéro définitif devient le numéro initialement prévu
								update_numero_definitif($donnees['numero'],$donnees['id_compo'],$donnees['id_joueur_reel']);
						}
					}
				}else{
					if(is_null($donnees['numero_remplacement'])){
						//Le joueur a une note et ne fait l'objet d'aucun remplacement tactique donc il est directement dans l'effectif définitif
						addLogEvent( $donnees['cle_roto_primaire'].' a joué et n\'est pas remplacé ');
						//On update. Le numéro définitif du joueur avec son numéro initial
						update_numero_definitif($donnees['numero'],$donnees['id_compo'],$donnees['id_joueur_reel']);
						//On regarde le nombre de but réel marqué par ce joueur sur cette journée
						updateButReelDuJoueur($donnees['id_compo'], $constanteJourneeReelle, $donnees['id_joueur_reel']);
					}
				}
			}

			//################## Deuxième boucle ############################
			//Ici on passe en revue tous les joueurs titulaire dans les compos mais absents et n'ayant pas eu de remplacement poste pour poste
			// On vérifie si un joueur de la ligne inférieur a joué parmis les remplaçants encore disponibles
			// On applique une minoration de la note si il y a remplacement

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 2 **************************');

			$tab_effectif = get_effectifs_non_remplace_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue);
			foreach($tab_effectif as $donneesEffectifNonRemplace)
			{
				$estRemplace = 0;
				$lignesRemplacant = get_effectifs_remplacant_ligue_journee_equipe($constante_num_journee_cal_reel,$constanteConfrontationLigue,$donneesEffectifNonRemplace['id_equipe']);
				if (count($lignesRemplacant) == 0) {
					//Aucun remplaçant, le joueur reste dans la compo
					addLogEvent( 'Aucun remplaçant, le joueur '.$donneesEffectifNonRemplace['cle_roto_primaire'].' reste dans la compo');
					//On update. Le numéro définitif devient le numéro initialement prévu
					update_numero_definitif($donneesEffectifNonRemplace['numero'],$donneesEffectifNonRemplace['id_compo'],$donneesEffectifNonRemplace['id_joueur_reel']);
				}else{
					foreach ($lignesRemplacant as $ligneRemplacant) {
						if((($donneesEffectifNonRemplace['position'] == 'Midfielder' && $ligneRemplacant['position'] == 'Defender') || ($donneesEffectifNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Midfielder')) && $ligneRemplacant['note']>0 && $estRemplace == 0 ){
							//Il existe un remplacement par le poste du dessous
							$estRemplace = 1;
							addLogEvent( 'Remplacement de '.$donneesEffectifNonRemplace['cle_roto_primaire'].' par le poste inférieur '.$ligneRemplacant['cle_roto_primaire'] );
							//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
							update_numero_definitif($donneesEffectifNonRemplace['numero'],$donneesEffectifNonRemplace['id_compo'],$ligneRemplacant['id_joueur_reel']);

							//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
							updateButReelDuJoueur($donneesEffectifNonRemplace['id_compo'], $constanteJourneeReelle, $ligneRemplacant['id_joueur_reel']);

							//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
							update_numero_definitif(0,$donneesEffectifNonRemplace['id_compo'],$donneesEffectifNonRemplace['id_joueur_reel']);

							//On update. La note du joueur remplaçant baisse de 1 car le poste est différent
							if($ligneRemplacant['note']-1<0.5){
								$nouvelleNote = 0.5;
							}else{
								$nouvelleNote = $ligneRemplacant['note']-1;
							}

							update_note_joueur_compo($nouvelleNote, $donneesEffectifNonRemplace['id_compo'], $ligneRemplacant['id_joueur_reel']);
							update_note_bonus_joueur_compo($ligneRemplacant['note_bonus']-1,$donneesEffectifNonRemplace['id_compo'],$ligneRemplacant['id_joueur_reel']);
							addLogEvent( 'Note - 1 ');
						}
					}
					if($donneesEffectifNonRemplace['position'] == 'Midfielder' && $estRemplace == 0){
						//Si il n'existe pas de remplacement d'un défenseur pour un milieu alors le joueur ne peut pas être remplacé
						addLogEvent( 'Aucun défenseur pour remplacer le milieu '.$donneesEffectifNonRemplace['cle_roto_primaire'].' reste dans la compo');
						//On update. Le numéro définitif devient le numéro initialement prévu
						update_numero_definitif($donneesEffectifNonRemplace['numero'],$donneesEffectifNonRemplace['id_compo'],$donneesEffectifNonRemplace['id_joueur_reel']);
					}
				}
			}

			//################## Troisième boucle ############################
			//Ici on passe en revue tous les attaquants titulaires dans les compos mais absents et n'ayant pas eu de remplacement par un attaquant ou un milieu
			// On vérifie si un défenseur a joué parmis les remplaçants encore disponibles
			// On applique une minoration de la note si il y a remplacement

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 3 **************************');

			//constante 17 pour le test uniquement
			$tab_effectif = get_attaquants_non_remplace_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue);
			foreach($tab_effectif as $donneesAttaquantNonRemplace)
			{
				$estRemplace = 0;

				$lignesRemplacant = get_effectifs_remplacant_ligue_journee_equipe($constante_num_journee_cal_reel,$constanteConfrontationLigue,$donneesAttaquantNonRemplace['id_equipe']);
				if (count($lignesRemplacant) == 0) {
					//Aucun remplaçant, le joueur reste dans la compo
					addLogEvent( 'Aucun remplaçant, le joueur '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' reste dans la compo');
					//On update. Le numéro définitif devient le numéro initialement prévu
					update_numero_definitif($donneesAttaquantNonRemplace['numero'],$donneesAttaquantNonRemplace['id_compo'],$donneesAttaquantNonRemplace['id_joueur_reel']);
				}else{
					foreach ($lignesRemplacant as $ligneRemplacant) {
						if($donneesAttaquantNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Defender'&& $ligneRemplacant['note']>0 && $estRemplace == 0 ){
							//Il existe un remplacement par le poste du dessous
							$estRemplace = 1;
							addLogEvent( 'Remplacement de l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' par un défenseur '.$ligneRemplacant['cle_roto_primaire'] );
							//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
							update_numero_definitif($donneesAttaquantNonRemplace['numero'],$donneesAttaquantNonRemplace['id_compo'],$ligneRemplacant['id_joueur_reel']);
							//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
							updateButReelDuJoueur($donneesAttaquantNonRemplace['id_compo'], $constanteJourneeReelle, $ligneRemplacant['id_joueur_reel']);

							//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
							update_numero_definitif(0,$donneesAttaquantNonRemplace['id_compo'],$donneesAttaquantNonRemplace['id_joueur_reel']);

							//On update. La note du joueur remplaçant baisse de 2 car le poste est très différent
							//On update. La note du joueur remplaçant baisse de 1 car le poste est différent
							if($ligneRemplacant['note']-2<0.5){
								$nouvelleNote = 0.5;
							}else{
								$nouvelleNote = $ligneRemplacant['note']-2;
							}
							update_note_joueur_compo($nouvelleNote, $donneesAttaquantNonRemplace['id_compo'], $ligneRemplacant['id_joueur_reel']);
							update_note_bonus_joueur_compo($ligneRemplacant['note_bonus']-2,$donneesAttaquantNonRemplace['id_compo'],$ligneRemplacant['id_joueur_reel']);
							addLogEvent( 'Note - 2 ');
						}
					}
					if($donneesAttaquantNonRemplace['position'] == 'Forward' && $estRemplace == 0){
						//Si il n'existe pas de remplacement d'un défenseur pour un attaquant alors le joueur ne peut pas être remplacé
						addLogEvent( 'Aucun défenseur pour remplacer l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' reste dans la compo');
						//On update. Le numéro définitif devient le numéro initialement prévu
						update_numero_definitif($donneesAttaquantNonRemplace['numero'],$donneesAttaquantNonRemplace['id_compo'],$donneesAttaquantNonRemplace['id_joueur_reel']);
					}
				}
			}

			//################## Quatrième boucle ############################
			//Ici on passe en revue tous les joueurs ayant un remplacement tactique programmé
			// On vérifie que le remplaçant n'est pas déjà entré dans l'effectif
			// On vérifie si le remplacement tactique s'applique
			// On update le remplacement

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 4 **************************');

			//Requete qui renvoie tous les joueurs titulaires, avec une note inférieur à la note minimum du remplacement tactique et un remplaçant ayant joué encore sur le banc
			$tab_effectif = get_joueurAvecRemplacementTactiqueActif($constante_num_journee_cal_reel,$constanteConfrontationLigue);
			foreach($tab_effectif as $donneesRemplacementTactique)
			{
				//Il existe un remplacement tactique
				addLogEvent( 'Remplacement Tactique de '.$donneesRemplacementTactique['cle_roto_primaire'].' par le joueur avec l\'id : '.$donneesRemplacementTactique['id_joueur_reel_remplacant']) ;
				//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
				update_numero_definitif($donneesRemplacementTactique['numero'],$donneesRemplacementTactique['id_compo'],$donneesRemplacementTactique['id_joueur_reel_remplacant']);

				//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
				updateButReelDuJoueur($donneesRemplacementTactique['id_compo'], $constanteJourneeReelle, $donneesRemplacementTactique['id_joueur_reel_remplacant']);

				//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
				update_numero_definitif(0,$donneesRemplacementTactique['id_compo'],$donneesRemplacementTactique['id_joueur_reel_remplacant']);
			}

			//################## Cinquième boucle ############################
			// Ici on passe en revue tous les joueurs titulaires pour qui le remplacement tactique ne s'est pas appliqué
			// On update les numéros définitifs
			// L'équipe doit être complète

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 5 **************************');

			$tab_effectif = get_joueurRestants($constante_num_journee_cal_reel,$constanteConfrontationLigue);
			foreach($tab_effectif as $donneesJoueursRestants)
			{
				//le remplacement tactique n'était pas possible
				addLogEvent( 'Le remplacement tactique de '.$donneesJoueursRestants['cle_roto_primaire'].' n\'était pas possible ');
				//On update. Le numéro définitif du joueur
				update_numero_definitif($donneesJoueursRestants['numero'],$donneesJoueursRestants['id_compo'],$donneesJoueursRestants['id_joueur_reel']);

				//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
				updateButReelDuJoueur($donneesJoueursRestants['id_compo'], $constanteJourneeReelle, $donneesJoueursRestants['id_joueur_reel']);
			}

			//################## Sixième boucle ############################
			// Ici on applique les malus infligés par l'équipe adverse et affectant les notes des joueurs
			addLogEvent( ' ************************ APPLICATION DES MALUS ADVERSAIRE - BOUCLE 6 **************************');

			//Requete Joueur Concerné par le bonus
			$req_joueur_bonus = $bdd->prepare('SELECT t1.id_joueur_reel_equipe, t3.note, t3.note_bonus, t3.id_compo FROM bonus_malus t1, compo_equipe t4, calendrier_ligue t2, joueur_compo_equipe t3  WHERE t2.num_journee_cal_reel = :num_journee_cal_reel AND t2.id = t4.id_cal_ligue AND t4.id = t3.id_compo AND t3.numero_definitif IS NOT NULL AND t3.id_joueur_reel = t1.id_joueur_reel_equipe AND t1.id_equipe = :id_equipe AND t1.id_cal_ligue = :id_cal_ligue ;');

			//BONUS FAMILLE STADE
			$upd_noteFamille = $bdd->prepare('UPDATE compo_equipe t1, calendrier_ligue t2, joueur_compo_equipe t3 SET t3.note = :note WHERE t2.num_journee_cal_reel =  :num_journee_cal_reel AND t2.id = t1.id_cal_ligue AND t1.id = t3.id_compo AND t3.numero_definitif IS NOT NULL AND t3.id_joueur_reel = :id_joueur_reel and t3.note <= 9 ;');

			//MALUS FUMIGENE Requete note gardien d'une équipe
			$req_note_gardien = $bdd->prepare('SELECT t2.id_compo, t2.note, t2.note_bonus, t2.id_joueur_reel FROM compo_equipe t1, joueur_compo_equipe t2, calendrier_ligue t3 WHERE t1.id_equipe = :id_equipe AND t2.id_compo = t1.id AND t2.numero_definitif = 1 AND t3.id = t1.id_cal_ligue AND t1.id = t2.id_compo AND t3.num_journee_cal_reel = :num_journee_cal_reel ;');

			//MALUS FUMIGENE Update note gardien
			$upd_note_gardien = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND numero_definitif = 1 ;');

			$tab_effectif = get_effectif_malus_bonus($constante_num_journee_cal_reel, $constanteConfrontationLigue);
			foreach($tab_effectif as $donneesMalusBonus)
			{
				if(is_null($donneesMalusBonus['code_bonus_malus'])){
						//Pas de malus/bonus pour cette équipe
						addLogEvent( 'L\'équipe '.$donneesMalusBonus['id_equipe'].' n\'a pas mis de bonus/malus ');
				}else{
					if($donneesMalusBonus['code_bonus_malus'] == 'FUMIGENE'){
						if($donneesMalusBonus['id_equipe'] == $donneesMalusBonus['id_equipe_dom']){
							//Léquipe qui a mis ce malus/bonus est l'équipe domicile
							addLogEvent( 'L\'équipe '.$donneesMalusBonus['id_equipe'].' a mis un Fumigène à l\'équipe '.$donneesMalusBonus['id_equipe_ext']);
							$ligneNoteGardienAdverse = get_note_gardien_equipe($donneesMalusBonus['id_equipe_ext'], $constante_num_journee_cal_reel);
							if(count($ligneNoteGardienAdverse) == 1) {
								//Pas d'erreur on a qu'un seul retour
								foreach ($ligneNoteGardienAdverse as $noteGardienAdverse) {
									if($noteGardienAdverse['note'] <= 0.5 || is_null($noteGardienAdverse['note'])){
										//Fumigene non appliqué car pas de gardien ou gardien a déjà la note minimum
										addLogEvent( 'Impossible d\'appliquer le fumigene sur le gardien adverse (note minimum ou tontonpat)');
									}else{
										$noteUpdateGardien = $noteGardienAdverse['note'] - 1;
										if($noteGardienAdverse['note'] == 1){
											$noteUpdateGardien = 0.5 ;
										}

										update_note_gardien($noteUpdateGardien,$noteGardienAdverse['id_compo']);
										addLogEvent( 'FUMIGENE: Malus de -1 sur le gardien de la compo '.$noteGardienAdverse['id_compo']);

										update_note_bonus_joueur_compo($noteGardienAdverse['note_bonus']-1,$noteGardienAdverse['id_compo'],$noteGardienAdverse['id_joueur_reel']);
										addLogEvent( 'MAJ note bonus '.$note_bonus.' ' );
									}
								}
							}
						}else{
							//Léquipe qui a mis ce malus/bonus est l'équipe visiteur
							addLogEvent( 'L\'équipe '.$donneesMalusBonus['id_equipe'].' a mis un Fumigène à l\'équipe '.$donneesMalusBonus['id_equipe_dom']);
							$ligneNoteGardienAdverse = get_note_gardien_equipe($donneesMalusBonus['id_equipe_dom'], $constante_num_journee_cal_reel);
							if(count($ligneNoteGardienAdverse) == 1) {
								//Pas d'erreur on a qu'un seul retour
								foreach ($ligneNoteGardienAdverse as $noteGardienAdverse) {
									if($noteGardienAdverse['note'] <= 0.5 || is_null($noteGardienAdverse['note'])){
										//Fumigene non appliqué car pas de gardien ou gardien a déjà la note minimum
										addLogEvent( 'Impossible d\'appliquer le fumigene sur le gardien adverse (note minimum ou tontonpat)');
									}else{
										$noteUpdateGardien = $noteGardienAdverse['note'] - 1;
										if($noteGardienAdverse['note'] == 1){
											$noteUpdateGardien = 0.5 ;
										}

										update_note_gardien($noteUpdateGardien,$noteGardienAdverse['id_compo']);
										addLogEvent( 'FUMIGENE: Malus de -1 sur le gardien de la compo '.$noteGardienAdverse['id_compo']);

										update_note_bonus_joueur_compo($noteGardienAdverse['note_bonus']-1,$noteGardienAdverse['id_compo'],$noteGardienAdverse['id_joueur_reel']);
										addLogEvent( 'MAJ note bonus '.$note_bonus.' ' );
									}
								}
							}
						}
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'FAM_STA'){
						//Bonus Famille dans le stade, +1 pour le joueur choisi si dans compo finale et si note <= 9
						$lignesJoueurBonus = get_joueur_concerne_bonus($constante_num_journee_cal_reel,$donneesMalusBonus['id_equipe'],$donneesMalusBonus['id']);
						foreach ($lignesJoueurBonus as $idJoueurBonus) {
							update_note_famille($idJoueurBonus['note']+1,$constante_num_journee_cal_reel,$idJoueurBonus['id_joueur_reel_equipe']);
							addLogEvent( $donneesMalusBonus['code_bonus_malus'].' sur le joueur_reel '.$idJoueurBonus['id_joueur_reel_equipe'].' qui a déjà la note de '.$idJoueurBonus['note']);
							update_note_bonus_joueur_compo($idJoueurBonus['note_bonus']+1,$idJoueurBonus['id_compo'],$idJoueurBonus['id_joueur_reel_equipe']);
							addLogEvent( 'MAJ note bonus '.$note_bonus.' ' );
						}
						$req_joueur_bonus->closeCursor();
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'BOUCHER'){
						//A FAIRE
						//Un joueur à 0 et sans but dans chaque camp
						addLogEvent( $donneesMalusBonus['code_bonus_malus']);
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'BUS'){

						//A FAIRE
						//Pas de but virtuel
						addLogEvent( $donneesMalusBonus['code_bonus_malus']);
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'CHA_GB'){

						//A FAIRE
						//Remplacement tactique sur le gardien
						addLogEvent( $donneesMalusBonus['code_bonus_malus']);
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'CON_ZZ'){

						//A FAIRE
						//+0.5 pour toute l'équipe
						addLogEvent( $donneesMalusBonus['code_bonus_malus']);
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'DIN_ARB'){
						//A FAIRE
						//1 but reel adverse en moins
						addLogEvent( $donneesMalusBonus['code_bonus_malus'].' (traité en boucle 8)')	;
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'MAU_CRA'){

						//A FAIRE
						//Note de -1 pour un joueur adverse
						addLogEvent( $donneesMalusBonus['code_bonus_malus']);
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'PAR_TRU'){

						//A FAIRE
						//But doublé pour un joueur sur une mi-temps
						addLogEvent( $donneesMalusBonus['code_bonus_malus']);
					}elseif($donneesMalusBonus['code_bonus_malus'] == 'SEL_TRI'){

						//A FAIRE
						//+0.5 pour les joueurs français
						addLogEvent( $donneesMalusBonus['code_bonus_malus']);
					}
				}
			}

			//################## Septième boucle ############################
			// Ici on passe en revue tous les joueurs titulaires pour qui le remplacement tactique ne s'est pas appliqué
			// On update les numéros définitifs
			// L'équipe doit être complète

			addLogEvent( ' ************************ CALCUL BUT VIRTUEL EQUIPE - BOUCLE 7 **************************');

			// Calcul But Virtuel
			$i=0;
			$tab_confrontation = get_confrontations_par_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue);
			foreach($tab_confrontation as $listeConfrontationParJournee)
			{
				if($i==0){
					$equipeA = $listeConfrontationParJournee['id'];
					$i++;
				}else{
					$equipeB = $listeConfrontationParJournee['id'];
					$i=0;
					calculButVirtuel($equipeA,$equipeB);
				}
			}


			//################## Huitième boucle ############################
			// Ici supprime le but réel d'un joueur en réponse au Malus DIN_ARB

			addLogEvent( ' ************************ MALUS DIN_ARB - BOUCLE 8 **************************');
			$id_compo_deja_affecte=-1;
			$upd_buteur_impacte_par_malus_dinarb = $bdd->prepare('UPDATE bonus_malus SET id_joueur_reel_adverse = :id_joueur_reel_adverse WHERE id_equipe = :id_equipe AND id_cal_ligue = :id_cal_ligue;');

			$tab_buteurs = get_buteurs_impactes_malus_dinarb($constante_num_journee_cal_reel, $constanteConfrontationLigue);
			foreach($tab_buteurs as $listeButeursImpactesMalusDinArb)
			{
				if($listeButeursImpactesMalusDinArb['id_compo'] != $id_compo_deja_affecte)
				{
					$id_compo_deja_affecte = $listeButeursImpactesMalusDinArb['id_compo'];

					//Update -1 sur le but réel d'un joueur
					modification_but_reel_joueur($listeButeursImpactesMalusDinArb['nb_but_reel'] - 1,$id_compo_deja_affecte,$listeButeursImpactesMalusDinArb['id_joueur_reel']);
					update_buteur_impacte_malus_dinarb($listeButeursImpactesMalusDinArb['id_joueur_reel'],$listeButeursImpactesMalusDinArb['id_aversaire'],$listeButeursImpactesMalusDinArb['id']);

					addLogEvent('Joueur avec id : '.$listeButeursImpactesMalusDinArb['id_joueur_reel'].' perd 1 but réel [MALUS DIN ARB] (compo=' . $id_compo_deja_affecte . ')');
				}
			}
			//Application des malus équipe de l'adversaire (MAJ Note)
		}	//FIN DE BOUCLE FOR EACH SUR LA LIGUE
	}	//FIN DU IF SUR LA LIGUE

	addLogEvent( ' ************************ NETTOYAGE DES NUMEROS DEFINITIFS A ZERO ET DES NOTES DES REMPLACANTS **************************');;
	nettoyage_joueur_compo_equipe();
	impactCSC($constanteJourneeReelle, $constante_num_journee_cal_reel);

	if ($maj_stats_classement) {
		mise_a_jour_stat_classement($constante_num_journee_cal_reel, $constanteJourneeReelle, $req_ligues_concernees);
	}
}

function getLiguesATraiter($numJournee)
{
	global $bdd;

  $q = $bdd->prepare('SELECT DISTINCT(id_ligue) FROM calendrier_ligue WHERE num_journee_cal_reel = :num');
  $q->execute([':num' => $numJournee]);

  $ligues = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $ligues[] = $donnees['id_ligue'];
  }
  $q->closeCursor();

  return $ligues;
}

function getEquipesParLigue($idLigue)
{
	global $bdd;

  $q = $bdd->prepare('SELECT id FROM equipe WHERE id_ligue = :id');
  $q->execute([':id' => $idLigue]);

  $equipes = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $equipes[] = $donnees['id'];
  }
  $q->closeCursor();

  return $equipes;
}

function majMoyenneEquipe($idEquipe)
{
	global $bdd;

  $q = $bdd->prepare('SELECT id_joueur_reel as idJoueur, (SUM(note)/count(*)) as moyenne
    FROM joueur_compo_equipe
    WHERE id_compo IN (SELECT id FROM compo_equipe WHERE id_equipe = :id)
    AND numero_definitif IS NOT NULL
    GROUP BY id_joueur_reel');
  $q->execute([':id' => $idEquipe]);

  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $q2 = $bdd->prepare('UPDATE joueur_equipe SET moy_note = :moy
      WHERE id_equipe = :idEquipe AND id_joueur_reel = :idJoueur');
    $q2->bindValue(':moy', $donnees['moyenne']);
    $q2->bindValue(':idJoueur', $donnees['idJoueur']);
    $q2->bindValue(':idEquipe', $idEquipe);

    $q2->execute();
  }
  $q->closeCursor();
}

function maj_moyennes_joueurs($numJournee)
{
	$ligues = getLiguesATraiter($numJournee);
  if ($ligues != null) {
    addLogEvent(sizeof($ligues) . ' ligue(s) à traiter pour maj des moyennes des joueurs.');

    foreach($ligues as $cle => $idLigue)
    {
      $equipes = getEquipesParLigue($idLigue);

      addLogEvent(sizeof($equipes) . ' équipes pour la ligue ' . $idLigue . '.');

      foreach($equipes as $cle2 => $idEquipe)
      {
        majMoyenneEquipe($idEquipe);
      }
    }
		addLogEvent('Fin maj des moyennes des joueurs OK.');
  } else {
    addLogEvent('Aucune ligue à traiter pour la maj des moyennes des joueurs pour cette journée !');
  }
}

//Remet à NULL les stats de la table JOUEUR COMPO EQUIPE
//Le parametre Ligue_unique, permet de faire tourner le script uniquement sur cette ligue, si sa valeur est null alors on cherche sur toutes les ligues
function raz_table_joueur_compo_equipe_sur_journee($constante_num_journee_cal_reel, $ligue_unique)
{
	global $bdd;
	if(is_null($ligue_unique))
	{
		addLogEvent('FONCTION raz_table_joueur_compo_equipe_sur_journee - Toutes les ligues');
		$upd_remise_a_zero_jce = $bdd->prepare('UPDATE joueur_compo_equipe, calendrier_ligue, compo_equipe SET joueur_compo_equipe.note = NULL, joueur_compo_equipe.note_bonus = NULL, joueur_compo_equipe.nb_but_reel = NULL, joueur_compo_equipe.nb_but_virtuel = NULL, joueur_compo_equipe.numero_definitif = NULL WHERE calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel AND calendrier_ligue.id = compo_equipe.id_cal_ligue AND compo_equipe.id = joueur_compo_equipe.id_compo;');
		$upd_remise_a_zero_jce->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	}else{
		addLogEvent('FONCTION raz_table_joueur_compo_equipe_sur_journee - Uniquement la ligue n°'.$ligue_unique);
		$upd_remise_a_zero_jce = $bdd->prepare('UPDATE joueur_compo_equipe, calendrier_ligue, compo_equipe SET joueur_compo_equipe.note = NULL, joueur_compo_equipe.note_bonus = NULL, joueur_compo_equipe.nb_but_reel = NULL, joueur_compo_equipe.nb_but_virtuel = NULL, joueur_compo_equipe.numero_definitif = NULL WHERE calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel AND calendrier_ligue.id = compo_equipe.id_cal_ligue AND compo_equipe.id = joueur_compo_equipe.id_compo AND calendrier_ligue.id_ligue = :id_ligue;');
		$upd_remise_a_zero_jce->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $ligue_unique));
	}

	$upd_remise_a_zero_jce->closeCursor();
}

//Renvoie un tableau contenant les id_ligues touchés par une journée
function get_ligues_concernees_journee($constante_num_journee_cal_reel, $req_ligues_concernees)
{
	addLogEvent('FONCTION get_ligues_concernees_journee');
	global $bdd;

	$req_ligues_concernees->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$tab_ligues_concernees = $req_ligues_concernees->fetchAll();
	$req_ligues_concernees->closeCursor();

	return $tab_ligues_concernees;
}

//Renvoie la compo définitive d'un id_compo
function get_compo_definitive($id_compo)
{
	addLogEvent('FONCTION get_compo_definitive');
	global $bdd;

	$req_compoDefinitive = $bdd->prepare('SELECT t1.id_joueur_reel, t2.cle_roto_primaire, t2.position, t1.numero_definitif , t1.note, t1.nb_but_reel, t4.nb_def, t4.nb_mil, t4.nb_att FROM joueur_compo_equipe t1, joueur_reel t2, compo_equipe t3, nomenclature_tactique t4 WHERE t1.id_compo = :id_compo AND t1.id_joueur_reel = t2.id AND t1.numero_definitif > 0 AND t1.numero_definitif < 12 AND t1.numero_definitif IS NOT NULL AND t3.id = t1.id_compo AND t3.code_tactique = t4.code ORDER BY t1.numero_definitif ASC;');
	$req_compoDefinitive->execute(array('id_compo' => $id_compo));

	$tab_compo_definitive = $req_compoDefinitive->fetchAll();
	$req_compoDefinitive->closeCursor();

	return $tab_compo_definitive;
}

function calculButVirtuel($equipeA,$equipeB){
	addLogEvent('FONCTION calculButVirtuel');

	$moyGardienA;
	$moyGardienB;

	$moyDefenseA = 0;
	$moyDefenseB = 0;
	$tontonPatDefenseA = 0;
	$tontonPatDefenseB = 0;
	$nbDefA = 0;
	$nbDefB = 0;

	$moyMilieuA = 0;
	$moyMilieuB = 0;
	$tontonPatMilieuA = 0;
	$tontonPatMilieuB = 0;
	$nbMilA = 0;
	$nbMilB = 0;

	$moyAttaqueA = 0;
	$moyAttaqueB = 0;
	$tontonPatAttaqueA = 0;
	$tontonPatAttaqueB = 0;
	$nbAttA = 0;
	$nbAttB = 0;


	$tab_compo_definitive = get_compo_definitive($equipeA);
	//Boucle CALCUL MOYENNE ET TONTON PAT sur la compo domicile
	foreach($tab_compo_definitive as $compoDefinitive)
	{
		if($compoDefinitive['numero_definitif'] == 1){
			if(is_null($compoDefinitive['note'])){
				$moyGardienA = 1 ;
			}else{
				$moyGardienA =  $compoDefinitive['note'];
			}
		}else{
			if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
				if(is_null($compoDefinitive['note'])){
					$tontonPatDefenseA++;
				}else{
					$moyDefenseA += $compoDefinitive['note'];
					$nbDefA++;
				}
			}else{
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['note'])){
						$tontonPatMilieuA++;
					}else{
						$moyMilieuA += $compoDefinitive['note'];
						$nbMilA++;
					}
				}else{
					if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['note'])){
							$tontonPatAttaqueA++;
						}else{
							$moyAttaqueA += $compoDefinitive['note'];
							$nbAttA++;
						}
					}
				}
			}
		}
	}

	//Vérification des plafonds suite aux malus tontonpat sur les moyennes
	if($nbDefA > 0 && $tontonPatDefenseA <= $moyDefenseA/$nbDefA){
		$moyDefenseA = ($moyDefenseA/$nbDefA) - $tontonPatDefenseA;
	}else{
		$moyDefenseA = 0;
	}

	if($nbMilA > 0 && $tontonPatMilieuA <= $moyMilieuA/$nbMilA){
		$moyMilieuA = ($moyMilieuA/$nbMilA) - $tontonPatMilieuA;
	}else{
		$moyMilieuA = 0;
	}

	if($nbAttA > 0 && $tontonPatAttaqueA <= $moyAttaqueA/$nbAttA){
		$moyAttaqueA = ($moyAttaqueA/$nbAttA) - $tontonPatAttaqueA;
	}else{
		$moyAttaqueA = 0;
	}
	addLogEvent( 'MOY Compo Dom ['.$equipeA.'] MoyDefense = '.$moyDefenseA.' MoyMilieu = '.$moyMilieuA.' MoyAttaque = '.$moyAttaqueA);
	addLogEvent( 'TONTON Compo Dom ['.$equipeA.'] TontonPatDef = '.$tontonPatDefenseA.' TontonPatMil = '.$tontonPatMilieuA.' TontonPatAtt = '.$tontonPatAttaqueA);

	$tab_compo_definitive = get_compo_definitive($equipeB);
	//Boucle CALCUL MOYENNE ET TONTON PAT sur la compo visiteur
	foreach($tab_compo_definitive as $compoDefinitive)
	{
		if($compoDefinitive['numero_definitif'] == 1){
			if(is_null($compoDefinitive['note'])){
				$moyGardienB = 1 ;
			}else{
				$moyGardienB =  $compoDefinitive['note'];
			}
		}else{
			if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
				if(is_null($compoDefinitive['note'])){
					$tontonPatDefenseB++;
				}else{
					$moyDefenseB += $compoDefinitive['note'];
					$nbDefB++;
					if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyAttaqueA) && ($compoDefinitive['note']-1 > $moyMilieuA) && ($compoDefinitive['note']-1.5 > $moyDefenseA) && ($compoDefinitive['note']-2 > $moyGardienA)){
						//butVirtuel
						addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
						update_but_virtuel(1,$equipeB,$compoDefinitive['id_joueur_reel']);
					}else{
						update_but_virtuel(NULL,$equipeB,$compoDefinitive['id_joueur_reel']);
					}

				}
			}else{
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['note'])){
						$tontonPatMilieuB++;
					}else{
						$moyMilieuB += $compoDefinitive['note'];
						$nbMilB++;
						if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyMilieuA) && ($compoDefinitive['note']-1 > $moyDefenseA) && ($compoDefinitive['note']-1.5 > $moyGardienA)){
							//butVirtuel
							addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
							update_but_virtuel(1,$equipeB,$compoDefinitive['id_joueur_reel']);
						}else{
							update_but_virtuel(NULL,$equipeB,$compoDefinitive['id_joueur_reel']);
						}
					}
				}else{
					if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['note'])){
							$tontonPatAttaqueB++;
						}else{
							$moyAttaqueB += $compoDefinitive['note'];
							$nbAttB++;
							if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyDefenseA) && ($compoDefinitive['note']-1 > $moyGardienA)){
								//butVirtuel
								addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
								update_but_virtuel(1,$equipeB,$compoDefinitive['id_joueur_reel']);
							}else{
								update_but_virtuel(NULL,$equipeB,$compoDefinitive['id_joueur_reel']);
							}
						}
					}
				}
			}
		}
	}

	//Vérification des plafonds suite aux malus tontonpat sur les moyennes
	if($nbDefB > 0 && $tontonPatDefenseB <= $moyDefenseB/$nbDefB){
		$moyDefenseB = ($moyDefenseB/$nbDefB) - $tontonPatDefenseB;
	}else{
		$moyDefenseB = 0;
	}

	if($nbMilB > 0 && $tontonPatMilieuB <= $moyMilieuB/$nbMilB){
		$moyMilieuB = ($moyMilieuB/$nbMilB) - $tontonPatMilieuB;
	}else{
		$moyMilieuB = 0;
	}

	if($nbAttB > 0 && $tontonPatAttaqueB <= $moyAttaqueB/$nbAttB){
		$moyAttaqueB = ($moyAttaqueB/$nbAttB) - $tontonPatAttaqueB;
	}else{
		$moyAttaqueB = 0;
	}
	addLogEvent( 'MOY Compo Ext ['.$equipeB.'] MoyDefense = '.$moyDefenseB.' MoyMilieu = '.$moyMilieuB.' MoyAttaque = '.$moyAttaqueB);
	addLogEvent( 'TONTON Compo Ext ['.$equipeB.'] TontonPatDef = '.$tontonPatDefenseB.' TontonPatMil = '.$tontonPatMilieuB.' TontonPatAtt = '.$tontonPatAttaqueB);


	$tab_compo_definitive = get_compo_definitive($equipeA);
	//Boucle CALCUL MOYENNE ET TONTON PAT sur la compo domicile
	foreach($tab_compo_definitive as $compoDefinitive)
	{
		if($compoDefinitive['numero_definitif'] == 1){

		}else{
			if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
				if(is_null($compoDefinitive['note'])){

				}else{

					if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyAttaqueB) && ($compoDefinitive['note']-1 > $moyMilieuB) && ($compoDefinitive['note']-1.5 > $moyDefenseB) && ($compoDefinitive['note']-2 > $moyGardienB)){
						//butVirtuel
						addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
						update_but_virtuel(1,$equipeA,$compoDefinitive['id_joueur_reel']);
					}else{
						update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
					}
				}
			}else{
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['note'])){

					}else{
						if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyMilieuB) && ($compoDefinitive['note']-1 > $moyDefenseB) && ($compoDefinitive['note']-1.5 > $moyGardienB)){
							//butVirtuel
							addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
							update_but_virtuel(1,$equipeA,$compoDefinitive['id_joueur_reel']);
						}else{
							update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
						}
					}
				}else{
					if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['note'])){

						}else{

							if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyDefenseB) && ($compoDefinitive['note']-1 > $moyGardienB)){
								//butVirtuel
								addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
								update_but_virtuel(1,$equipeA,$compoDefinitive['id_joueur_reel']);
							}else{
								update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
							}
						}
					}
				}
			}
		}
	}
}

//On met les notes des joueurs non titulaires définitifement à NULL
//On met les numéros définitifs à NULL si numéro_définitif = 0 ou si note = NULL
function nettoyage_joueur_compo_equipe()
{
	addLogEvent('FONCTION nettoyage_joueur_compo_equipe');
	global $bdd;

	$upd_pas_de_note_remplacant = $bdd->prepare('UPDATE joueur_compo_equipe SET note = NULL WHERE numero > 11 AND numero_definitif IS NULL;');
	$upd_pas_de_note_remplacant->execute();
	$upd_pas_de_note_remplacant->closeCursor();

	$upd_numero_definitif_zero = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = NULL WHERE (numero_definitif = 0 OR note IS NULL);');
	$upd_numero_definitif_zero->execute();
	$upd_numero_definitif_zero->closeCursor();
}

//MAJ table JCE avec le nb de but en CSC
function impactCSC($journee, $short_journee)
{
	addLogEvent('FONCTION impactCSC');
	global $bdd;

	//Attention id_journee vs num_journee_cal_reel
	$upd_csc = $bdd->prepare('UPDATE compo_equipe ce, joueur_compo_equipe jce, joueur_stats js, joueur_reel jr, calendrier_ligue cl SET jce.nb_csc = js.csc WHERE jce.id_compo = ce.id  AND jr.id = jce.id_joueur_reel AND js.id IN (jr.cle_roto_primaire, jr.cle_roto_secondaire) AND ce.id_cal_ligue = cl.id AND js.journee = :journee AND cl.num_journee_cal_reel = :short_journee AND js.csc > 0 AND jce.numero_definitif > 0 AND jce.numero_definitif < 12;');

	$upd_csc->execute(array('journee' => $journee, 'short_journee' => $short_journee));
	$upd_csc->closeCursor();
}

//Met à jour les stats et le classement des ligues ayant lieu sur la journée
//A FAIRE : Le parametre Ligue_unique, permet de faire tourner le script uniquement sur cette ligue, si sa valeur est null alors on cherche sur toutes les ligues
function mise_a_jour_stat_classement($constante_num_journee_cal_reel, $constanteJourneeReelle, $req_ligues_concernees)
{
	addLogEvent('FONCTION mise_a_jour_stat_classement');
	global $bdd;

	//SCORE DOM => TABLE calendrier_ligue

	$req_score_dom = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_but_reel,0)) + SUM(IFNULL(t3.nb_but_virtuel,0)) AS \'score_domicile\', SUM(IFNULL(t3.nb_csc,0)) AS \'csc_concedes\' FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_dom  GROUP BY t3.id_compo;');

	$req_csc_adversaire_dom = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_csc,0)) AS \'csc_concedes\' FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.id = :id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_ext  GROUP BY t3.id_compo;');

	$req_score_dom->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));

	$upd_maj_score_dom = $bdd->prepare('UPDATE calendrier_ligue SET score_dom = :score_dom WHERE calendrier_ligue.id = :id ;');

	$scores_dom_a_saisir = $req_score_dom->fetchAll();

	if (count($scores_dom_a_saisir) == 0) {
		addLogEvent(  'Aucun score à saisir sur la journee '.$constante_num_journee_cal_reel);

	} else {
		foreach ($scores_dom_a_saisir as $score_dom_a_saisir) {
			$req_csc_adversaire_dom->execute(array('id' => $score_dom_a_saisir['id']));
			$csc_adversaires_a_saisir = $req_csc_adversaire_dom->fetchAll();
			if (count($csc_adversaires_a_saisir) == 0) {
				addLogEvent(  'ERREUR Aucun adversaire pour CSC DOM sur la journee '.$constante_num_journee_cal_reel);

			} else {
				foreach ($csc_adversaires_a_saisir as $csc_adversaire_a_saisir){
					addLogEvent( 'ID cal, DOM : '.$score_dom_a_saisir['id'].' a marqué '.$score_dom_a_saisir['score_domicile'].' buts et obtient '.$csc_adversaire_a_saisir['csc_concedes'].' csc');

					$upd_maj_score_dom->execute(array('score_dom' => $score_dom_a_saisir['score_domicile']+$csc_adversaire_a_saisir['csc_concedes'],'id' => $score_dom_a_saisir['id']));
					$upd_maj_score_dom->closeCursor();
				}
			}
			$req_csc_adversaire_dom->closeCursor();
		}
	}
	$req_score_dom->closeCursor();


	//SCORE EXT => TABLE calendrier_ligue

	$req_score_ext = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_but_reel,0)) + SUM(IFNULL(t3.nb_but_virtuel,0)) AS \'score_exterieur\' FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_ext  GROUP BY t3.id_compo;');

	$req_csc_adversaire_ext = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_csc,0)) AS \'csc_concedes\' FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.id = :id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_dom  GROUP BY t3.id_compo;');


	$req_score_ext->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));

	$upd_maj_score_ext = $bdd->prepare('UPDATE calendrier_ligue SET score_ext = :score_ext WHERE calendrier_ligue.id = :id ;');

	$scores_ext_a_saisir = $req_score_ext->fetchAll();

	if (count($scores_ext_a_saisir) == 0) {
		addLogEvent(  'Aucun score à saisir sur la journee '.$constante_num_journee_cal_reel);

	} else {
		foreach ($scores_ext_a_saisir as $score_ext_a_saisir) {
			$req_csc_adversaire_ext->execute(array('id' => $score_ext_a_saisir['id']));
			$csc_adversaires_a_saisir = $req_csc_adversaire_ext->fetchAll();
			if (count($csc_adversaires_a_saisir) == 0) {
				addLogEvent(  'ERREUR Aucun adversaire pour CSC EXT sur la journee '.$constante_num_journee_cal_reel);

			} else {
				foreach ($csc_adversaires_a_saisir as $csc_adversaire_a_saisir){
					addLogEvent( 'ID cal, EXT : '.$score_ext_a_saisir['id'].' a marqué '.$score_ext_a_saisir['score_exterieur'].' buts et obtient '.$csc_adversaire_a_saisir['csc_concedes'].' csc');

					$upd_maj_score_ext->execute(array('score_ext' => $score_ext_a_saisir['score_exterieur']+$csc_adversaire_a_saisir['csc_concedes'],'id' => $score_ext_a_saisir['id']));
					$upd_maj_score_ext->closeCursor();
				}
			}
			$req_csc_adversaire_ext->closeCursor();
		}
	}
	$req_score_ext->closeCursor();

	//NB_MATCH, NB BUT REEL, NB BUT VIRTUEL => TABLE joueur_equipe

	$req_nb_match = $bdd->prepare('SELECT count(*) AS \'nb_match\', SUM(IFNULL(t3.nb_but_reel,0)) AS \'nb_but_reel\', SUM(IFNULL(t3.nb_but_virtuel,0)) AS \'nb_but_virtuel\', SUM(IFNULL(t3.nb_csc,0)) AS \'nb_csc\', t1.id_ligue, t2.id_equipe, t3.id_joueur_reel FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t3.id_compo = t2.id AND t2.id_cal_ligue = t1.id AND t3.numero_definitif IS NOT NULL AND t1.score_dom IS NOT NULL AND t1.score_ext IS NOT NULL GROUP BY t1.id_ligue, t2.id_equipe, t3.id_joueur_reel ORDER BY t3.id_joueur_reel;');

	$req_nb_match->execute();

	$upd_maj_nb_match = $bdd->prepare('UPDATE joueur_equipe SET nb_match = :nb_match, nb_but_reel = :nb_but_reel, nb_but_virtuel = :nb_but_virtuel, nb_csc = :nb_csc WHERE id_ligue = :id_ligue AND id_equipe = :id_equipe AND id_joueur_reel = :id_joueur_reel;');

	$nb_matchs_a_saisir = $req_nb_match->fetchAll();

	if (count($nb_matchs_a_saisir) == 0) {
		addLogEvent('Aucun nb de match à saisir sur la journee '.$constante_num_journee_cal_reel);
	} else {
		foreach ($nb_matchs_a_saisir as $nb_match_a_saisir) {
			$upd_maj_nb_match->execute(array('nb_match' => $nb_match_a_saisir['nb_match'],'nb_but_reel' => $nb_match_a_saisir['nb_but_reel'],'nb_but_virtuel' => $nb_match_a_saisir['nb_but_virtuel'], 'nb_csc' => $nb_match_a_saisir['nb_csc'],'id_ligue' => $nb_match_a_saisir['id_ligue'],'id_equipe' => $nb_match_a_saisir['id_equipe'],'id_joueur_reel' => $nb_match_a_saisir['id_joueur_reel']));
			$upd_maj_nb_match->closeCursor();
		}
	}
	$req_nb_match->closeCursor();


	//NB VICTOIRE => TABLE EQUIPE

	$upd_nb_victoire = $bdd->prepare('UPDATE equipe SET nb_victoire = nb_victoire+1 WHERE id IN(
	SELECT id_equipe_dom FROM calendrier_ligue WHERE score_dom > score_ext AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
	UNION
	SELECT id_equipe_ext FROM calendrier_ligue WHERE score_ext > score_dom  AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel)
	AND id_ligue IN(
	SELECT id_ligue FROM calendrier_ligue WHERE score_dom > score_ext AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
	UNION
	SELECT id_ligue FROM calendrier_ligue WHERE score_ext > score_dom  AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel);');

	$upd_nb_victoire->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_victoire->closeCursor();


	//NB DEFAITE => TABLE EQUIPE

	$upd_nb_defaite = $bdd->prepare('UPDATE equipe SET nb_defaite = nb_defaite+1 WHERE id IN(
	SELECT id_equipe_dom FROM calendrier_ligue WHERE score_dom < score_ext AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
	UNION
	SELECT id_equipe_ext FROM calendrier_ligue WHERE score_ext < score_dom  AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel)
	AND id_ligue IN(
	SELECT id_ligue FROM calendrier_ligue WHERE score_dom < score_ext AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
	UNION
	SELECT id_ligue FROM calendrier_ligue WHERE score_ext < score_dom  AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel);');

	$upd_nb_defaite->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_defaite->closeCursor();


	//NB NUL => TABLE EQUIPE

	$upd_nb_nul = $bdd->prepare('UPDATE equipe SET nb_nul = nb_nul+1 WHERE id IN(
	SELECT id_equipe_dom FROM calendrier_ligue WHERE score_dom = score_ext AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
	UNION
	SELECT id_equipe_ext FROM calendrier_ligue WHERE score_ext = score_dom  AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel)
	AND id_ligue IN(
	SELECT id_ligue FROM calendrier_ligue WHERE score_dom = score_ext AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
	UNION
	SELECT id_ligue FROM calendrier_ligue WHERE score_ext = score_dom  AND calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel);');

	$upd_nb_nul->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_nul->closeCursor();

	//NB BUT POUR DOM => TABLE EQUIPE

	$upd_nb_but_pour_dom = $bdd->prepare('UPDATE equipe e, calendrier_ligue cl SET e.nb_but_pour = IFNULL(e.nb_but_pour,0) + IFNULL(cl.score_dom,0) WHERE cl.num_journee_cal_reel = :num_journee_cal_reel AND cl.id_ligue = e.id_ligue AND cl.id_equipe_dom = e.id;');

	$upd_nb_but_pour_dom->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_but_pour_dom->closeCursor();


	//NB BUT POUR EXT => TABLE EQUIPE

	$upd_nb_but_pour_ext = $bdd->prepare('UPDATE equipe e, calendrier_ligue cl SET e.nb_but_pour = IFNULL(e.nb_but_pour,0) + IFNULL(cl.score_ext,0) WHERE cl.num_journee_cal_reel = :num_journee_cal_reel AND cl.id_ligue = e.id_ligue AND cl.id_equipe_ext = e.id;');

	$upd_nb_but_pour_ext->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_but_pour_ext->closeCursor();

	//NB BUT CONTRE DOM => TABLE EQUIPE

	$upd_nb_but_contre_dom = $bdd->prepare('UPDATE equipe e, calendrier_ligue cl SET e.nb_but_contre = IFNULL(e.nb_but_contre,0) + IFNULL(cl.score_ext,0) WHERE cl.num_journee_cal_reel = :num_journee_cal_reel AND cl.id_ligue = e.id_ligue AND cl.id_equipe_dom = e.id;');

	$upd_nb_but_contre_dom->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_but_contre_dom->closeCursor();

	//NB BUT CONTRE EXT => TABLE EQUIPE

	$upd_nb_but_contre_ext = $bdd->prepare('UPDATE equipe e, calendrier_ligue cl SET e.nb_but_contre = IFNULL(e.nb_but_contre,0) + IFNULL(cl.score_dom,0) WHERE cl.num_journee_cal_reel = :num_journee_cal_reel AND cl.id_ligue = e.id_ligue AND cl.id_equipe_ext = e.id;');

	$upd_nb_but_contre_ext->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_but_contre_ext->closeCursor();

	//NB JOUE => TABLE EQUIPE

	$upd_nb_joue = $bdd->prepare('UPDATE equipe e, calendrier_ligue cl SET e.nb_match = e.nb_match + 1 WHERE cl.num_journee_cal_reel = :num_journee_cal_reel AND e.id IN(cl.id_equipe_dom, cl.id_equipe_ext) AND e.id_ligue = cl.id_ligue;');

	$upd_nb_joue->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_joue->closeCursor();

	//NB BONUS => TABLE EQUIPE
	$req_nb_bonus = $bdd->prepare('UPDATE equipe e, calendrier_ligue cl, compo_equipe ce SET e.nb_bonus = e.nb_bonus+1 WHERE ce.code_bonus_malus iS NOT NULL AND cl.num_journee_cal_reel = :num_journee_cal_reel AND e.id_ligue = cl.id_ligue AND e.id = ce.id_equipe AND cl.id = ce.id_cal_ligue;');
	$req_nb_bonus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$req_nb_bonus->closeCursor();

	//NB MALUS => TABLE EQUIPE à faire
	$upd_nb_malus = $bdd->prepare('UPDATE equipe e, (SELECT cl.id_equipe_ext AS \'equipe_victime\', cl.id_ligue from compo_equipe ce, calendrier_ligue cl WHERE ce.code_bonus_malus IS NOT NULL AND ce.id_cal_ligue = cl.id AND cl.num_journee_cal_reel = :num_journee_cal_reel AND ce.id_equipe = cl.id_equipe_dom UNION SELECT cl1.id_equipe_dom AS \'equipe_victime\', cl1.id_ligue from compo_equipe ce1, calendrier_ligue cl1 WHERE ce1.code_bonus_malus IS NOT NULL AND ce1.id_cal_ligue = cl1.id AND cl1.num_journee_cal_reel = :num_journee_cal_reel AND ce1.id_equipe = cl1.id_equipe_ext) t1 SET e.nb_malus = e.nb_malus+1 WHERE e.id_ligue = t1.id_ligue AND e.id = t1.equipe_victime;');
	$upd_nb_malus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_malus->closeCursor();

	//CLASSEMENT => TABLE EQUIPE
	$req_ligues_concernees->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$req_classement_ligue = $bdd->prepare('SELECT tmp.id
		FROM (
			SELECT e.id_ligue, e.id, ((e.nb_victoire*3)+e.nb_nul) as \'points\',
			CAST(e.nb_but_pour AS SIGNED)-CAST(e.nb_but_contre AS SIGNED) as \'diff_but\',
			CAST(e.nb_bonus AS SIGNED)-CAST(e.nb_malus AS SIGNED) as \'diff_bonus\'
			FROM equipe e
			WHERE e.id_ligue = :id_ligue) tmp
		GROUP BY tmp.id_ligue, tmp.id
		ORDER BY tmp.points DESC, tmp.diff_but DESC, tmp.diff_bonus DESC;');

	$upd_classement_ligue = $bdd->prepare('UPDATE equipe SET classement = :classement WHERE id_ligue = :id_ligue and id = :id;');

	while ($listeLiguesConcernees = $req_ligues_concernees->fetch())
	{
		$req_classement_ligue->execute(array('id_ligue' => $listeLiguesConcernees['id_ligue']));
		$rang = 1;
		while ($classementCalcule = $req_classement_ligue->fetch())
		{
			$upd_classement_ligue->execute(array('classement' => $rang, 'id_ligue' => $listeLiguesConcernees['id_ligue'], 'id' => $classementCalcule['id']));
			$upd_classement_ligue->closeCursor();
			$rang++;
		}
		$req_classement_ligue->closeCursor();
	}
	$req_ligues_concernees->closeCursor();

	$upd_statut_journee = $bdd->prepare('UPDATE calendrier_reel SET statut = 2 WHERE num_journee = :num_journee_cal_reel;');
	$upd_statut_journee->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_statut_journee->closeCursor();

	addLogEvent('Fin script');
}

//RESTE A FAIRE
function nettoyageFichierStat()
{
	//Virer les doublons sur le même poste

}





?>
