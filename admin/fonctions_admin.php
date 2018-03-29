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
function maj_etat_joueur_reel

*/

require_once(__DIR__ . '/../modele/connexionSQL.php');
require_once(__DIR__ . '/../modele/equipe/equipe.php');
require_once(__DIR__ . '/../modele/joueurequipe/joueurEquipe.php');
require_once(__DIR__ . '/../modele/compoequipe/compoEquipe.php');
require_once(__DIR__ . '/../modele/compoequipe/joueurCompoEquipe.php');
require_once(__DIR__ . '/../modele/ligue/etatLigue.php');
require_once(__DIR__ . '/../modele/nomenclature/caricatureEnum.php');
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

include_once('calculConfrontations.php');

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

function getJourneeRecente()
{
	global $bdd;

	$q = $bdd->prepare('SELECT * FROM calendrier_reel
		WHERE date_heure_debut <= NOW() ORDER BY date_heure_debut DESC LIMIT 1');
	$q->execute();
	return $q->fetchAll();
}

//Retourne la journee au format YYYYJJ
function get_journee_format_long($journee_short)
{
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
    	WHERE ce.id_equipe = :id AND ce.id_cal_ligue = (SELECT cl.id FROM calendrier_ligue cl
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

  $q = $bdd->prepare('SELECT je.prix as prixAchat, je.nb_match,
		(je.nb_but_reel + je.nb_but_virtuel) as totalBut, j.position, j.id
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
				addLogEvent('Equipe ' . $idEquipe . ' : Compo précédente ' . $compo->id() . '.');
        $joueurs = getTitulairesByCompo($compo->id());
        $idCompo = creerCompoCommePrecedente($compo, $idEquipe, $idCalLigue, $joueurs);
      } else {
				addLogEvent('Equipe ' . $idEquipe . ' : Première compo.');
        $joueurs = getJoueurEquipeByEquipe($idEquipe);
        $idCompo = creerPremiereCompo($idEquipe, $idCalLigue, $joueurs);
      }

			addLogEvent('Création compo ' . $idCompo . ' pour le match ' . $idCalLigue . ' pour l\'équipe ' . $idEquipe);
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

function is_Fichier_Roto_A_Telecharger($journee)
{
	global $bdd;
	$req_matchs_termines_depuis_longtemps=$bdd->prepare('SELECT count(*) AS \'nb_match\' FROM resultatsl1_reel WHERE UNIX_TIMESTAMP()-statut > 600 AND statut > 3 AND SUBSTRING(journee,5,2) = :journee;');
	$req_matchs_termines_depuis_longtemps->execute(array('journee' => $journee));

	while ($nb_match = $req_matchs_termines_depuis_longtemps->fetch())
	{
		if($nb_match['nb_match'] > 0)
		{
			addLogEvent('FONCTION is_Fichier_Roto_A_Telecharger => oui');
			return true;
		}else{
			addLogEvent('FONCTION is_Fichier_Roto_A_Telecharger => non');
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

//Calcule les notes de tous les joueurs ayant une note à NULL
function calculer_notes_joueurs()
{
	addLogEvent('FONCTION calculer_notes_joueurs');
	global $bdd;

		$req = $bdd->query('SELECT t1.id as idJReel, t1.position, t2.* FROM joueur_reel t1, joueur_stats t2 WHERE t2.id IN (t1.cle_roto_primaire, t1.cle_roto_secondaire) AND t2.note IS NULL');
		$req_regleCalcul = $bdd->prepare('SELECT * FROM nomenclature_reglescalculnote WHERE position = :position');
		$req_scoreToNote = $bdd->prepare('SELECT note FROM nomenclature_scoretonote WHERE ScoreObtenu = :scoreObtenu AND Position = :position');
		$upd_note = $bdd->prepare('UPDATE joueur_stats SET note = :note WHERE id = :id AND journee = :journee');

		$nbJoueur = 0;
		while ($donnees = $req->fetch())
		{
			$nbJoueur++;
			$scoreCalcule = 0;
			if($donnees['a_joue'] == '0'){
				$noteObtenue = 0;
				addLogEvent($donnees['id'].' (id='.$donnees['idJReel'].') n\' a pas joué sur la journee '.$donnees['journee'].' et obtient la note de '.$noteObtenue);
				$upd_note->execute(array('note' => $noteObtenue,'id' => $donnees['id'],'journee' => $donnees['journee']));
			}else{
				$req_regleCalcul->execute(array('position' => $donnees['position']));
				while ($tableauReglesCalcul = $req_regleCalcul->fetch())
				{
					$scoreCalcule += $donnees[$tableauReglesCalcul['StatName']] * $tableauReglesCalcul['Ponderation'];
				}

				$req_scoreToNote->execute(array('scoreObtenu' => round($scoreCalcule,2),'position' => $donnees['position']));
				$noteObtenue=$req_scoreToNote->fetch(PDO::FETCH_ASSOC);
				addLogEvent($donnees['id'].' (id='.$donnees['idJReel'].') sur la journee '.$donnees['journee'].' obtient la note de '.$noteObtenue['note']);
				$upd_note->execute(array('note' => $noteObtenue['note'],'id' => $donnees['id'],'journee' => $donnees['journee']));
			}
		}
		$req->closeCursor();
		$req_regleCalcul->closeCursor();
		$req_scoreToNote->closeCursor();
		$upd_note->closeCursor();

		addLogEvent('Fin calcul des notes des '.$nbJoueur.' joueurs réels.');
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

function majScoreTempJourneeTerminee($numJournee)
{
	addLogEvent('Mise à jour des scores pour la journée Terminée OK.');
	global $bdd;

    $q = $bdd->prepare('UPDATE calendrier_ligue cl
      SET cl.score_dom = (
        SELECT COALESCE(SUM(jce.nb_but_reel),0) + COALESCE(SUM(jce.nb_but_virtuel),0)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_dom
        AND ce.id_cal_ligue = cl.id
      ),
      cl.score_ext = (
        SELECT COALESCE(SUM(jce.nb_but_reel),0) + COALESCE(SUM(jce.nb_but_virtuel),0)
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

//Récupere les infos du joueur ciblé par un bonus/malus
function get_joueur_concerne_bonus($idEquipe, $idCompo, $idCal)
{
	addLogEvent('FONCTION get_joueur_concerne_bonus idEquipe='.$idEquipe.', idCompo='.$idCompo.', idCalLigue='.$idCal);
	global $bdd;
	//Requete Joueur Concerné par le bonus
	$req_joueur_bonus = $bdd->prepare('SELECT jce.*
		FROM joueur_compo_equipe jce
		JOIN bonus_malus b ON b.id_joueur_reel_equipe = jce.id_joueur_reel
		AND b.id_equipe = :idEquipe AND b.id_cal_ligue = :idCal
		WHERE jce.id_compo = :idCompo AND jce.note > 0');
	$req_joueur_bonus->execute(array('idEquipe' => $idEquipe,'idCal' => $idCal,'idCompo' => $idCompo));
	$resultat = $req_joueur_bonus->fetch();
	$req_joueur_bonus->closeCursor();

	if (!$resultat) {
		return NULL;
	} else {
		return new JoueurCompoEquipe($resultat);
	}
}



//On change le nb de but virtuel d'un joueur selon son id, sa compo et sa journee
function update_but_virtuel($but,$id_compo, $id_joueur_reel)
{
	addLogEvent('FONCTION update_but_virtuel => id='.$id_joueur_reel.' (compo='.$id_compo.') => nbBut='.$but);
	global $bdd;

	$upd_butVirtuel = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_virtuel = :nb_but_virtuel WHERE joueur_compo_equipe.id_compo = :id_compo AND joueur_compo_equipe.id_joueur_reel = :id_joueur_reel;');
	$upd_butVirtuel->execute(array('nb_but_virtuel' => $but, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_butVirtuel->closeCursor();
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

  $q = $bdd->prepare('SELECT e.*, c.nom as nom_coach FROM equipe e
  	JOIN coach c ON c.id = e.id_coach
  	WHERE id_ligue = :id');
  $q->execute([':id' => $idLigue]);

  $equipes = [];
      while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $equipes[] = new Equipe($donnees);
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

function getNbMatchJournee($idLigue, $numJournee)
{
	global $bdd;

	$q = $bdd->prepare('SELECT COUNT(*) FROM calendrier_ligue
		WHERE id_ligue = :id AND num_journee_cal_reel = :num');
	$q->execute([':id' => $idLigue, ':num' => $numJournee]);

	return $q->fetchColumn();
}

function creerCaricature($idEquipe, $code, $idJoueurReel, $total)
{
	global $bdd;
	$q = $bdd->prepare('INSERT INTO caricature_equipe(id_equipe, code, id_joueur_reel, total)
		VALUES(:idEquipe, :code, :idJoueurReel, :total)');
	$q->bindValue(':idEquipe', $idEquipe);
	$q->bindValue(':code', $code);
	$q->bindValue(':idJoueurReel', $idJoueurReel);
	$q->bindValue(':total', $total);

	$q->execute();
}

function getJoueursVoyantLigue($idLigue)
{
	global $bdd;

	$q = $bdd->prepare('SELECT id_joueur_reel, id_equipe, tour_mercato
		FROM joueur_equipe WHERE (nb_but_reel + nb_but_virtuel) >= (
			SELECT MIN(temp.total) FROM (
				SELECT (je.nb_but_reel + je.nb_but_virtuel) as total
				FROM joueur_equipe je WHERE je.id_ligue = :id ORDER BY total DESC LIMIT 10
			) temp
		) AND id_ligue = :id AND tour_mercato >= 3');
	$q->execute([':id' => $idLigue]);

	return $q->fetchAll();
}

function definirCaricaturesLigue($idLigue, $equipes)
{
	addLogEvent('Début traitement des caricatures.');

	// TODO TONTON_PAT, PIGNON, BUT_REEL_ENC, BUT_VIRTUEL_ENC

	$tabEquipeTrophee = [];
	$nbMalusMax = 0;
	$nbAttaqueMin = 1000;
	$budgetMax = 0;

	$equipePigeon = 0;
	$prixPigeon = 0;
	$idJoueurPigeon = 0;

	$equipeDepensier = 0;
	$prixDepensier = 0;

	foreach($equipes as $equipe)
    {
		if ($equipe->classement() == 1) {
			addLogEvent('Caricature ' . CaricatureEnum::CHAMPION . ' pour l\'équipe ' . $equipe->nom() . ' (id=' . $equipe->id() . ').');
			creerCaricature($equipe->id(), CaricatureEnum::CHAMPION, NULL, 0);
			$tabEquipeTrophee[$equipe->id()] = 1;
		} else if ($equipe->classement() == sizeof($equipes)) {
			addLogEvent('Caricature ' . CaricatureEnum::SOURIS . ' pour l\'équipe ' . $equipe->nom() . ' (id=' . $equipe->id() . ').');
			creerCaricature($equipe->id(), CaricatureEnum::SOURIS, NULL, 0);
			$tabEquipeTrophee[$equipe->id()] = 1;
		} else {
			$tabEquipeTrophee[$equipe->id()] = 0;
		}

		if ($equipe->nbMalus() > $nbMalusMax) {
			$nbMalusMax = $equipe->nbMalus();
		}
		if ($equipe->nbButPour() < $nbAttaqueMin) {
			$nbAttaqueMin = $equipe->nbButPour();
		}
		if ($equipe->budgetRestant() > $budgetMax) {
			$budgetMax = $equipe->budgetRestant();
		}

		$prixAttSansBut = 0;
		$idAttSansBut = 0;
		$depensier = 0;

		$joueursEquipe = getJoueurEquipeByEquipe($equipe->id());
		foreach($joueursEquipe as $joueur)
		{
			//addLogEvent($equipe->id() . ') joueur:' . $joueur->id() . ' => position=' . $joueur->position() . ', totalBut=' . $joueur->totalBut() . ', nbMatch=' . $joueur->nbMatch() . ', prix=' . $joueur->prixAchat());
			if ($joueur->totalBut() > 0 && $joueur->prixAchat()/$joueur->totalBut() < ConstantesAppli::RATIO_PEPITE) {
				addLogEvent('Caricature ' . CaricatureEnum::PEPITE . ' pour l\'équipe ' . $equipe->nom() . ' (id=' . $equipe->id() . ') et le joueur ' . $joueur->nom() . ' (id=' . $joueur->id() . ').');
				creerCaricature($equipe->id(), CaricatureEnum::PEPITE, $joueur->id(), 0);
				$tabEquipeTrophee[$equipe->id()] = 1;
			}
			if ($joueur->position() == ConstantesAppli::ATTAQUANT && $joueur->totalBut() == 0 && $joueur->prixAchat() > $prixAttSansBut) {
				$prixAttSansBut = $joueur->prixAchat();
				$idAttSansBut = $joueur->id();
			}
			if ($joueur->nbMatch() == 0) {
				$depensier += $joueur->prixAchat();
			}
		}

		if ($prixAttSansBut > $prixPigeon) {
			$equipePigeon = $equipe->id();
			$prixPigeon = $prixAttSansBut;
			$idJoueurPigeon = $idAttSansBut;
		}

		if ($depensier > $prixDepensier) {
			$equipeDepensier = $equipe->id();
			$prixDepensier = $depensier;
		}
	}

	if ($idJoueurPigeon > 0) {
		addLogEvent('Caricature ' . CaricatureEnum::PIGEON . ' pour l\'équipe (id=' . $equipePigeon . ') et le joueur (id=' . $idJoueurPigeon . ') au prix de ' . $prixPigeon . '.');
		creerCaricature($equipePigeon, CaricatureEnum::PIGEON, $idJoueurPigeon, $prixPigeon);
		$tabEquipeTrophee[$equipePigeon] = 1;
	}
	else {
		addLogEvent('Aucun ' . CaricatureEnum::PIGEON . ' !');
	}

	if ($prixDepensier > 0) {
		addLogEvent('Caricature ' . CaricatureEnum::DEPENSIER . ' pour l\'équipe (id=' . $equipeDepensier . ') au prix de ' . $prixDepensier . '.');
		creerCaricature($equipeDepensier, CaricatureEnum::DEPENSIER, NULL, $prixDepensier);
		$tabEquipeTrophee[$equipeDepensier] = 1;
	}
	else {
		addLogEvent('Aucun ' . CaricatureEnum::DEPENSIER . ' !');
	}

	$joueursVoyant = getJoueursVoyantLigue($idLigue);
	foreach($joueursVoyant as $joueur)
    {
		addLogEvent('Caricature ' . CaricatureEnum::VOYANT . ' pour l\'équipe (id=' . $joueur['id_equipe'] . ') et le joueur (id=' . $joueur['id_joueur_reel'] . ') au tour mercato ' . $joueur['tour_mercato'] . '.');
		creerCaricature($joueur['id_equipe'], CaricatureEnum::VOYANT, $joueur['id_joueur_reel'], $joueur['tour_mercato']);
		$tabEquipeTrophee[$joueur['id_equipe']] = 1;
	}

	foreach($equipes as $equipe)
    {
		if ($equipe->nbMalus() == $nbMalusMax) {
			addLogEvent('Caricature ' . CaricatureEnum::VICTIME . ' pour l\'équipe ' . $equipe->nom() . ' (id=' . $equipe->id() . ') avec ' . $nbMalusMax . ' malus.');
			creerCaricature($equipe->id(), CaricatureEnum::VICTIME, NULL, $nbMalusMax);
			$tabEquipeTrophee[$equipe->id()] = 1;
		}
		if ($equipe->nbButPour() == $nbAttaqueMin) {
			addLogEvent('Caricature ' . CaricatureEnum::PIRE_ATTAQUE . ' pour l\'équipe ' . $equipe->nom() . ' (id=' . $equipe->id() . ') avec ' . $nbAttaqueMin . ' buts.');
			creerCaricature($equipe->id(), CaricatureEnum::PIRE_ATTAQUE, NULL, $nbAttaqueMin);
			$tabEquipeTrophee[$equipe->id()] = 1;
		}
		if ($equipe->budgetRestant() == $budgetMax && $budgetMax > 0) {
			addLogEvent('Caricature ' . CaricatureEnum::ECONOME . ' pour l\'équipe ' . $equipe->nom() . ' (id=' . $equipe->id() . ') avec un budget restant de ' . $budgetMax . '.');
			creerCaricature($equipe->id(), CaricatureEnum::ECONOME, NULL, $budgetMax);
			$tabEquipeTrophee[$equipe->id()] = 1;
		}

		if ($tabEquipeTrophee[$equipe->id()] == 0) {
			addLogEvent('Caricature ' . CaricatureEnum::AUC_TROPHEE . ' pour l\'équipe ' . $equipe->nom() . ' (id=' . $equipe->id() . ').');
			creerCaricature($equipe->id(), CaricatureEnum::AUC_TROPHEE, NULL, 0);
		}
	}

	addLogEvent('Fin traitement des caricatures.');
}

function majEtatLigue($idLigue, $etat)
{
	global $bdd;

	$q = $bdd->prepare('UPDATE ligue SET etat = :etat WHERE id = :id');
	$q->execute([':etat' => $etat, ':id' => $idLigue]);
}

function maj_ligues_fin_journee($numJournee)
{
	$ligues = getLiguesATraiter($numJournee);
	if ($ligues != null) {
    addLogEvent(sizeof($ligues) . ' ligue(s) à traiter pour cette fin de journée.');
		$prochainNumJournee = intVal($numJournee) + 1;
    foreach($ligues as $cle => $idLigue)
    {
      $equipes = getEquipesParLigue($idLigue);
      addLogEvent(sizeof($equipes) . ' équipes pour la ligue ' . $idLigue . '.');
			// Maj des moyennes des joueurs
      foreach($equipes as $equipe)
      {
        majMoyenneEquipe($equipe->id());
      }
			// Vérification du statut de la ligue
			if (getNbMatchJournee($idLigue, $prochainNumJournee) == 0)
			{
				definirCaricaturesLigue($idLigue, $equipes);

				majEtatLigue($idLigue, EtatLigue::TERMINEE);
				addLogEvent('La ligue passe au statut 3 (TERMINEE).');
			}
    }
		addLogEvent('Fin traitement des ligues OK.');
  } else {
    addLogEvent('Aucune ligue à traiter pour cette fin de journée !');
  }
}

?>
