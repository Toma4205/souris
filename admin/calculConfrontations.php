<?php
// Calcul les buts et buts virtuels des confrontations ayant lieu sur la journée
// $ligue_unique : permet de faire tourner le script uniquement sur cette ligue, si sa valeur est null alors on cherche sur toutes les ligues
// $maj_stats_classement : permet de définir si la maj du classement et des stats doivent être faites.
function calculer_confrontations_journee($constante_num_journee_cal_reel, $ligue_unique, $maj_stats_classement)
{
	addLogEvent('FONCTION calculer_confrontations_journee');
	global $bdd;

	$constanteJourneeFormatLong = get_journee_format_long($constante_num_journee_cal_reel);
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

		initialiserNoteJoueurMatchAnnule($constanteJourneeFormatLong);

		$cacheNote = creerCacheNoteJoueurReel($constanteJourneeFormatLong);
		addLogEvent('Récupération du cache des notes pour la journée '.$constanteJourneeFormatLong.' pour '.sizeof($cacheNote).' joueurs.');

		$cacheNbDefParTectique = getCacheNbDefParTactique();
		addLogEvent('Récupération du cache nbDef par tactique pour '.sizeof($cacheNbDefParTectique).' tactiques.');

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

			$tabMatch = getMatchParLigueEtJournee($constante_num_journee_cal_reel, $constanteConfrontationLigue);
			//$tab_effectif = get_effectifs_ligue_journee($constante_num_journee_cal_reel, $constanteConfrontationLigue);
			addLogEvent(sizeof($tabMatch) . ' matchs à traiter pour cette ligue.');

			foreach($tabMatch as $donnees)
			{
				$idCompoDom = $donnees['idCompoDom'];
				$tactiqueDom = $donnees['tactiqueDom'];
				$bonusDom = $donnees['codeBonusDom'];
				$idJoueurReelDom = $donnees['idJoueurBonusDom'];
				$idJoueurReelAdvDom = $donnees['idJoueurAdvBonusDom'];
				$idCompoExt = $donnees['idCompoExt'];
				$tactiqueExt = $donnees['tactiqueExt'];
				$bonusExt = $donnees['codeBonusExt'];
				$idJoueurReelExt = $donnees['idJoueurBonusExt'];
				$idJoueurReelAdvExt = $donnees['idJoueurAdvBonusExt'];

				addLogEvent('Début match '.$idCompoDom.' (tactique='.$tactiqueDom.', bonus='.$bonusDom.
					', idJoueur='.$idJoueurReelDom.', idJoueurAdv='.$idJoueurReelAdvDom.') vs '.$idCompoExt.
					' (tactique='.$tactiqueExt.', bonus='.$bonusExt.', idJoueur='.$idJoueurReelExt.
					', idJoueurAdv='.$idJoueurReelAdvExt.')');

				$tabJoueurs = getJoueurParMatch($idCompoDom, $idCompoExt);
				foreach($tabJoueurs as $joueur)
				{
					$idCompo = $joueur['id_compo'];
					$idJoueurReel = $joueur['id_joueur_reel'];

					if (isset($cacheNote[$idJoueurReel]) && $cacheNote[$idJoueurReel] > 0) {
						$note = $cacheNote[$idJoueurReel];
						if ($idCompo == $idCompoDom) {
							initialiserNoteJoueurCompo($idJoueurReel, $idCompo, $note, $joueur['capitaine'],
								$joueur['position'], $joueur['numero'], $joueur['cle_roto_primaire'], $cacheNbDefParTectique[$tactiqueDom],
								$bonusDom, $idJoueurReelDom, $bonusExt, $idJoueurReelAdvExt, $constanteJourneeFormatLong);
						} else {
							initialiserNoteJoueurCompo($idJoueurReel, $idCompo, $note, $joueur['capitaine'],
								$joueur['position'], $joueur['numero'], $joueur['cle_roto_primaire'], $cacheNbDefParTectique[$tactiqueExt],
								$bonusExt, $idJoueurReelExt, $bonusDom, $idJoueurReelAdvDom, $constanteJourneeFormatLong);
						}
					} else {
						addLogEvent($joueur['cle_roto_primaire'].' (id='.$idJoueurReel.', compo='.$idCompo.') n\'a pas joué.');
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
						addLogEvent('Aucun remplaçant, le joueur '.$donnees['cle_roto_primaire'].' (id='.$donnees['id_joueur_reel'].', compo='.$donnees['id_compo'].') reste dans la compo.');
						//On update. Le numéro définitif devient le numéro initialement prévu
						update_numero_definitif($donnees['numero'],$donnees['id_compo'],$donnees['id_joueur_reel']);
					} else {
						foreach ($lignesRemplacant as $ligneRemplacant) {
							if($ligneRemplacant['position'] == $donnees['position'] && $ligneRemplacant['note'] > 0  && $estRemplace == 0){
								//Il existe un remplacement poste pour poste
								$estRemplace = 1;
								addLogEvent('Remplacement de '.$donnees['cle_roto_primaire'].' (id='.$donnees['id_joueur_reel'].', compo='.$donnees['id_compo'].') par le même poste '.$ligneRemplacant['cle_roto_primaire']);
								//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
								update_numero_definitif($donnees['numero'],$donnees['id_compo'],$ligneRemplacant['id_joueur_reel']);
								//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
								updateButReelDuJoueur($donnees['id_compo'], $constanteJourneeFormatLong, $ligneRemplacant['id_joueur_reel']);
								//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
								update_numero_definitif(0,$donnees['id_compo'],$donnees['id_joueur_reel']);
							}
						}
						if($donnees['position'] == 'Defender' && $estRemplace == 0){
								//Si il n'existe pas de remplacement poste pour poste pour un défenseur alors le joueur ne peut pas être remplacé
								addLogEvent('Aucun défenseur remplaçant, le joueur '.$donnees['cle_roto_primaire'].' (id='.$donnees['id_joueur_reel'].', compo='.$donnees['id_compo'].') reste dans la compo');
								//On update. Le numéro définitif devient le numéro initialement prévu
								update_numero_definitif($donnees['numero'],$donnees['id_compo'],$donnees['id_joueur_reel']);
						}
					}
				}else{
					if(is_null($donnees['numero_remplacement'])){
						//Le joueur a une note et ne fait l'objet d'aucun remplacement tactique donc il est directement dans l'effectif définitif
						addLogEvent($donnees['cle_roto_primaire'].' (id='.$donnees['id_joueur_reel'].', compo='.$donnees['id_compo'].') a joué et n\'est pas remplacé.');
						//On update. Le numéro définitif du joueur avec son numéro initial
						update_numero_definitif($donnees['numero'],$donnees['id_compo'],$donnees['id_joueur_reel']);
						//On regarde le nombre de but réel marqué par ce joueur sur cette journée
						updateButReelDuJoueur($donnees['id_compo'], $constanteJourneeFormatLong, $donnees['id_joueur_reel']);
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
					addLogEvent('Aucun remplaçant, le joueur '.$donneesEffectifNonRemplace['cle_roto_primaire'].' reste dans la compo');
					//On update. Le numéro définitif devient le numéro initialement prévu
					update_numero_definitif($donneesEffectifNonRemplace['numero'],$donneesEffectifNonRemplace['id_compo'],$donneesEffectifNonRemplace['id_joueur_reel']);
				}else{
					foreach ($lignesRemplacant as $ligneRemplacant) {
						if((($donneesEffectifNonRemplace['position'] == 'Midfielder' && $ligneRemplacant['position'] == 'Defender') || ($donneesEffectifNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Midfielder')) && $ligneRemplacant['note']>0 && $estRemplace == 0 ){
							//Il existe un remplacement par le poste du dessous
							$estRemplace = 1;
							addLogEvent('Remplacement de '.$donneesEffectifNonRemplace['cle_roto_primaire'].' par le poste inférieur '.$ligneRemplacant['cle_roto_primaire']);
							//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
							update_numero_definitif($donneesEffectifNonRemplace['numero'],$donneesEffectifNonRemplace['id_compo'],$ligneRemplacant['id_joueur_reel']);

							//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
							updateButReelDuJoueur($donneesEffectifNonRemplace['id_compo'], $constanteJourneeFormatLong, $ligneRemplacant['id_joueur_reel']);

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
						addLogEvent('Aucun défenseur pour remplacer le milieu '.$donneesEffectifNonRemplace['cle_roto_primaire'].'. Il reste dans la compo.');
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
					addLogEvent('Aucun remplaçant, le joueur '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' reste dans la compo');
					//On update. Le numéro définitif devient le numéro initialement prévu
					update_numero_definitif($donneesAttaquantNonRemplace['numero'],$donneesAttaquantNonRemplace['id_compo'],$donneesAttaquantNonRemplace['id_joueur_reel']);
				}else{
					foreach ($lignesRemplacant as $ligneRemplacant) {
						if($donneesAttaquantNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Defender'&& $ligneRemplacant['note']>0 && $estRemplace == 0 ){
							//Il existe un remplacement par le poste du dessous
							$estRemplace = 1;
							addLogEvent('Remplacement de l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' par un défenseur '.$ligneRemplacant['cle_roto_primaire']);
							//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
							update_numero_definitif($donneesAttaquantNonRemplace['numero'],$donneesAttaquantNonRemplace['id_compo'],$ligneRemplacant['id_joueur_reel']);
							//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
							updateButReelDuJoueur($donneesAttaquantNonRemplace['id_compo'], $constanteJourneeFormatLong, $ligneRemplacant['id_joueur_reel']);

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
						}
					}
					if($donneesAttaquantNonRemplace['position'] == 'Forward' && $estRemplace == 0){
						//Si il n'existe pas de remplacement d'un défenseur pour un attaquant alors le joueur ne peut pas être remplacé
						addLogEvent('Aucun défenseur pour remplacer l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].'. Il reste dans la compo.');
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
				addLogEvent('Remplacement Tactique de '.$donneesRemplacementTactique['cle_roto_primaire'].' (id='.$donneesRemplacementTactique['id_joueur_reel'].') par le joueur avec l\'id : '.$donneesRemplacementTactique['id_joueur_reel_remplacant']) ;
				//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
				update_numero_definitif($donneesRemplacementTactique['numero'],$donneesRemplacementTactique['id_compo'],$donneesRemplacementTactique['id_joueur_reel_remplacant']);

				//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
				updateButReelDuJoueur($donneesRemplacementTactique['id_compo'], $constanteJourneeFormatLong, $donneesRemplacementTactique['id_joueur_reel_remplacant']);

				//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
				update_numero_definitif(0,$donneesRemplacementTactique['id_compo'],$donneesRemplacementTactique['id_joueur_reel']);
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
				addLogEvent('Le remplacement tactique de '.$donneesJoueursRestants['cle_roto_primaire'].' n\'était pas possible ');
				//On update. Le numéro définitif du joueur
				update_numero_definitif($donneesJoueursRestants['numero'],$donneesJoueursRestants['id_compo'],$donneesJoueursRestants['id_joueur_reel']);

				//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
				updateButReelDuJoueur($donneesJoueursRestants['id_compo'], $constanteJourneeFormatLong, $donneesJoueursRestants['id_joueur_reel']);
			}

			//################## Sixième boucle ############################
			// Ici on applique les bonus/malus affectant les notes des joueurs
			addLogEvent( ' ************************ APPLICATION DES BONUS/MALUS - BOUCLE 6 **************************');

			//MALUS FUMIGENE Requete note gardien d'une équipe
			$req_note_gardien = $bdd->prepare('SELECT t2.id_compo, t2.note, t2.note_bonus, t2.id_joueur_reel
				FROM compo_equipe t1, joueur_compo_equipe t2, calendrier_ligue t3
				WHERE t1.id_equipe = :id_equipe AND t2.id_compo = t1.id AND t2.numero_definitif = 1
				AND t3.id = t1.id_cal_ligue AND t1.id = t2.id_compo
				AND t3.num_journee_cal_reel = :num_journee_cal_reel');

			//MALUS FUMIGENE Update note gardien
			$upd_note_gardien = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note
				WHERE id_compo = :id_compo AND numero_definitif = 1 ;');

			$tab_effectif = get_effectif_malus_bonus($constante_num_journee_cal_reel, $constanteConfrontationLigue);
			foreach($tab_effectif as $donneesMalusBonus)
			{
				if(is_null($donneesMalusBonus['bonusDom'])){
					addLogEvent('L\'équipe '.$donneesMalusBonus['id_equipe_dom'].' n\'a pas mis de bonus/malus.');
				} else {
					appliquerBonusMalus($donneesMalusBonus['bonusDom'], $donneesMalusBonus['id_equipe_dom'],
						$donneesMalusBonus['id_equipe_ext'], $donneesMalusBonus['compoDom'],
						$donneesMalusBonus['compoExt'], $donneesMalusBonus['id']);
				}

				if(is_null($donneesMalusBonus['bonusExt'])){
					addLogEvent('L\'équipe '.$donneesMalusBonus['id_equipe_ext'].' n\'a pas mis de bonus/malus.');
				} else {
					appliquerBonusMalus($donneesMalusBonus['bonusExt'], $donneesMalusBonus['id_equipe_ext'],
						$donneesMalusBonus['id_equipe_dom'], $donneesMalusBonus['compoExt'],
						$donneesMalusBonus['compoDom'], $donneesMalusBonus['id']);
				}
			}

			//################## Septième boucle ############################
			// Ici on passe en revue tous les joueurs titulaires pour qui le remplacement tactique ne s'est pas appliqué
			// On update les numéros définitifs
			// L'équipe doit être complète

			addLogEvent( ' ************************ CALCUL BUT VIRTUEL EQUIPE - BOUCLE 7 **************************');

			// Calcul But Virtuel
			$tab_confrontation = get_confrontations_par_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue);
			foreach($tab_confrontation as $match)
			{
				calculButVirtuel($match['idDom'],$match['bonusDom'],$match['idExt'],$match['bonusExt']);
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
					update_buteur_impacte_malus_dinarb($listeButeursImpactesMalusDinArb['id_joueur_reel'],$listeButeursImpactesMalusDinArb['id_equipe'],$listeButeursImpactesMalusDinArb['id']);

					addLogEvent('Joueur avec id : ' . $listeButeursImpactesMalusDinArb['id_joueur_reel']
						. ' perd 1 but réel [MALUS DIN ARB] (match=' . $listeButeursImpactesMalusDinArb['id']
						. ', compo=' . $id_compo_deja_affecte . ')');
				}
			}
			//Application des malus équipe de l'adversaire (MAJ Note)
		}	//FIN DE BOUCLE FOR EACH SUR LA LIGUE
	}	//FIN DU IF SUR LA LIGUE

	addLogEvent( ' ************************ NETTOYAGE DES NUMEROS DEFINITIFS A ZERO ET DES NOTES DES REMPLACANTS **************************');;
	nettoyage_joueur_compo_equipe();
	impactCSC($constanteJourneeFormatLong, $constante_num_journee_cal_reel);

	if ($maj_stats_classement) {
		mise_a_jour_stat_classement($constante_num_journee_cal_reel, $constanteJourneeFormatLong, $req_ligues_concernees);
		verifierPariTruque($constante_num_journee_cal_reel, $ligue_unique);
	}

  addLogEvent('Fin calcul des confrontations.');
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

//Remet à NULL les stats de la table JOUEUR COMPO EQUIPE
//Le parametre Ligue_unique, permet de faire tourner le script uniquement sur cette ligue, si sa valeur est null alors on cherche sur toutes les ligues
function raz_table_joueur_compo_equipe_sur_journee($constante_num_journee_cal_reel, $ligue_unique)
{
	global $bdd;
	if(is_null($ligue_unique))
	{
		addLogEvent('FONCTION raz_table_joueur_compo_equipe_sur_journee - Toutes les ligues');
		$upd_remise_a_zero_jce = $bdd->prepare('UPDATE joueur_compo_equipe, calendrier_ligue, compo_equipe
			SET joueur_compo_equipe.note = NULL, joueur_compo_equipe.note_bonus = NULL,
			joueur_compo_equipe.nb_but_reel = NULL, joueur_compo_equipe.nb_but_virtuel = NULL,
			joueur_compo_equipe.nb_csc = NULL, joueur_compo_equipe.numero_definitif = NULL
			WHERE calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
			AND calendrier_ligue.id = compo_equipe.id_cal_ligue AND compo_equipe.id = joueur_compo_equipe.id_compo;');
		$upd_remise_a_zero_jce->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	}else{
		addLogEvent('FONCTION raz_table_joueur_compo_equipe_sur_journee - Uniquement la ligue n°'.$ligue_unique);
		$upd_remise_a_zero_jce = $bdd->prepare('UPDATE joueur_compo_equipe, calendrier_ligue, compo_equipe
			SET joueur_compo_equipe.note = NULL, joueur_compo_equipe.note_bonus = NULL,
			joueur_compo_equipe.nb_but_reel = NULL, joueur_compo_equipe.nb_but_virtuel = NULL,
			joueur_compo_equipe.nb_csc = NULL, joueur_compo_equipe.numero_definitif = NULL
			WHERE calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel
			AND calendrier_ligue.id = compo_equipe.id_cal_ligue AND compo_equipe.id = joueur_compo_equipe.id_compo
			AND calendrier_ligue.id_ligue = :id_ligue;');
		$upd_remise_a_zero_jce->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $ligue_unique));
	}

	$upd_remise_a_zero_jce->closeCursor();
}

function initialiserNoteJoueurMatchAnnule($journee)
{
	global $bdd;
	$q = $bdd->prepare('SELECT cle_roto_primaire FROM joueur_reel WHERE equipe IN (
    	SELECT equipeDomicile
    	FROM resultatsl1_reel WHERE journee = :journee AND winOrLoseDomicile = :ANNULE)
		OR equipe IN (
    	SELECT equipeVisiteur
    	FROM resultatsl1_reel WHERE journee = :journee AND winOrLoseVisiteur = :ANNULE)');
	$q->execute(['journee' => $journee, 'ANNULE' => 'ANNULE']);

	$insert = $bdd->prepare('INSERT IGNORE INTO joueur_stats(id,journee,a_joue,minutes,titulaire,est_rentre,
				est_sorti,jaune,jaune_rouge,rouge,but,passe_d,second_passe_d,tir,tir_cadre,interception,centre,
				centre_reussi,occasion_creee,contre,total_tacle,tacle_reussi,faute_commise,faute_subie,passe,
				passe_tentee,centre_reussi_dans_le_jeu,duel_aerien_gagne,grosse_occasion_creee,ballon_recupere,
				dribble,duel_gagne,ballon_touche,ballon_touche_int_surface,tir_int_surface,tir_ext_surface,
				tir_cadre_int_surface,tir_cadre_ext_surface,but_int_surface,but_ext_surface,ballon_perdu,csc,
				penalty_tire,penalty_marque,penalty_rate,penalty_arrete,corner_tire,corner_centre,corner_gagne,
				coup_franc_centre,coup_franc_centre_reussi,coup_franc_tire,coup_franc_cadre,coup_franc_marque,
				but_concede,cleansheet,arret,arret_tir_int_surface,arret_tir_ext_surface,sortie_ext_surface_reussie,
				penalty_concede,penalty_subi_gb,penalty_arrete_gb,degagement,degagement_reussi,degagement_poing,
				6_buts_ou_plus_pris_sans_penalty,5_buts_pris_sans_penalty,4_buts_pris_sans_penalty,
				3_buts_pris_sans_penalty,2_buts_pris_sans_penalty,1_but_pris_sans_penalty,rouge_60,rouge_75,
				rouge_80,rouge_85,centre_rate,clean_60,clean_60D,ecart_moins_5,ecart_moins_4,ecart_moins_3,
				ecart_moins_2,ecart_plus_2,ecart_plus_3,ecart_plus_4,grosse_occasion_ratee,malus_defaite,
				15_passes_OK_30,15_passes_OK_40,15_passes_OK_50,15_passes_OK_90,15_passes_OK_95,15_passes_OK_100,
				25_passes_OK_30,25_passes_OK_40,25_passes_OK_50,25_passes_OK_90,25_passes_OK_95,25_passes_OK_100,
				tacle_rate,tir_non_cadre,80_ballons_touches,90_ballons_touches,100_ballons_touches,bonus_victoire,
				coup_franc_rate,note)
			VALUES(:id,:journee,1,90,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
				0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
				0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,5)');

	$nbMaj = 0;
	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
	{
		$nbMaj++;
		$insert->execute(['id' => $donnees['cle_roto_primaire'], 'journee' => $journee]);
	}

	addLogEvent('Insertion de ' . $nbMaj . ' lignes dans joueur_stats suite aux matchs annulés.');
}

function creerCacheNoteJoueurReel($journee)
{
	global $bdd;
	$q = $bdd->prepare('SELECT js.note, jr.id
		FROM joueur_stats js, joueur_reel jr
		WHERE js.journee = :journee
		AND js.id IN (jr.cle_roto_primaire, jr.cle_roto_secondaire)');
	$q->execute(['journee' => $journee]);

	$cache = [];
	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
	{
		 $cache[$donnees['id']] = $donnees['note'];
	}

	return $cache;
}

//Récupère le nombre de défenseur à partir du code tactique
function getCacheNbDefParTactique()
{
	global $bdd;

	$q = $bdd->prepare('SELECT code, nb_def FROM nomenclature_tactique');
	$q->execute();

	$cacheNbDefParTectique = [];
	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
	{
		$cacheNbDefParTectique[$donnees['code']] = $donnees['nb_def'];
	}
	$q->closeCursor();
	return $cacheNbDefParTectique;
}

function getMatchParLigueEtJournee($numJournee, $idLigue)
{
	global $bdd;

	$q = $bdd->prepare('SELECT ce_dom.id as idCompoDom, ce_dom.code_tactique as tactiqueDom, b_dom.code as codeBonusDom, b_dom.id_joueur_reel_equipe as idJoueurBonusDom,
		b_dom.id_joueur_reel_adverse as idJoueurAdvBonusDom, ce_ext.id as idCompoExt, ce_ext.code_tactique as tactiqueExt, b_ext.code as codeBonusExt, b_ext.id_joueur_reel_equipe as idJoueurBonusExt,
		b_ext.id_joueur_reel_adverse as idJoueurAdvBonusExt
		FROM calendrier_ligue cl
    JOIN compo_equipe ce_dom ON ce_dom.id_cal_ligue = cl.id AND ce_dom.id_equipe = cl.id_equipe_dom
    JOIN compo_equipe ce_ext ON ce_ext.id_cal_ligue = cl.id AND ce_ext.id_equipe = cl.id_equipe_ext
    LEFT JOIN bonus_malus b_dom ON b_dom.code = ce_dom.code_bonus_malus AND b_dom.id_equipe = ce_dom.id_equipe AND b_dom.id_cal_ligue = cl.id
    LEFT JOIN bonus_malus b_ext ON b_ext.code = ce_ext.code_bonus_malus AND b_ext.id_equipe = ce_ext.id_equipe AND b_ext.id_cal_ligue = cl.id
		WHERE cl.num_journee_cal_reel = :num AND cl.id_ligue = :id
		ORDER BY cl.id');
	$q->execute(array('num' => $numJournee, 'id' => $idLigue));

	$tabMatch = $q->fetchAll();
	$q->closeCursor();

	return $tabMatch;
}

function getJoueurParMatch($idCompoDom, $idCompoExt)
{
	global $bdd;

	$q = $bdd->prepare('SELECT jce.id_compo, jce.id_joueur_reel, jr.cle_roto_primaire, jce.capitaine, jr.position, jce.numero
		FROM joueur_compo_equipe jce
    JOIN joueur_reel jr ON jr.id = jce.id_joueur_reel
		WHERE jce.id_compo IN (:idDom, :idExt)
		ORDER BY jce.id_compo, jce.numero');
		$q->execute(array('idDom' => $idCompoDom, 'idExt' => $idCompoExt));

	$tabJoueur = $q->fetchAll();
	$q->closeCursor();

	return $tabJoueur;
}

function initialiserNoteJoueurCompo($idJoueurReel, $idCompo, $note, $capitaine, $position, $numero,
	$cleRotoPrimaire, $nbDef, $bonus, $idJoueurBonus, $bonusAdv, $idJoueurAdvBonus, $constanteJourneeFormatLong)
{
	$note_bonus = 0; //Bonus de base

	//test ajout bonus capitaine
	if ($capitaine == 1) {
		$victDefaite = get_victoire_ou_defaite_capitaine($idJoueurReel,$constanteJourneeFormatLong);
		if($victDefaite == 2){
				//Le joueur est capitaine et son équipe a gagné => BONUS
				$note += 0.5;
				$note_bonus = 0.5;
				addLogEvent('Capitaine Victoire');
		}elseif($victDefaite == 1){
				//Le joueur est capitaine et son équipe a perdu => MALUS
				$note -= 1;
				$note_bonus = -1;
				addLogEvent('Capitaine Defaite');
		}
	}

	//ajout bonus defense
	if($nbDef == 5 && $position == ConstantesAppli::DEFENSEUR && $numero <= 11){
			//Defense à 5, les défenseurs titulaires prennent un bonus
			$note += 1;
			$note_bonus += 1;
	}else if($nbDef == 4 && $position == ConstantesAppli::DEFENSEUR && $numero <= 11){
			//Defense à 4, les défenseurs titulaires prennent un bonus
			$note += 0.5;
			$note_bonus += 0.5;
	}

	// TODO Bonus ConstantesAppli::BONUS_MALUS_SEL_TRI

	$avecBoucher = FALSE;
	// Prise en compte des bonus
	if($bonus == ConstantesAppli::BONUS_MALUS_CON_ZZ){
		$note += 0.5;
		$note_bonus += 0.5;
	} elseif($bonus == ConstantesAppli::BONUS_MALUS_FAM_STA && $idJoueurBonus == $idJoueurReel) {
		$note += 1;
		$note_bonus += 1;
		addLogEvent('Bonus '.ConstantesAppli::BONUS_MALUS_FAM_STA);
	} elseif($bonus == ConstantesAppli::BONUS_MALUS_BOUCHER && $idJoueurBonus == $idJoueurReel) {
		$note = 0;
		$note_bonus = 0;
		$avecBoucher = TRUE;
		addLogEvent('Bonus '.ConstantesAppli::BONUS_MALUS_BOUCHER);
	} elseif($bonus == ConstantesAppli::BONUS_MALUS_BUS) {
		// Pris en compte boucle 7
	} elseif($bonus == ConstantesAppli::BONUS_MALUS_DIN_ARB) {
		// Pris en compte boucle 8
	} elseif($bonus == ConstantesAppli::BONUS_MALUS_CHA_GB && $numero == 1) {
		addLogEvent('Bonus '.ConstantesAppli::BONUS_MALUS_CHA_GB.' pris en compte en Boucle 4.');
	}

	// Prise en compte des malus
	if($bonusAdv == ConstantesAppli::BONUS_MALUS_MAU_CRA && $idJoueurAdvBonus == $idJoueurReel) {
		$note -= 1;
		$note_bonus -= 1;
		addLogEvent('Malus '.ConstantesAppli::BONUS_MALUS_MAU_CRA);
	} elseif($bonusAdv == ConstantesAppli::BONUS_MALUS_BOUCHER && $idJoueurAdvBonus == $idJoueurReel) {
		$note = 0;
		$note_bonus = 0;
		$avecBoucher = TRUE;
		addLogEvent('Malus '.ConstantesAppli::BONUS_MALUS_BOUCHER);
	} elseif($bonusAdv == ConstantesAppli::BONUS_MALUS_FUMIGENE && $numero == 1) {
		addLogEvent('Malus '.ConstantesAppli::BONUS_MALUS_FUMIGENE.' pris en compte en Boucle 6.');
	}

	//Vérification des plafonds
	if($note > 10){
		addLogEvent($cleRotoPrimaire.' (id='.$idJoueurReel.', compo='.$idCompo.') a eu la note de '.$note.' (bonus='.$note_bonus.') => on plafonne à 10.');
		$note = 10;
	}elseif($note < 0.5 && $avecBoucher == FALSE){
		$note = 0.5;
		addLogEvent($cleRotoPrimaire.' (id='.$idJoueurReel.', compo='.$idCompo.') a eu la note de '.$note.' (bonus='.$note_bonus.') => on force à 0,5.');
	}

	/*  UPDATE DES NOTES DANS LA TABLE */
	addLogEvent($cleRotoPrimaire.' (id='.$idJoueurReel.', compo='.$idCompo.') a eu la note de '.$note.' (bonus='.$note_bonus.').');
	update_note_joueur_note_bonus($note, $note_bonus, $idCompo, $idJoueurReel);
}

//Vérifie si le capitaine a gagné ou perdu  - Renvoie sous forme de tableau //Renvoie 0 si NUL, 1 si Defaite et 2 si Victoire
function get_victoire_ou_defaite_capitaine($id_joueur_reel, $constanteJourneeReelle)
{
	global $bdd;

	$req_victoireOuDefaiteCapitaine = $bdd->prepare('SELECT t3.malus_defaite + 2*t3.bonus_victoire AS victoireOuDefaite
		FROM joueur_reel t1, joueur_stats t3
		WHERE t1.id = :id AND t3.id IN (t1.cle_roto_primaire, t1.cle_roto_secondaire) AND t3.journee = :journee ;');
	$req_victoireOuDefaiteCapitaine->execute(array('id' => $id_joueur_reel, 'journee' => $constanteJourneeReelle));

	$victDefaite = $req_victoireOuDefaiteCapitaine->fetchColumn();
	$req_victoireOuDefaiteCapitaine->closeCursor();
	return $victDefaite;
}

//Change la note et la note_bonus d'un joueur dans une compo
function update_note_joueur_note_bonus($note,$note_bonus,$id_compo,$id_joueur_reel)
{
	addLogEvent('FONCTION update_note_joueur_note_bonus => id='.$id_joueur_reel.' (compo='.$id_compo.') obtient note '.$note.' (dont bonus '.$note_bonus.')');
	global $bdd;

	$upd_noteJoueurCompo = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note, note_bonus = :bonus WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_noteJoueurCompo->execute(array('note' => $note, 'bonus' => $note_bonus, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_noteJoueurCompo->closeCursor();
}

//Récupère les effectifs concernés sur une ligue et sur une journée - Renvoie sous forme de tableau
function get_effectifs_titulaires_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_effectifs_titulaires_ligue_journee');
	global $bdd;

	$req_effectifnote = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t1.id_equipe_dom, t1.id_equipe_ext,
    t3.id_joueur_reel, t4.cle_roto_primaire, t3.capitaine, t4.position, t3.numero , t3.note, t3.note_bonus,
    t2.code_bonus_malus AS code_bonus_malus_equipe, t3.numero_remplacement, t3.id_joueur_reel_remplacant,
    t3.note_min_remplacement
		FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
		WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id
    AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12');
	$req_effectifnote->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_effectifnote->fetchAll();
	$req_effectifnote->closeCursor();
	return $tab_effectif;
}

//Remise à Null de tous les numéros définitifs d'une compo
function remise_a_null_numero_definitif_compo($id_compo)
{
	addLogEvent('FONCTION remise_a_null_numero_definitif_compo (id='.$id_compo.')');
	global $bdd;

	$upd_remiseANullDesNumerosDefinitifs = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = NULL WHERE id_compo = :id_compo ;');
	$upd_remiseANullDesNumerosDefinitifs->execute(array('id_compo' => $id_compo));
	$upd_remiseANullDesNumerosDefinitifs->closeCursor();
}

//Remise à Null de tous les buts reels d'une compo
function remise_a_null_buts_reels_compo($id_compo)
{
	addLogEvent('FONCTION remise_a_null_buts_reels_compo (id='.$id_compo.')');
	global $bdd;

	$upd_remiseANullDesButsReels = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = NULL WHERE id_compo = :id_compo ;');
	$upd_remiseANullDesButsReels->execute(array('id_compo' => $id_compo));
	$upd_remiseANullDesButsReels->closeCursor();
}

//Récupère les remplaçant concernés sur une ligue, sur une equipe et sur une journée - Renvoie sous forme de tableau
function get_effectifs_remplacant_ligue_journee_equipe($constante_num_journee_cal_reel,$constanteConfrontationLigue,$id_equipe)
{
	addLogEvent('FONCTION get_effectifs_remplacant_ligue_journee_equipe');
	global $bdd;

	$req_remplacant = $bdd->prepare('SELECT t3.id_joueur_reel, t4.cle_roto_primaire,  t4.position, t3.numero,
    t3.note, t3.note_bonus, t3.numero_definitif
    FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
    WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id
    AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t2.id_equipe = :id_equipe
    AND t3.numero_definitif IS NULL ORDER By t3.numero');
	$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue, 'id_equipe' => $id_equipe));

	$tab_effectif = $req_remplacant->fetchAll();
	$req_remplacant->closeCursor();
	return $tab_effectif;
}

//Change le numero définitif d'un joueur en fonction de la compo, et l'id_joueur_reel
function update_numero_definitif($numero_definitif,$id_compo,$id_joueur_reel)
{
	addLogEvent('FONCTION update_numero_definitif => id='.$id_joueur_reel.' (compo='.$id_compo.') => '.$numero_definitif);
	global $bdd;

			//On update le numéro définitif
	$upd_numeroDefinitif = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = :numero_definitif WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_numeroDefinitif->execute(array('numero_definitif' => $numero_definitif, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_numeroDefinitif->closeCursor();
}

//On change le nb de but reel d'un joueur selon son id, sa compo et sa journee A PARTIR DES STATS
function updateButReelDuJoueur($id_compo, $journee, $id_joueur_reel)
{
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
		addLogEvent('Erreur le joueur : '.$id_joueur_reel.' (compo='.$id_compo.') a plusieurs lignes de stat sur la journée : '.$journee);
	}else{
		foreach ($lignesNbButReel as $ligneNbButReel) {
			if($ligneNbButReel['but']>0){
				//Ce joueur a marqué au moins un but durant cette journée
				addLogEvent($id_joueur_reel.' (compo='.$id_compo.') a marqué '.$ligneNbButReel['but'].' but(s) réel(s).');
				$upd_butReel->execute(array('nb_but_reel' => $ligneNbButReel['but'], 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
				$upd_butReel->closeCursor();
			}
		}
	}
	$req_nbButReel->closeCursor();
}

//Récupère les effectifs titulaires n'ayant pas été remplacés sur une ligue et sur une journée - Renvoie sous forme de tableau
function get_effectifs_non_remplace_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_effectifs_non_remplace_ligue_journee');
	global $bdd;

	$req_effectif_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel,
    t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.numero_definitif
    FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
    WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id
    AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL
    AND t3.note IS NULL ORDER BY id_compo, t3.numero ASC');
	$req_effectif_nonRemplace->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));

	$tab_effectif = $req_effectif_nonRemplace->fetchAll();
	$req_effectif_nonRemplace->closeCursor();
	return $tab_effectif;
}

//Change la note d'un joueur dans une compo
function update_note_joueur_compo($note,$id_compo,$id_joueur_reel)
{
	addLogEvent('FONCTION update_note_joueur_compo => id='.$id_joueur_reel.' (compo='.$id_compo.') obtient note '.$note);
	global $bdd;

	$upd_noteJoueurCompo = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_noteJoueurCompo->execute(array('note' => $note, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_noteJoueurCompo->closeCursor();
}

//Change le bonus perçu par un joueur dans une compo
function update_note_bonus_joueur_compo($note,$id_compo,$id_joueur_reel)
{
	addLogEvent('FONCTION update_note_bonus_joueur_compo => id='.$id_joueur_reel.' (compo='.$id_compo.') obtient note bonus '.$note);
	global $bdd;

	$upd_noteBonus = $bdd->prepare('UPDATE joueur_compo_equipe SET note_bonus = :note WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_noteBonus->execute(array('note' => $note, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_noteBonus->closeCursor();
}

//Récupère les effectifs titulaires n'ayant pas été remplacés sur une ligue et sur une journée - Renvoie sous forme de tableau
function get_attaquants_non_remplace_ligue_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
	addLogEvent('FONCTION get_attaquants_non_remplace_ligue_journee');
	global $bdd;

	$req_attaquant_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel,
    t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.note_bonus, t3.numero_definitif
    FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
    WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id
    AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL
    AND t3.note IS NULL AND t4.position = \'Forward\' ORDER BY id_compo, t3.numero ASC');
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

	$req_joueurAvecRemplacementTactiqueActif= $bdd->prepare('SELECT DISTINCT t3.id_joueur_reel, t3.id_compo,
    t4.cle_roto_primaire, t3.numero, t3.id_joueur_reel_remplacant
    FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
    WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id
    AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL
    AND t3.note IS NOT NULL AND t3.note < t3.note_min_remplacement AND t3.id_joueur_reel_remplacant IN (
      SELECT t3.id_joueur_reel
      FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
      WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = id_ligue AND t2.id_cal_ligue = t1.id
      AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t3.numero_definitif IS NULL
      AND t3.note IS NOT NULL)');

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

	$req_malus_bonus= $bdd->prepare('SELECT cl.id, cl.id_equipe_dom, cl.id_equipe_ext,
			ce1.code_bonus_malus as bonusDom, ce2.code_bonus_malus as bonusExt,
			ce1.id as compoDom, ce2.id as compoExt
			FROM calendrier_ligue cl
			JOIN compo_equipe ce1 ON cl.id = ce1.id_cal_ligue AND cl.id_equipe_dom = ce1.id_equipe
			JOIN compo_equipe ce2 ON cl.id = ce2.id_cal_ligue AND cl.id_equipe_ext = ce2.id_equipe
			WHERE cl.id_ligue = :id AND cl.num_journee_cal_reel = :num');

	$req_malus_bonus->execute(array('num' => $constante_num_journee_cal_reel, 'id' => $constanteConfrontationLigue));
	$tab_effectif = $req_malus_bonus->fetchAll();
	$req_malus_bonus->closeCursor();
	return $tab_effectif;
}

function appliquerBonusMalus($bonus, $idEquipe, $idEquipeAdv, $idCompo, $idCompoAdv, $idCalLigue)
{
	if($bonus == ConstantesAppli::BONUS_MALUS_FUMIGENE){
		//Léquipe qui a mis ce malus/bonus est l'équipe domicile
		addLogEvent('L\'équipe '.$idEquipe.' a mis un Fumigène à l\'équipe '.$idEquipeAdv);
		$ligneNoteGardienAdverse = get_note_gardien_equipe($idCompoAdv);
		if(count($ligneNoteGardienAdverse) == 1) {
			//Pas d'erreur on a qu'un seul retour
			foreach ($ligneNoteGardienAdverse as $noteGardienAdverse) {
				if($noteGardienAdverse['note'] <= 0.5 || is_null($noteGardienAdverse['note'])){
					//Fumigene non appliqué car pas de gardien ou gardien a déjà la note minimum
					addLogEvent('Impossible d\'appliquer le fumigene sur le gardien adverse (note minimum ou tontonpat).');
				}else{
					$noteUpdateGardien = $noteGardienAdverse['note'] - 1;
					if($noteUpdateGardien < 0.5){
						$noteUpdateGardien = 0.5 ;
					}

					addLogEvent('FUMIGENE: Malus de -1 sur le gardien de la compo '.$idCompoAdv.' => note : '.$noteUpdateGardien);
					update_note_gardien($noteUpdateGardien,$idCompoAdv);
					update_note_bonus_joueur_compo($noteGardienAdverse['note_bonus']-1,$idCompoAdv,$noteGardienAdverse['id_joueur_reel']);
				}
			}
		}
	}elseif($bonus == ConstantesAppli::BONUS_MALUS_BOUCHER){
		//A FAIRE
		//Un joueur à 0 et sans but dans chaque camp
		addLogEvent( $bonus);
	}elseif($bonus == ConstantesAppli::BONUS_MALUS_CHA_GB){
		//A FAIRE
		//Remplacement tactique sur le gardien
		addLogEvent( $bonus);
	}elseif($bonus == ConstantesAppli::BONUS_MALUS_DIN_ARB){
		addLogEvent($bonus.' (traité en boucle 8)')	;
	}elseif($bonus == ConstantesAppli::BONUS_MALUS_MAU_CRA){
		//A FAIRE
		//Note de -1 pour un joueur adverse
		addLogEvent( $bonus);
	}
}

//Requete note gardien d'une équipe
function get_note_gardien_equipe($idCompo)
{
	addLogEvent('FONCTION get_note_gardien_equipe => Compo ' . $idCompo);
	global $bdd;

	$req_note_gardien = $bdd->prepare('SELECT note, note_bonus, id_joueur_reel
		FROM joueur_compo_equipe
		WHERE numero_definitif = 1 AND id_compo = :id');
	$req_note_gardien->execute(array('id' => $idCompo));

	$tab_note = $req_note_gardien->fetchAll();
	$req_note_gardien->closeCursor();

	return $tab_note;
}

//Change la note du gardien fonction de la compo
function update_note_gardien($note,$id_compo)
{
	addLogEvent('FONCTION update_note_gardien (compo='.$id_compo.', note='.$note.')');
	global $bdd;

	//MALUS FUMIGENE Update note gardien
	$upd_note_gardien = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND numero_definitif = 1 ;');
	$upd_note_gardien->execute(array('note' => $note, 'id_compo' => $id_compo));
	$upd_note_gardien->closeCursor();
}

//Récupère les confrontations d'une journée sur une ligue - Renvoie sous forme de tableau
function get_confrontations_par_journee($constante_num_journee_cal_reel,$constanteConfrontationLigue)
{
  // TODO vérifier doublon avec getMatchParLigueEtJournee
	addLogEvent('FONCTION get_confrontations_par_journee');
	global $bdd;

	$req_listeConfrontationParJournee = $bdd->prepare('SELECT cl.id, ce1.id as idDom,
			ce1.code_bonus_malus as bonusDom, ce2.id as idExt, ce2.code_bonus_malus as bonusExt
			FROM calendrier_ligue cl
			JOIN compo_equipe ce1 ON cl.id = ce1.id_cal_ligue AND cl.id_equipe_dom = ce1.id_equipe
			JOIN compo_equipe ce2 ON cl.id = ce2.id_cal_ligue AND cl.id_equipe_ext = ce2.id_equipe
			WHERE cl.id_ligue = :id_ligue AND cl.num_journee_cal_reel = :num_journee_cal_reel ORDER BY cl.id');

	$req_listeConfrontationParJournee->execute(array('id_ligue' => $constanteConfrontationLigue, 'num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$tab_confrontation = $req_listeConfrontationParJournee->fetchAll();
	$req_listeConfrontationParJournee->closeCursor();

	return $tab_confrontation;
}
function calculButVirtuel($equipeA,$bonusA,$equipeB,$bonusB){
	addLogEvent('FONCTION calculButVirtuel entre équipes DOM '.$equipeA.' (bonus='.$bonusA.') et EXT '.$equipeB.' (bonus='.$bonusB.')');

	$moyGardienA = 1;
	$moyGardienB = 1;

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

	$tab_compo_definitiveA = get_compo_definitive($equipeA);
	if ($bonusA != ConstantesAppli::BONUS_MALUS_BUS) {
		//Boucle CALCUL MOYENNE ET TONTON PAT sur la compo domicile
		foreach($tab_compo_definitiveA as $compoDefinitive)
		{
			/*addLogEvent('id='.$compoDefinitive['id_joueur_reel'].',cle='.$compoDefinitive['cle_roto_primaire'].
				',pos='.$compoDefinitive['position'].',num='.$compoDefinitive['numero_definitif'].',note='.$compoDefinitive['note'].
				',but='.$compoDefinitive['nb_but_reel'].',nbDef='.$compoDefinitive['nb_def'].',nbMil='.$compoDefinitive['nb_mil'].',nbAtt='.$compoDefinitive['nb_att']);*/
			if($compoDefinitive['numero_definitif'] == 1){
				if(!is_null($compoDefinitive['note'])){
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
		addLogEvent('MOY Compo Dom ['.$equipeA.'] MoyGB = '.$moyGardienA.' MoyDefense = '.$moyDefenseA.' MoyMilieu = '.$moyMilieuA.' MoyAttaque = '.$moyAttaqueA);
		addLogEvent('TONTON Compo Dom ['.$equipeA.'] TontonPatDef = '.$tontonPatDefenseA.' TontonPatMil = '.$tontonPatMilieuA.' TontonPatAtt = '.$tontonPatAttaqueA);
	}

	$tab_compo_definitiveB = get_compo_definitive($equipeB);
	if ($bonusB != ConstantesAppli::BONUS_MALUS_BUS) {
		//Boucle CALCUL MOYENNE ET TONTON PAT sur la compo domicile
		foreach($tab_compo_definitiveB as $compoDefinitive)
		{
			if($compoDefinitive['numero_definitif'] == 1){
				if(!is_null($compoDefinitive['note'])){
					$moyGardienB =  $compoDefinitive['note'];
				}
			}else{
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['note'])){
						$tontonPatDefenseB++;
					}else{
						$moyDefenseB += $compoDefinitive['note'];
						$nbDefB++;
					}
				}else{
					if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['note'])){
							$tontonPatMilieuB++;
						}else{
							$moyMilieuB += $compoDefinitive['note'];
							$nbMilB++;
						}
					}else{
						if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
							if(is_null($compoDefinitive['note'])){
								$tontonPatAttaqueB++;
							}else{
								$moyAttaqueB += $compoDefinitive['note'];
								$nbAttB++;
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
		addLogEvent('MOY Compo Ext ['.$equipeB.'] MoyGB = '.$moyGardienB.' MoyDefense = '.$moyDefenseB.' MoyMilieu = '.$moyMilieuB.' MoyAttaque = '.$moyAttaqueB);
		addLogEvent('TONTON Compo Ext ['.$equipeB.'] TontonPatDef = '.$tontonPatDefenseB.' TontonPatMil = '.$tontonPatMilieuB.' TontonPatAtt = '.$tontonPatAttaqueB);
	}

	//Boucle CALCUL Buts virtuels
	if ($bonusB != ConstantesAppli::BONUS_MALUS_BUS) {
		foreach($tab_compo_definitiveA as $compoDefinitive)
		{
			if($compoDefinitive['numero_definitif'] > 1) {
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] >= $moyAttaqueB) && ($compoDefinitive['note']-1 >= $moyMilieuB) && ($compoDefinitive['note']-1.5 >= $moyDefenseB) && ($compoDefinitive['note']-2 >= $moyGardienB)){
						//butVirtuel
						addLogEvent('But Virtuel de '.$compoDefinitive['cle_roto_primaire'].' (id='.$compoDefinitive['id_joueur_reel'].') avec une note de '.$compoDefinitive['note']);
						update_but_virtuel(1,$equipeA,$compoDefinitive['id_joueur_reel']);
					}else{
						//update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
					}
				}elseif($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] >= $moyMilieuB) && ($compoDefinitive['note']-1 >= $moyDefenseB) && ($compoDefinitive['note']-1.5 >= $moyGardienB)){
							//butVirtuel
							addLogEvent('But Virtuel de '.$compoDefinitive['cle_roto_primaire'].' (id='.$compoDefinitive['id_joueur_reel'].') avec une note de '.$compoDefinitive['note']);
							update_but_virtuel(1,$equipeA,$compoDefinitive['id_joueur_reel']);
						}else{
							//update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
						}
				}elseif($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] >= $moyDefenseB) && ($compoDefinitive['note']-1 >= $moyGardienB)){
								//butVirtuel
								addLogEvent('But Virtuel de '.$compoDefinitive['cle_roto_primaire'].' (id='.$compoDefinitive['id_joueur_reel'].') avec une note de '.$compoDefinitive['note']);
								update_but_virtuel(1,$equipeA,$compoDefinitive['id_joueur_reel']);
						}else{
								//update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
						}
				}
			}
		}
	} else {
		addLogEvent('On ne calcule pas les buts virtuels de la compo '.$equipeA.' car malus BUS.');
	}
	if ($bonusA != ConstantesAppli::BONUS_MALUS_BUS) {
		foreach($tab_compo_definitiveB as $compoDefinitive)
		{
			if($compoDefinitive['numero_definitif'] > 1) {
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] >= $moyAttaqueA) && ($compoDefinitive['note']-1 >= $moyMilieuA) && ($compoDefinitive['note']-1.5 >= $moyDefenseA) && ($compoDefinitive['note']-2 > $moyGardienA)){
						//butVirtuel
						addLogEvent('But Virtuel de '.$compoDefinitive['cle_roto_primaire'].' (id='.$compoDefinitive['id_joueur_reel'].') avec une note de '.$compoDefinitive['note']);
						update_but_virtuel(1,$equipeB,$compoDefinitive['id_joueur_reel']);
					}else{
						//update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
					}
				}elseif($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] >= $moyMilieuA) && ($compoDefinitive['note']-1 >= $moyDefenseA) && ($compoDefinitive['note']-1.5 > $moyGardienA)){
							//butVirtuel
							addLogEvent('But Virtuel de '.$compoDefinitive['cle_roto_primaire'].' (id='.$compoDefinitive['id_joueur_reel'].') avec une note de '.$compoDefinitive['note']);
							update_but_virtuel(1,$equipeB,$compoDefinitive['id_joueur_reel']);
						}else{
							//update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
						}
				}elseif($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] >= $moyDefenseA) && ($compoDefinitive['note']-1 > $moyGardienA)){
								//butVirtuel
								addLogEvent('But Virtuel de '.$compoDefinitive['cle_roto_primaire'].' (id='.$compoDefinitive['id_joueur_reel'].') avec une note de '.$compoDefinitive['note']);
								update_but_virtuel(1,$equipeB,$compoDefinitive['id_joueur_reel']);
						}else{
								//update_but_virtuel(NULL,$equipeA,$compoDefinitive['id_joueur_reel']);
						}
				}
			}
		}
	} else {
		addLogEvent('On ne calcule pas les buts virtuels de la compo '.$equipeB.' car malus BUS.');
	}
}

//Renvoie la compo définitive d'un id_compo
function get_compo_definitive($id_compo)
{
	addLogEvent('FONCTION get_compo_definitive => Compo ' . $id_compo);
	global $bdd;

	$req_compoDefinitive = $bdd->prepare('SELECT t1.id_joueur_reel, t2.cle_roto_primaire, t2.position,
		t1.numero_definitif , t1.note, t1.nb_but_reel, t4.nb_def, t4.nb_mil, t4.nb_att
		FROM joueur_compo_equipe t1, joueur_reel t2, compo_equipe t3, nomenclature_tactique t4
		WHERE t1.id_compo = :id_compo AND t1.id_joueur_reel = t2.id AND t1.numero_definitif > 0
		AND t3.id = t1.id_compo AND t3.code_tactique = t4.code
		ORDER BY t1.numero_definitif ASC');
	$req_compoDefinitive->execute(array('id_compo' => $id_compo));

	$tab_compo_definitive = $req_compoDefinitive->fetchAll();
	$req_compoDefinitive->closeCursor();

	return $tab_compo_definitive;
}

function get_buteurs_impactes_malus_dinarb($constante_num_journee_cal_reel, $idLigue)
{
	addLogEvent('FONCTION get_buteurs_impactes_malus_dinarb');
	global $bdd;

	$req_buteurs_impactes_par_malus_dinarb = $bdd->prepare('SELECT IF(t5.id_equipe = cl.id_equipe_dom,
		cl.id_equipe_ext, cl.id_equipe_dom) AS id_equipe, cl.id, t4.id_compo, t4.id_joueur_reel, t4.nb_but_reel
		FROM joueur_compo_equipe t4, compo_equipe t5, calendrier_ligue cl
		WHERE cl.id = t5.id_cal_ligue AND t5.id = t4.id_compo AND t4.numero_definitif IS NOT NULL
		AND t4.nb_but_reel > 0 AND cl.num_journee_cal_reel = :num_journee_cal_reel AND cl.id_ligue = :idLigue
		AND t5.id_equipe IN(
			SELECT t1.id_equipe_dom
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3
			WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :idLigue AND t1.id = t2.id_cal_ligue
			AND t2.code_bonus_malus = :codeBonus AND t3.id_compo = t2.id AND t1.id_equipe_dom != t2.id_equipe
			UNION SELECT t1.id_equipe_ext
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3
			WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :idLigue AND t1.id = t2.id_cal_ligue
			AND t2.code_bonus_malus = :codeBonus AND t3.id_compo = t2.id AND t1.id_equipe_ext != t2.id_equipe);');
	$req_buteurs_impactes_par_malus_dinarb->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'idLigue' => $idLigue, 'codeBonus' => ConstantesAppli::BONUS_MALUS_DIN_ARB));
	$tab_joueur = $req_buteurs_impactes_par_malus_dinarb->fetchAll();
	$req_buteurs_impactes_par_malus_dinarb->closeCursor();
	return $tab_joueur;
}

function modification_but_reel_joueur($nb_but_reel, $id_compo, $id_joueur_reel)
{
	addLogEvent('FONCTION modification_but_reel_joueur');
	global $bdd;
	$upd_nb_but_buteur = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = :nb_but_reel WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
	$upd_nb_but_buteur->execute(array('nb_but_reel' => $nb_but_reel, 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
	$upd_nb_but_buteur->closeCursor();
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

	$req_score_dom = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_but_reel,0)) + SUM(IFNULL(t3.nb_but_virtuel,0)) AS score_domicile, SUM(IFNULL(t3.nb_csc,0)) AS csc_concedes FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_dom  GROUP BY t3.id_compo;');
	$req_csc_adversaire_dom = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_csc,0)) AS csc_concedes FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.id = :id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_ext  GROUP BY t3.id_compo;');
  $upd_maj_score_dom = $bdd->prepare('UPDATE calendrier_ligue SET score_dom = :score_dom WHERE calendrier_ligue.id = :id ;');

  $req_score_dom->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
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

	$req_score_ext = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_but_reel,0)) + SUM(IFNULL(t3.nb_but_virtuel,0)) AS score_exterieur FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_ext  GROUP BY t3.id_compo;');
	$req_csc_adversaire_ext = $bdd->prepare('SELECT t1.id, SUM(IFNULL(t3.nb_csc,0)) AS csc_concedes FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3 WHERE t1.id = :id AND t3.id_compo = t2.id AND t3.numero_definitif IS NOT NULL AND t2.id_equipe = t1.id_equipe_dom  GROUP BY t3.id_compo;');
  $upd_maj_score_ext = $bdd->prepare('UPDATE calendrier_ligue SET score_ext = :score_ext WHERE calendrier_ligue.id = :id ;');

	$req_score_ext->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
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

	$req_nb_match = $bdd->prepare('SELECT count(*) AS nb_match, SUM(IFNULL(t3.nb_but_reel,0)) AS nb_but_reel,
    SUM(IFNULL(t3.nb_but_virtuel,0)) AS nb_but_virtuel, SUM(IFNULL(t3.nb_csc,0)) AS nb_csc, t1.id_ligue,
    t2.id_equipe, t3.id_joueur_reel
    FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3
    WHERE t3.id_compo = t2.id AND t2.id_cal_ligue = t1.id AND t3.numero_definitif IS NOT NULL
    AND t1.score_dom IS NOT NULL AND t1.score_ext IS NOT NULL
    GROUP BY t1.id_ligue, t2.id_equipe, t3.id_joueur_reel ORDER BY t3.id_joueur_reel;');
  $upd_maj_nb_match = $bdd->prepare('UPDATE joueur_equipe SET nb_match = :nb_match, nb_but_reel = :nb_but_reel, nb_but_virtuel = :nb_but_virtuel, nb_csc = :nb_csc WHERE id_ligue = :id_ligue AND id_equipe = :id_equipe AND id_joueur_reel = :id_joueur_reel;');

	$req_nb_match->execute();
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
	$upd_nb_malus = $bdd->prepare('UPDATE equipe e, (SELECT cl.id_equipe_ext AS equipe_victime, cl.id_ligue
    FROM compo_equipe ce, calendrier_ligue cl
    WHERE ce.code_bonus_malus IS NOT NULL AND ce.id_cal_ligue = cl.id AND cl.num_journee_cal_reel = :num_journee_cal_reel
    AND ce.id_equipe = cl.id_equipe_dom
    UNION SELECT cl1.id_equipe_dom AS equipe_victime, cl1.id_ligue
    FROM compo_equipe ce1, calendrier_ligue cl1
    WHERE ce1.code_bonus_malus IS NOT NULL AND ce1.id_cal_ligue = cl1.id AND cl1.num_journee_cal_reel = :num_journee_cal_reel
    AND ce1.id_equipe = cl1.id_equipe_ext) t1
    SET e.nb_malus = e.nb_malus+1
    WHERE e.id_ligue = t1.id_ligue AND e.id = t1.equipe_victime;');
	$upd_nb_malus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$upd_nb_malus->closeCursor();

	//CLASSEMENT => TABLE EQUIPE

	$req_classement_ligue = $bdd->prepare('SELECT tmp.id
		FROM (
			SELECT e.id_ligue, e.id, ((e.nb_victoire*3)+e.nb_nul) as points,
			CAST(e.nb_but_pour AS SIGNED)-CAST(e.nb_but_contre AS SIGNED) as diff_but,
			CAST(e.nb_bonus AS SIGNED)-CAST(e.nb_malus AS SIGNED) as diff_bonus
			FROM equipe e
			WHERE e.id_ligue = :id_ligue) tmp
		GROUP BY tmp.id_ligue, tmp.id
		ORDER BY tmp.points DESC, tmp.diff_but DESC, tmp.diff_bonus DESC;');
	$upd_classement_ligue = $bdd->prepare('UPDATE equipe SET classement = :classement WHERE id_ligue = :id_ligue and id = :id;');

  $req_ligues_concernees->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
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
}

function verifierPariTruque($constante_num_journee_cal_reel, $ligue_unique)
{
	addLogEvent('Début vérification des paris truqués.');

	global $bdd;
	if(is_null($ligue_unique)) {
		$q = $bdd->prepare('SELECT ce.id_equipe, e.id_coach, l.nom
			FROM compo_equipe ce
      JOIN equipe e ON e.id = ce.id_equipe
			JOIN calendrier_ligue cl ON cl.id = ce.id_cal_ligue
			JOIN ligue l on l.id = e.id_ligue
			JOIN calendrier_reel cr ON cr.num_journee = cl.num_journee_cal_reel
			WHERE cr.num_journee = :num
			AND ce.pari_dom = cl.score_dom AND ce.pari_ext = cl.score_ext');
	  $q->execute([':num' => $constante_num_journee_cal_reel]);
	} else {
		$q = $bdd->prepare('SELECT ce.id_equipe, e.id_coach, l.nom
			FROM compo_equipe ce
      JOIN equipe e ON e.id = ce.id_equipe
			JOIN calendrier_ligue cl ON cl.id = ce.id_cal_ligue
			JOIN ligue l on l.id = e.id_ligue
			JOIN calendrier_reel cr ON cr.num_journee = cl.num_journee_cal_reel
			WHERE cr.num_journee = :num AND cl.id_ligue = :id
			AND ce.pari_dom = cl.score_dom AND ce.pari_ext = cl.score_ext');
	  $q->execute([':num' => $constante_num_journee_cal_reel, ':id' => $ligue_unique]);
	}

	$tabBonus = getNomenclatureBonusMalus();
	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
	{
		$bonus = $tabBonus[rand(0,4)];
		$equipe = $donnees['id_equipe'];
		$coach = $donnees['id_coach'];
		$nomLigue = $donnees['nom'];
		$libActu = 'Congrats ! Tu as gagné un bonus "' . $bonus['libelle_court'] .
			'" dans la ligue "' . $nomLigue . '" grâce à ton pari truqué.';

		$q2 = $bdd->prepare('INSERT INTO bonus_malus(code, id_equipe) VALUES(:code, :idEquipe)');
		$q2->bindValue(':code', $bonus['code']);
		$q2->bindValue(':idEquipe', $equipe);
		$q2->execute();

		creerActualiteCoach($coach, $equipe, $libActu);

		addLogEvent('Ajout du bonus ' . $bonus['code'] . ' pour l\'équipe ' . $equipe . ' (coach=' . $coach . ').');
	}
	$q->closeCursor();

	addLogEvent('Fin vérification des paris truqués.');
}

function getNomenclatureBonusMalus()
{
	global $bdd;

	$q = $bdd->prepare('SELECT code, libelle_court FROM nomenclature_bonus_malus');
	$q->execute();
	// TODO Ajouter bonus quand prise en compte OK dans cron

	$tabBonus = [];
	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
	{
		$code = $donnees['code'];
		if ($code == ConstantesAppli::BONUS_MALUS_FUMIGENE
			|| $code == ConstantesAppli::BONUS_MALUS_DIN_ARB
			|| $code == ConstantesAppli::BONUS_MALUS_FAM_STA
			|| $code == ConstantesAppli::BONUS_MALUS_BUS
      || $code == ConstantesAppli::BONUS_MALUS_MAU_CRA
			|| $code == ConstantesAppli::BONUS_MALUS_CON_ZZ) {
			$tabBonus[] = $donnees;
		}
	}

	return $tabBonus;
}

function creerActualiteCoach($idCoach, $idEquipe, $libelle)
{
	global $bdd;

	$q2 = $bdd->prepare('INSERT INTO actualite_coach(id_coach, id_equipe, libelle, date_creation)
		VALUES(:coach, :equipe, :libelle, NOW())');
	$q2->bindValue(':coach', $idCoach);
	$q2->bindValue(':equipe', $idEquipe);
	$q2->bindValue(':libelle', $libelle);
	$q2->execute();
}
?>
