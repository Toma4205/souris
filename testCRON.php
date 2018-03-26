<?php
include_once('admin/fonctions_admin.php');
include_once('admin/secure_admin.php');

/*
$calendrier_reel = array(
array('num_journee'=>'01','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'02','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'03','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'04','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'05','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'06','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'07','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'08','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'09','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'10','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'11','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'12','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'13','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'14','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'15','debut'=>'2017-11-24 20:45:00','fin'=>'2017-11-24 20:45:00 +2 days 5 hours'),
array('num_journee'=>'16','debut'=>'2017-11-28 19:00:00','fin'=>'2017-11-28 19:00:00 +2 days 5 hours'),
array('num_journee'=>'17','debut'=>'2017-12-01 20:45:00','fin'=>'2017-12-01 20:45:00 +2 days 5 hours'),
array('num_journee'=>'18','debut'=>'2017-12-08 20:45:00','fin'=>'2017-12-08 20:45:00 +2 days 5 hours'),
array('num_journee'=>'19','debut'=>'2017-12-15 20:45:00','fin'=>'2017-12-15 20:45:00 +2 days 5 hours'),
array('num_journee'=>'20','debut'=>'2017-12-20 20:00:00','fin'=>'2017-12-20 20:00:00 +2 days 5 hours'),
array('num_journee'=>'21','debut'=>'2018-01-13 20:00:00','fin'=>'2018-01-13 20:00:00 +2 days 5 hours'),
array('num_journee'=>'22','debut'=>'2018-01-17 20:00:00','fin'=>'2018-01-17 20:00:00 +2 days 5 hours'),
array('num_journee'=>'23','debut'=>'2018-01-20 20:00:00','fin'=>'2018-01-20 20:00:00 +2 days 5 hours'),
array('num_journee'=>'24','debut'=>'2018-01-27 20:00:00','fin'=>'2018-01-27 20:00:00 +2 days 5 hours'),
array('num_journee'=>'25','debut'=>'2018-02-03 20:00:00','fin'=>'2018-02-03 20:00:00 +2 days 5 hours'),
array('num_journee'=>'26','debut'=>'2018-02-10 20:00:00','fin'=>'2018-02-10 20:00:00 +2 days 5 hours'),
array('num_journee'=>'27','debut'=>'2018-02-17 20:00:00','fin'=>'2018-02-17 20:00:00 +2 days 5 hours'),
array('num_journee'=>'28','debut'=>'2018-02-24 20:00:00','fin'=>'2018-02-24 20:00:00 +2 days 5 hours'),
array('num_journee'=>'29','debut'=>'2018-03-09 20:45:00','fin'=>'2018-03-11 23:20:00'),
array('num_journee'=>'30','debut'=>'2018-03-16 20:45:00','fin'=>'2018-03-18 23:20:00'),
array('num_journee'=>'31','debut'=>'2018-03-31 17:00:00','fin'=>'2018-04-04 21:10:00'),//journee spéciale à vérifier
array('num_journee'=>'32','debut'=>'2018-04-07 20:00:00','fin'=>'2018-04-07 20:00:00 +2 days 5 hours'),
array('num_journee'=>'33','debut'=>'2018-04-14 20:00:00','fin'=>'2018-04-14 20:00:00 +2 days 5 hours'),
array('num_journee'=>'34','debut'=>'2018-04-21 20:00:00','fin'=>'2018-04-21 20:00:00 +2 days 5 hours'),
array('num_journee'=>'35','debut'=>'2018-04-28 20:00:00','fin'=>'2018-04-28 20:00:00 +2 days 5 hours'),
array('num_journee'=>'36','debut'=>'2018-05-06 20:00:00','fin'=>'2018-05-06 20:00:00 +2 days 5 hours'),
array('num_journee'=>'37','debut'=>'2018-05-12 20:00:00','fin'=>'2018-05-12 20:00:00 +2 days 5 hours'),
array('num_journee'=>'38','debut'=>'2018-05-19 20:00:00','fin'=>'2018-05-19 20:00:00 +2 days 5 hours'));
*/

date_default_timezone_set('Europe/Paris');
$now = getdate();
$time_fin_dernier_match = null ;

addLogEvent('LANCEMENT DU SCRIPT CRON');

//Tous les midis : Mise à jour des états des joueurs réels
if($now['hours'] == 12)
{
	get_dispo_joueur_roto();
}

$tabJournee = getJourneeRecente();
if ($tabJournee !== null)
{
	$journee = $tabJournee['0'];
	$numJournee = $journee['num_journee'];
	$numJourneeLong = get_journee_format_long($numJournee);
	$statut = $journee['statut'];

	addLogEvent('Début traitement journée ' . $numJournee . ' (statut=' . $statut . ')');
	if ($statut == 0) {
		if (strtotime("now")-strtotime($journee['date_heure_debut']) >= 0) {
			addLogEvent('Date de début dépassée (' . $journee['date_heure_debut'] . ') => initialisation de la journée.');
			dump_pre_journee($numJournee);

			//L'INIT n'a pas été fait alors faire le SCRIPT ZERO
			initializeJournee($numJournee);
			$statut == 1;

		} else {
			addLogEvent('Date de début non atteinte (' . $journee['date_heure_debut'] . ') => RAS.');
		}
	}
	if ($statut == 1) {
			//EN SUSPENS LE LIVE
		/*4 - maj_table_live_buteur
			4.1 - nettoyageTableButeurLive
			4.2 - setButeurLive
			4.3 - associer_buteur_live_joueur_reel
			4.4 - afficher_log_buteur_sans_matching*/

			//SCRAP MAXI DES SCORES DES MATCHS TERMINES
			$time_tmp = scrapMaxi($numJournee);
			if(!is_null($time_tmp)){
				$time_fin_dernier_match = $time_tmp;
			}

			//Test la nécessité de télécharger un fichier roto
			if(is_Fichier_Roto_A_Telecharger($numJournee))
			{
				set_statut_match_termine_journee($numJournee,1,4);
				get_csv_from_roto($numJourneeLong);
				calculer_notes_joueurs();
				maj_scores_journee_en_cours($numJournee);
			}

			//Test pour savoir si tous les matchs de la journée sont terminés depuis plus de 10 minutes (statut = 1)
			if(get_nb_match_termine_par_journee($numJournee) < 10)
			{
				addLogEvent('CRON tous les matchs ne sont pas terminés.');
				//Tous les matchs de la journée n'ont pas encore été joué
			}else{
				addLogEvent('CRON tous les matchs OK, Statut journee = 2.');
				calculer_confrontations_journee($numJournee, null, FALSE);
				majScoreTempJourneeTerminee($numJournee);
				setStatutJournee($numJournee,2);
				$statut = 2;
			}

			// TODO MPL - TVE revoir ordre de ces appels
			//SI on atteint la fin de la journée et que tous les status des matchs ne sont pas à 1 alors les matchs restants sont considérés comme "Annulés";
			if(strtotime("now - 10 minutes")-strtotime($journee['date_heure_fin'])>=0 && get_nb_match_termine_par_journee($numJournee) < 10)
			{
				addLogEvent('CRON DES MATCHS SEMBLENT ANNULES');
				annuler_match_restants($numJournee);
				set_statut_match_termine_journee($numJournee,1,0);
				get_csv_from_roto($numJourneeLong);
				calculer_notes_joueurs();
				calculer_confrontations_journee($numJournee, null, FALSE);
				majScoreTempJourneeTerminee($numJournee);
				setStatutJournee($numJournee,2);
				$statut = 2;
			}
	}
	if ($statut == 2) {
		if (strtotime("now -6 hours")-strtotime($journee['date_heure_fin'])>=0) {
			addLogEvent('Nous avons terminé '.$numJournee.' depuis plus de 6h => mise à jour des ligues.');

			get_csv_from_roto($numJourneeLong);
			calculer_notes_joueurs();
			calculer_confrontations_journee($numJournee, null, TRUE);
			maj_ligues_fin_journee($numJournee);
			setStatutJournee($numJournee,3);
			$statut = 3;
		} else {
			addLogEvent('Délai d\'attente pour clôture de la journée non atteint => RAS.');
		}
	}
	if ($statut == 3) {
		addLogEvent('Journée clôturée => RAS.');
	}
}
else {
	addLogEvent('Aucune journée trouvée en BDD.');
}

?>
