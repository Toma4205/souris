<?php
	$constanteConfrontationLigue = 5;
	$constanteJourneeReelle = '201719';
	$constante_num_journee_cal_reel = '17';
	
	function updateButReelDuJoueur($id_compo, $journee, $id_joueur_reel, $bdd){
		//On compte le nombre de but réel d'un joueur sur une journée
		$req_nbButReel=$bdd->prepare('SELECT t3.but FROM joueur_compo_equipe t1, joueur_reel t2, joueur_stats t3 WHERE t1.id_joueur_reel = t2.id AND t3.id IN (t2.cle_roto_primaire, t2.cle_roto_secondaire) AND t3.journee = :journee AND t1.id_joueur_reel = :id_joueur_reel');
		
		//On update un but réel
		$upd_butReel= $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = :nb_but_reel WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
		
		//On regarde le nombre de but réel marqué par ce joueur sur cette journée
		$req_nbButReel->execute(array('journee' => $journee, 'id_joueur_reel' => $id_joueur_reel));
		$lignesNbButReel = $req_nbButReel->fetchAll();
		if (count($lignesNbButReel) > 1) {
			//Erreur, il ne doit y avoir qu'une seule ligne par joueur par journée
			echo 'Erreur le joueur : '.$id_joueur_reel.' a plusieurs lignes de stat sur la journée : '.$journee;
			echo "<br />\n";
		}else{
			foreach ($lignesNbButReel as $ligneNbButReel) {
				if($ligneNbButReel['but']>0){
					//Ce joueur a marqué au moins un but durant cette journée
					echo $id_joueur_reel.' a marqué '.$ligneNbButReel['but'].' but(s) réel(s)';
					echo "<br />\n";
					$upd_butReel->execute(array('nb_but_reel' => $ligneNbButReel['but'], 'id_compo' => $id_compo, 'id_joueur_reel' => $id_joueur_reel));
					$upd_butReel->closeCursor();
				}
			}
		}
		
		$req_nbButReel->closeCursor();
	}
	
	function afficherUneCompo($id_compo, $bdd){
		$req_compoDefinitive = $bdd->prepare('SELECT t2.cle_roto_primaire, t2.position, t1.numero_definitif , t1.note, t1.nb_but_reel, t4.nb_def, t4.nb_mil, t4.nb_att FROM joueur_compo_equipe t1, joueur_reel t2, compo_equipe t3, nomenclature_tactique t4 WHERE t1.id_compo = :id_compo AND t1.id_joueur_reel = t2.id AND t1.numero_definitif > 0 AND t1.numero_definitif < 12 AND t1.numero_definitif IS NOT NULL AND t3.id = t1.id_compo AND t3.code_tactique = t4.code ORDER BY t1.numero_definitif ASC;');
		
		$req_compoDefinitive->execute(array('id_compo' => $id_compo));
		echo "<br />\n";
		echo '########################################################';
		echo "<br />\n";
		$premiereBoucle = 0;
		$defenseurs=' ';
		$milieux=' ';
		$attaquants=' ';
		while ($compoDefinitive = $req_compoDefinitive->fetch())
		{
			
			if($compoDefinitive['numero_definitif'] == 1){
				if(is_null($compoDefinitive['note'])){
					$gardien = 'P\'tit jeune du club (2.5)';
				}else{
					$gardien = $compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].')';
				}
			}else{
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['note'])){
						$defenseurs .= ' Tonton Pat (0) ';
					}else{
						$defenseurs .= ' '.$compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].') ';
						if(!is_null($compoDefinitive['nb_but_reel'])){
							$defenseurs .= '- '.$compoDefinitive['nb_but_reel'].' but(s) ';
						}
					}
				}else{
					if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['note'])){
							$milieux .= ' Tonton Pat (0) ';
						}else{
							$milieux .= ' '.$compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].') ';
							if(!is_null($compoDefinitive['nb_but_reel'])){
								$milieux .= '- '.$compoDefinitive['nb_but_reel'].' but(s) ';
							}
						}
					}else{
						if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
							if(is_null($compoDefinitive['note'])){
								$attaquants .= ' Tonton Pat (0) ';
							}else{
								$attaquants .= ' '.$compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].') ';
								if(!is_null($compoDefinitive['nb_but_reel'])){
									$attaquants .= '- '.$compoDefinitive['nb_but_reel'].' but(s) ';
								}
							}
						}
					}
				}
			}
		}
		
		echo $gardien;
		echo "<br />\n";
		echo "<br />\n";
		echo $defenseurs;
		echo "<br />\n";
		echo "<br />\n";
		echo $milieux;
		echo "<br />\n";
		echo "<br />\n";
		echo $attaquants;
		echo "<br />\n";
		echo '########################################################';

		$req_compoDefinitive->closeCursor();
	}
	
	
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
		
// ******************************* CALCUL ET UPDATE DES NOTES **************************************************************
		
		echo "<br />\n";
		echo ' ************************ CALCUL ET UPDATE DES NOTES **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		$req_effectifs = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t1.id_equipe_dom, t1.id_equipe_ext, t3.id_joueur_reel, t4.cle_roto_primaire, t3.capitaine, t4.position, t3.numero , t2.code_tactique, t3.code_bonus_malus AS \'code_bonus_malus_joueur\', t2.code_bonus_malus AS \'code_bonus_malus_equipe\', t3.numero_remplacement, t3.id_joueur_reel_remplacant, t3.note_min_remplacement FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel;');
		
		//constante 17 pour le test uniquement
		$req_effectifs->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));
		/*
			Permet d'obtenur un tableau au format suivant : id_equipe_dom / id_equipe_ext / id_equipe / cle_roto_primaire / position / numero / code_tactique / code_bonus_malus / capitaine / note_min_remplacement
		*/
		
		/*
			Boucle pour 
						- update des notes obtenues après-bonus dans la table joueur_compo_equipe
						- cumul des buts réels
						- calculs des moyennes par ligne
						- détection des buts virtuels
						- update des scores de la confrontation
		*/
		
				
		$req_noteDuJoueurJournee = $bdd->prepare('SELECT t1.note FROM joueur_stats t1, joueur_compo_equipe t2, joueur_reel t3 WHERE t2.id_joueur_reel = :id_joueur_reel AND t1.journee = :journee AND t2.id_joueur_reel = t3.id AND t1.id IN (t3.cle_roto_primaire, t3.cle_roto_secondaire);');
		$req_victoireOuDefaiteCapitaine = $bdd->prepare('SELECT t3.malus_defaite + 2*t3.bonus_victoire AS \'victoireOuDefaite\' FROM joueur_reel t1, joueur_stats t3 WHERE t1.id = :id AND t3.id IN (t1.cle_roto_primaire, t1.cle_roto_secondaire) AND t3.journee = :journee ;');
		//Renvoie 0 si NUL, 1 si Defaite et 2 si Victoire
		$req_nbDefenseur = $bdd->prepare('SELECT nb_def FROM nomenclature_tactique WHERE code = :code_tactique ;');
		
		$upd_noteJoueurCompo = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
		while ($donnees = $req_effectifs->fetch())
		{
			$note = 0; //Note de base pour un joueur absent
			$req_noteDuJoueurJournee->execute(array('id_joueur_reel' => $donnees['id_joueur_reel'], 'journee' => $constanteJourneeReelle));
			$rows = $req_noteDuJoueurJournee->fetchAll();
			
			if (count($rows) == 0) {
				echo  $donnees['cle_roto_primaire'].' n\'a pas joué sur la journée '.$constanteJourneeReelle;
				echo "<br />\n";
			} else {
				foreach ($rows as $row) {
					if ($row['note'] == 0){
						$note = 0;
						echo  $donnees['cle_roto_primaire'].' n\'est pas rentré '.$constanteJourneeReelle;
						echo "<br />\n";
						
					}else{
						$note = $row['note'];
						
						//ajout bonus capitaine
						
						$req_victoireOuDefaiteCapitaine->execute(array('id' => $donnees['id_joueur_reel'], 'journee' => $constanteJourneeReelle));
						$LignesVictoireOuPas = $req_victoireOuDefaiteCapitaine->fetchAll();
						foreach ($LignesVictoireOuPas as $victoireOuPas){
							if($victoireOuPas['victoireOuDefaite'] == 2 && $donnees['capitaine'] == 1){
								//Le joueur est capitaine et son équipe a gagné => BONUS
								$note += 0.5;
								echo ' Capitaine Victoire '; 
							}else{
								if($victoireOuPas['victoireOuDefaite'] == 1 && $donnees['capitaine'] == 1){
									//Le joueur est capitaine et son équipe a perdu => MALUS
									$note -= 1;
									echo ' Capitaine Defaite '; 
								}
							}
						}
						
						//ajout bonus defense
						$req_nbDefenseur->execute(array('code_tactique' => $donnees['code_tactique']));
						$LignesNbDefenseur = $req_nbDefenseur->fetchAll();
						foreach ($LignesNbDefenseur as $nbDefenseur){
							if($nbDefenseur['nb_def'] == 5 && $donnees['position'] == 'Defender' && $donnees['numero'] <= 11){
								//Defense à 5, les défenseurs titulaires prennent un bonus
								$note += 1;
								echo ' Défense à 5 '; 
							}else{
								if($nbDefenseur['nb_def'] == 4 && $donnees['position'] == 'Defender' && $donnees['numero'] <= 11){
									//Defense à 5, les défenseurs titulaires prennent un bonus
									$note += 0.5;
									echo ' Défense à 4 '; 
								}
							}
						}
						
						//ajout bonus/malus (A FAIRE)				
						if($donnees['code_bonus_malus_equipe'] == 'CON_ZZ'){
							$note += 0.5;
						}
						
						
						//Vérification des plafonds
						if($note > 10){
							$note = 10;
						}else{
							if($note < 0.5){
								$note = 0.5;
							}
						}
						
						echo  $donnees['cle_roto_primaire'].' a eu la note de '.$note.' sur la journée '.$constanteJourneeReelle;
						/*  UPDATE DES NOTES DANS LA TABLE */
							$upd_noteJoueurCompo->execute(array('note' => $note, 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
						
						echo "<br />\n";					
						
					}
				}
			}
		}
		$req_effectifs->closeCursor();
		$req_noteDuJoueurJournee->closeCursor();
		$req_victoireOuDefaiteCapitaine->closeCursor();
		$req_nbDefenseur->closeCursor();
		$upd_noteJoueurCompo->closeCursor();		
		
// ******************************* CONSTRUCTION EQUIPE **************************************************************
		
		echo "<br />\n";
		echo ' ************************ CONSTRUCTION EQUIPE - BOUCLE 1 **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		//On récupère tous les joueurs positionnés comme titulaires de la journée
		$req_effectifnote = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t1.id_equipe_dom, t1.id_equipe_ext, t3.id_joueur_reel, t4.cle_roto_primaire, t3.capitaine, t4.position, t3.numero , t3.note, t3.code_bonus_malus AS \'code_bonus_malus_joueur\', t2.code_bonus_malus AS \'code_bonus_malus_equipe\', t3.numero_remplacement, t3.id_joueur_reel_remplacant, t3.note_min_remplacement FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 ;');
		
		//constante 17 pour le test uniquement
		$req_effectifnote->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));
		
		//On récupère la liste des remplaçants
		$req_remplacant = $bdd->prepare('SELECT  t3.id_joueur_reel, t4.cle_roto_primaire,  t4.position, t3.numero, t3.note, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t2.id_equipe = :id_equipe AND t3.numero_definitif IS NULL ORDER By t3.numero ;');
		
		//On remet à Null tous les numéros définitifs d'une compo
		$upd_remiseANullDesNumérosDefinitifs = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = NULL WHERE id_compo = :id_compo ;');
		
		//On remet à Null tous les buts réels d'une compo
		$upd_remiseANullDesButsReels = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = NULL WHERE id_compo = :id_compo ;');
		
		
		//On update le numéro définitif
		$upd_numeroDefinitif = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = :numero_definitif WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
		
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
		
		while ($donnees = $req_effectifnote->fetch())
		{
			if($derniereCompoMAJ != $donnees['id_compo']){
				$derniereCompoMAJ = $donnees['id_compo'];
				$upd_remiseANullDesNumérosDefinitifs->execute(array('id_compo' => $donnees['id_compo']));
				$upd_remiseANullDesButsReels->execute(array('id_compo' => $donnees['id_compo']));
			}
			
			if($donnees['note'] == 0 || is_null($donnees['note'])){
				$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue, 'id_equipe' => $donnees['id_equipe']));
				$estRemplace = 0;
				$lignesRemplacant = $req_remplacant->fetchAll();
				if (count($lignesRemplacant) == 0) { 
					//Aucun remplaçant, le joueur reste dans la compo
					echo 'Aucun remplaçant, le joueur '.$donnees['cle_roto_primaire'].' reste dans la compo';
					echo "<br />\n";
					//On update. Le numéro définitif devient le numéro initialement prévu
					$upd_numeroDefinitif->execute(array('numero_definitif' => $donnees['numero'], 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
				} else {
					foreach ($lignesRemplacant as $ligneRemplacant) {
						if($ligneRemplacant['position'] == $donnees['position'] && $ligneRemplacant['note'] > 0  && $estRemplace == 0){
							//Il existe un remplacement poste pour poste
							$estRemplace = 1;
							echo 'Remplacement de '.$donnees['cle_roto_primaire'].' par le même poste '.$ligneRemplacant['cle_roto_primaire'] ;
							echo "<br />\n";
							//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
							$upd_numeroDefinitif->execute(array('numero_definitif' => $donnees['numero'], 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $ligneRemplacant['id_joueur_reel']));
							
							//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
							updateButReelDuJoueur($donnees['id_compo'], $constanteJourneeReelle, $ligneRemplacant['id_joueur_reel'], $bdd);
							
							//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
							$upd_numeroDefinitif->execute(array('numero_definitif' => 0, 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
						}
					}
					if($donnees['position'] == 'Defender' && $estRemplace == 0){
							//Si il n'existe pas de remplacement poste pour poste pour un défenseur alors le joueur ne peut pas être remplacé
							echo 'Aucun défenseur remplaçant, le joueur '.$donnees['cle_roto_primaire'].' reste dans la compo';
							echo "<br />\n";
							//On update. Le numéro définitif devient le numéro initialement prévu
							$upd_numeroDefinitif->execute(array('numero_definitif' => $donnees['numero'], 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
					}
				}
				$req_remplacant->closeCursor();
			}else{
				if(is_null($donnees['numero_remplacement'])){
					//Le joueur a une note et ne fait l'objet d'aucun remplacement tactique donc il est directement dans l'effectif définitif
					echo $donnees['cle_roto_primaire'].' a joué et n\'est pas remplacé ';
					echo "<br />\n";
					//On update. Le numéro définitif du joueur avec son numéro initial
					$upd_numeroDefinitif->execute(array('numero_definitif' => $donnees['numero'], 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
					
					//On regarde le nombre de but réel marqué par ce joueur sur cette journée
					updateButReelDuJoueur($donnees['id_compo'], $constanteJourneeReelle, $donnees['id_joueur_reel'], $bdd);
				}
			}
		}
		
		$upd_butReel->closeCursor();	
		$upd_numeroDefinitif->closeCursor();
		$req_effectifnote->closeCursor();
		$upd_remiseANullDesNumérosDefinitifs->closeCursor();	
		
		//################## Deuxième boucle ############################
		//Ici on passe en revue tous les joueurs titulaire dans les compos mais absents et n'ayant pas eu de remplacement poste pour poste
		// On vérifie si un joueur de la ligne inférieur a joué parmis les remplaçants encore disponibles
		// On applique une minoration de la note si il y a remplacement
		
		echo "<br />\n";
		echo ' ************************ CONSTRUCTION EQUIPE - BOUCLE 2 **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		$req_effectif_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel, t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NULL ORDER BY id_compo, t3.numero ASC ;');
		
		//constante 17 pour le test uniquement
		$req_effectif_nonRemplace->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));
		
		while ($donneesEffectifNonRemplace = $req_effectif_nonRemplace->fetch())
		{
			$estRemplace = 0;
			$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue, 'id_equipe' => $donneesEffectifNonRemplace['id_equipe']));
			$lignesRemplacant = $req_remplacant->fetchAll();
			if (count($lignesRemplacant) == 0) {
				//Aucun remplaçant, le joueur reste dans la compo
				echo 'Aucun remplaçant, le joueur '.$donneesEffectifNonRemplace['cle_roto_primaire'].' reste dans la compo';
				echo "<br />\n";
				//On update. Le numéro définitif devient le numéro initialement prévu
				$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesEffectifNonRemplace['numero'], 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $donneesEffectifNonRemplace['id_joueur_reel']));
			}else{
				foreach ($lignesRemplacant as $ligneRemplacant) {
					if((($donneesEffectifNonRemplace['position'] == 'Midfielder' && $ligneRemplacant['position'] == 'Defender') || ($donneesEffectifNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Midfielder')) && $ligneRemplacant['note']>0 && $estRemplace == 0 ){
						//Il existe un remplacement par le poste du dessous
						$estRemplace = 1;
						echo 'Remplacement de '.$donneesEffectifNonRemplace['cle_roto_primaire'].' par le poste inférieur '.$ligneRemplacant['cle_roto_primaire'] ;
						echo "<br />\n";
						//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
						$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesEffectifNonRemplace['numero'], 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $ligneRemplacant['id_joueur_reel']));
						
						//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
						updateButReelDuJoueur($donneesEffectifNonRemplace['id_compo'], $constanteJourneeReelle, $ligneRemplacant['id_joueur_reel'], $bdd);
						
						//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
						$upd_numeroDefinitif->execute(array('numero_definitif' => 0, 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $donneesEffectifNonRemplace['id_joueur_reel']));
						
						//On update. La note du joueur remplaçant baisse de 1 car le poste est différent
						if($ligneRemplacant['note']-1<0.5){
							$nouvelleNote = 0.5;
						}else{
							$nouvelleNote = $ligneRemplacant['note']-1;
						}
						$upd_noteJoueurCompo->execute(array('note' => $nouvelleNote, 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $ligneRemplacant['id_joueur_reel']));
						echo 'Note - 1 ';
						echo "<br />\n";
						$upd_noteJoueurCompo->closeCursor();
						
					}
				}
				if($donneesEffectifNonRemplace['position'] == 'Midfielder' && $estRemplace == 0){
					//Si il n'existe pas de remplacement d'un défenseur pour un milieu alors le joueur ne peut pas être remplacé
					echo 'Aucun défenseur pour remplacer le milieu '.$donneesEffectifNonRemplace['cle_roto_primaire'].' reste dans la compo';
					echo "<br />\n";
					//On update. Le numéro définitif devient le numéro initialement prévu
					$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesEffectifNonRemplace['numero'], 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $donneesEffectifNonRemplace['id_joueur_reel']));
				}
			}
			$req_remplacant->closeCursor();
		}
		
		$req_effectif_nonRemplace->closeCursor();
		$upd_numeroDefinitif->closeCursor();
			
		//################## Troisième boucle ############################
		//Ici on passe en revue tous les attaquants titulaires dans les compos mais absents et n'ayant pas eu de remplacement par un attaquant ou un milieu
		// On vérifie si un défenseur a joué parmis les remplaçants encore disponibles
		// On applique une minoration de la note si il y a remplacement
		
		echo "<br />\n";
		echo ' ************************ CONSTRUCTION EQUIPE - BOUCLE 3 **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		$req_attaquant_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel, t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NULL AND t4.position = \'Forward\' ORDER BY id_compo, t3.numero ASC ;');
		
		//constante 17 pour le test uniquement
		$req_attaquant_nonRemplace->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));
		
		while ($donneesAttaquantNonRemplace = $req_attaquant_nonRemplace->fetch())
		{
			$estRemplace = 0;
			$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue, 'id_equipe' => $donneesAttaquantNonRemplace['id_equipe']));
			$lignesRemplacant = $req_remplacant->fetchAll();
			if (count($lignesRemplacant) == 0) {
				//Aucun remplaçant, le joueur reste dans la compo
				echo 'Aucun remplaçant, le joueur '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' reste dans la compo';
				echo "<br />\n";
				//On update. Le numéro définitif devient le numéro initialement prévu
				$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesAttaquantNonRemplace['numero'], 'id_compo' => $donneesAttaquantNonRemplace['id_compo'], 'id_joueur_reel' => $donneesAttaquantNonRemplace['id_joueur_reel']));
			}else{
				foreach ($lignesRemplacant as $ligneRemplacant) {
					if($donneesAttaquantNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Defender'&& $ligneRemplacant['note']>0 && $estRemplace == 0 ){
						//Il existe un remplacement par le poste du dessous
						$estRemplace = 1;
						echo 'Remplacement de l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' par un défenseur '.$ligneRemplacant['cle_roto_primaire'] ;
						echo "<br />\n";
						//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
						$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesAttaquantNonRemplace['numero'], 'id_compo' => $donneesAttaquantNonRemplace['id_compo'], 'id_joueur_reel' => $ligneRemplacant['id_joueur_reel']));
						
						//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
						updateButReelDuJoueur($donneesAttaquantNonRemplace['id_compo'], $constanteJourneeReelle, $ligneRemplacant['id_joueur_reel'], $bdd);
						
						//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
						$upd_numeroDefinitif->execute(array('numero_definitif' => 0, 'id_compo' => $donneesAttaquantNonRemplace['id_compo'], 'id_joueur_reel' => $donneesAttaquantNonRemplace['id_joueur_reel']));
						
						//On update. La note du joueur remplaçant baisse de 2 car le poste est très différent
						//On update. La note du joueur remplaçant baisse de 1 car le poste est différent
						if($ligneRemplacant['note']-2<0.5){
							$nouvelleNote = 0.5;
						}else{
							$nouvelleNote = $ligneRemplacant['note']-2;
						}
						$upd_noteJoueurCompo->execute(array('note' => $nouvelleNote, 'id_compo' => $donneesAttaquantNonRemplace['id_compo'], 'id_joueur_reel' => $ligneRemplacant['id_joueur_reel']));
						echo 'Note - 2 ';
						echo "<br />\n";
						$upd_noteJoueurCompo->closeCursor();
					}
				}
				if($donneesAttaquantNonRemplace['position'] == 'Forward' && $estRemplace == 0){
					//Si il n'existe pas de remplacement d'un défenseur pour un attaquant alors le joueur ne peut pas être remplacé
					echo 'Aucun défenseur pour remplacer l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' reste dans la compo';
					echo "<br />\n";
					//On update. Le numéro définitif devient le numéro initialement prévu
					$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesAttaquantNonRemplace['numero'], 'id_compo' => $donneesAttaquantNonRemplace['id_compo'], 'id_joueur_reel' => $donneesAttaquantNonRemplace['id_joueur_reel']));
				}
			}
			$req_remplacant->closeCursor();
		}
		
		$req_attaquant_nonRemplace->closeCursor();
		$upd_numeroDefinitif->closeCursor();
		
		
		
		
		//################## Quatrième boucle ############################
		//Ici on passe en revue tous les joueurs ayant un remplacement tactique programmé
		// On vérifie que le remplaçant n'est pas déjà entré dans l'effectif
		// On vérifie si le remplacement tactique s'applique
		// On update le remplacement
		
		echo "<br />\n";
		echo ' ************************ CONSTRUCTION EQUIPE - BOUCLE 4 **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		//Requete qui renvoie tous les joueurs titulaires, avec une note inférieur à la note minimum du remplacement tactique et un remplaçant ayant joué encore sur le banc
		$req_joueurAvecRemplacementTactiqueActif= $bdd->prepare('SELECT DISTINCT t3.id_joueur_reel, t3.id_compo, t4.cle_roto_primaire, t3.numero, t3.id_joueur_reel_remplacant FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL AND t3.note < t3.note_min_remplacement AND t3.id_joueur_reel_remplacant IN (SELECT t3.id_joueur_reel FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL) ;');
		
		//constante 17 pour le test uniquement
		$req_joueurAvecRemplacementTactiqueActif->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));
		
		while ($donneesRemplacementTactique = $req_joueurAvecRemplacementTactiqueActif->fetch())
		{
			//Il existe un remplacement tactique
			echo 'Remplacement Tactique de '.$donneesRemplacementTactique['cle_roto_primaire'].' par le joueur avec l\'id : '.$donneesRemplacementTactique['id_joueur_reel_remplacant'] ;
			echo "<br />\n";
			//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
			$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesRemplacementTactique['numero'], 'id_compo' => $donneesRemplacementTactique['id_compo'], 'id_joueur_reel' => $donneesRemplacementTactique['id_joueur_reel_remplacant']));
			
			//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
			updateButReelDuJoueur($donneesRemplacementTactique['id_compo'], $constanteJourneeReelle, $donneesRemplacementTactique['id_joueur_reel_remplacant'], $bdd);
			
			//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
			$upd_numeroDefinitif->execute(array('numero_definitif' => 0, 'id_compo' => $donneesRemplacementTactique['id_compo'], 'id_joueur_reel' => $donneesRemplacementTactique['id_joueur_reel']));
			$upd_numeroDefinitif->closeCursor();
		}
		
		$req_joueurAvecRemplacementTactiqueActif->closeCursor();
		
		//################## Cinquième boucle ############################
		// Ici on passe en revue tous les joueurs titulaires pour qui le remplacement tactique ne s'est pas appliqué
		// On update les numéros définitifs
		// L'équipe doit être complète
		
		echo "<br />\n";
		echo ' ************************ CONSTRUCTION EQUIPE - BOUCLE 5 **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		//Requete qui renvoie tous les joueurs titulaires, ayant joué mais n'ayant pas encore de numéro définitif
		$req_joueursRestants= $bdd->prepare('SELECT t3.id_compo, t3.id_joueur_reel, t4.cle_roto_primaire, t3.note, t3.numero, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL ;');
		
		//constante 17 pour le test uniquement
		$req_joueursRestants->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $constanteConfrontationLigue));
		
		while ($donneesJoueursRestants = $req_joueursRestants->fetch())
		{
			//le remplacement tactique n'était pas possible 
			echo 'Le remplacement tactique de '.$donneesJoueursRestants['cle_roto_primaire'].' n\'était pas possible ';
			echo "<br />\n";
			//On update. Le numéro définitif du joueur
			$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesJoueursRestants['numero'], 'id_compo' => $donneesJoueursRestants['id_compo'], 'id_joueur_reel' => $donneesJoueursRestants['id_joueur_reel']));
			
			$upd_numeroDefinitif->closeCursor();
			
			//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
			updateButReelDuJoueur($donneesJoueursRestants['id_compo'], $constanteJourneeReelle, $donneesJoueursRestants['id_joueur_reel'], $bdd);
		}
		
		$req_joueursRestants->closeCursor();

		//################## Sixième boucle ############################
		// Ici on applique les malus infligés par l'équipe adverse et affectant les notes des joueurs
		
		echo "<br />\n";
		echo ' ************************ APPLICATION DES MALUS ADVERSAIRE - BOUCLE 6 **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		//Requete qui renvoie la liste des équipes ayant joué sur la journée ainsi que les malus appliquées
		$req_malus_bonus= $bdd->prepare('SELECT t1.id_equipe, t2.id_equipe_dom, t2.id_equipe_ext, t1.code_bonus_malus, t1.id FROM compo_equipe t1, calendrier_ligue t2 WHERE t2.num_journee_cal_reel = :num_journee_cal_reel AND t2.id = t1.id_cal_ligue ;');
		
		//MALUS FUMIGENE Requete note gardien d'une équipe
		$req_note_gardien = $bdd->prepare('SELECT t2.note, t2.id_compo FROM compo_equipe t1, joueur_compo_equipe t2, calendrier_ligue t3 WHERE t1.id_equipe = :id_equipe AND t2.id_compo = t1.id AND t2.numero_definitif = 1 AND t3.id = t1.id_cal_ligue AND t1.id = t2.id_compo AND t3.num_journee_cal_reel = :num_journee_cal_reel ;');
		
		//MALUS FUMIGENE Update note gardien
		$upd_note_gardien = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND numero_definitif = 1 ;');

		//constante 17 pour le test uniquement
		$req_malus_bonus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
		
		while ($donneesMalusBonus = $req_malus_bonus->fetch())
		{
			if(is_null($donneesMalusBonus['code_bonus_malus'])){
					//Pas de malus/bonus pour cette équipe
					echo 'L\'équipe '.$donneesMalusBonus['id_equipe'].' n\'a pas mis de bonus/malus ';
					echo "<br />\n";
			}else{
				if($donneesMalusBonus['code_bonus_malus'] == 'FUMIGENE'){
					if($donneesMalusBonus['id_equipe'] == $donneesMalusBonus['id_equipe_dom']){
						//Léquipe qui a mis ce malus/bonus est l'équipe domicile
						echo 'L\'équipe '.$donneesMalusBonus['id_equipe'].' a mis un Fumigène à l\'équipe '.$donneesMalusBonus['id_equipe_ext'];
						echo "<br />\n";
						//constante 17 pour le test uniquement
						$req_note_gardien->execute(array('id_equipe' => $donneesMalusBonus['id_equipe_ext'],'num_journee_cal_reel' => $constante_num_journee_cal_reel));
						$ligneNoteGardienAdverse = $req_note_gardien->fetchAll();
						if(count($ligneNoteGardienAdverse) == 1) {
							//Pas d'erreur on a qu'un seul retour
							foreach ($ligneNoteGardienAdverse as $noteGardienAdverse) {
								if($noteGardienAdverse['note'] <= 0.5 || is_null($noteGardienAdverse['note'])){
									//Fumigene non appliqué car pas de gardien ou gardien a déjà la note minimum
									echo 'Impossible d\'appliquer le fumigene sur le gardien adverse (note minimum ou tontonpat)';
									echo "<br />\n";
								}else{
									$noteUpdateGardien = $noteGardienAdverse['note'] - 1;
									if($noteGardienAdverse['note'] == 1){
										$noteUpdateGardien = 0.5 ;
									}
									$upd_note_gardien->execute(array('note' => $noteUpdateGardien, 'id_compo' => $noteGardienAdverse['id_compo']));
									echo 'FUMIGENE: Malus de -1 sur le gardien de la compo '.$noteGardienAdverse['id_compo'];
									echo "<br />\n";
									$upd_note_gardien->closeCursor();
								}
							}
						}
						$req_note_gardien->closeCursor();	
					}else{
						//Léquipe qui a mis ce malus/bonus est l'équipe visiteur
						echo 'L\'équipe '.$donneesMalusBonus['id_equipe'].' a mis un Fumigène à l\'équipe '.$donneesMalusBonus['id_equipe_dom'];
						echo "<br />\n";
						//constante 17 pour le test uniquement
						$req_note_gardien->execute(array('id_equipe' => $donneesMalusBonus['id_equipe_dom'],'num_journee_cal_reel' => $constante_num_journee_cal_reel));
						$ligneNoteGardienAdverse = $req_note_gardien->fetchAll();
						if(count($ligneNoteGardienAdverse) == 1) {
							//Pas d'erreur on a qu'un seul retour
							foreach ($ligneNoteGardienAdverse as $noteGardienAdverse) {
								if($noteGardienAdverse['note'] <= 0.5 || is_null($noteGardienAdverse['note'])){
									//Fumigene non appliqué car pas de gardien ou gardien a déjà la note minimum
									echo 'Impossible d\'appliquer le fumigene sur le gardien adverse (note minimum ou tontonpat)';
									echo "<br />\n";
								}else{
									$noteUpdateGardien = $noteGardienAdverse['note'] - 1;
									if($noteGardienAdverse['note'] == 1){
										$noteUpdateGardien = 0.5 ;
									}
									$upd_note_gardien->execute(array('note' => $noteUpdateGardien, 'id_compo' => $noteGardienAdverse['id_compo']));
									echo 'FUMIGENE: Malus de -1 sur le gardien de la compo '.$noteGardienAdverse['id_compo'];
									echo "<br />\n";
									$upd_note_gardien->closeCursor();
								}
							}
						}
						$req_note_gardien->closeCursor();
					}
				}
			
			}
		}
		$req_malus_bonus->closeCursor();
		
		
		

		//################## Septième boucle ############################
		// Ici on passe en revue tous les joueurs titulaires pour qui le remplacement tactique ne s'est pas appliqué
		// On update les numéros définitifs
		// L'équipe doit être complète
		
		echo "<br />\n";
		echo ' ************************ CALCUL BUT VIRTUEL EQUIPE - BOUCLE 7 **************************';
		echo "<br />\n";
		echo "<br />\n";
		
		
			// affichage compoDefinitive
			$req_listeConfrontationParJournee = $bdd->prepare('SELECT id_cal_ligue, t2.id FROM calendrier_ligue t1, compo_equipe t2 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t1.id_equipe_dom = t2.id_equipe UNION SELECT id_cal_ligue, t2.id FROM calendrier_ligue t1, compo_equipe t2 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t1.id_equipe_ext = t2.id_equipe ORDER BY id_cal_ligue ;');
			
			//constante 17 pour le test uniquement
			$req_listeConfrontationParJournee->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
			$i=0;
			while ($listeConfrontationParJournee = $req_listeConfrontationParJournee->fetch())
			{
				afficherUneCompo($listeConfrontationParJournee['id'], $bdd);
				if($i==0){
					echo "<br />\n";
					echo ' ------ VERSUS ----------';
					echo "<br />\n";
					$i++;
				}else{
					$i=0;
				}
			}	
		
		
		
		
		//Application des malus équipe de l'adversaire (MAJ Note)
		
		
?>