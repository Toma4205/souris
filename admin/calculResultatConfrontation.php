<html>
<body>
<?php
	include('./fonctions_admin.php');
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

	function updateButReelDuJoueur($id_compo, $journee, $id_joueur_reel, $bdd){
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

	function afficherUneCompo($id_compo, $bdd){
		$req_compoDefinitive = $bdd->prepare('SELECT t2.id, t2.cle_roto_primaire, t2.position, t1.numero_definitif , t1.note, t1.nb_but_reel, t1.nb_but_virtuel, t4.nb_def, t4.nb_mil, t4.nb_att FROM joueur_compo_equipe t1, joueur_reel t2, compo_equipe t3, nomenclature_tactique t4 WHERE t1.id_compo = :id_compo AND t1.id_joueur_reel = t2.id AND t1.numero_definitif > 0 AND t1.numero_definitif < 12 AND t1.numero_definitif IS NOT NULL AND t3.id = t1.id_compo AND t3.code_tactique = t4.code ORDER BY t1.numero_definitif ASC;');

		$req_compoDefinitive->execute(array('id_compo' => $id_compo));
		addLogEvent( '########################################################');
		$premiereBoucle = 0;
		$gardien = 'P\'tit jeune du club (1)';
		$defenseurs=' ';
		$milieux=' ';
		$attaquants=' ';
		while ($compoDefinitive = $req_compoDefinitive->fetch())
		{

			if($compoDefinitive['numero_definitif'] == 1){
				if(!is_null($compoDefinitive['note'])){
					$gardien = '['.$compoDefinitive['id'].']'.$compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].')';
				}
			}else{
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['note'])){
						$defenseurs .= ' Tonton Pat (0) ';
					}else{
						$defenseurs .= ' '.'['.$compoDefinitive['id'].']'.$compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].') ';
						if(!is_null($compoDefinitive['nb_but_reel'])){
							$defenseurs .= '- '.$compoDefinitive['nb_but_reel'].' but(s) ';
						}
						if(!is_null($compoDefinitive['nb_but_virtuel'])){
							$defenseurs .= '- '.$compoDefinitive['nb_but_virtuel'].' butVirtuel ';
						}
					}
				}else{
					if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['note'])){
							$milieux .= ' Tonton Pat (0) ';
						}else{
							$milieux .= ' '.'['.$compoDefinitive['id'].']'.$compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].') ';
							if(!is_null($compoDefinitive['nb_but_reel'])){
								$milieux .= '- '.$compoDefinitive['nb_but_reel'].' but(s) ';
							}
							if(!is_null($compoDefinitive['nb_but_virtuel'])){
								$milieux .= '- '.$compoDefinitive['nb_but_virtuel'].' butVirtuel ';
							}
						}
					}else{
						if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
							if(is_null($compoDefinitive['note'])){
								$attaquants .= ' Tonton Pat (0) ';
							}else{
								$attaquants .= ' '.'['.$compoDefinitive['id'].']'.$compoDefinitive['cle_roto_primaire'].' ('.$compoDefinitive['note'].') ';
								if(!is_null($compoDefinitive['nb_but_reel'])){
									$attaquants .= '- '.$compoDefinitive['nb_but_reel'].' but(s) ';
								}
								if(!is_null($compoDefinitive['nb_but_virtuel'])){
									$attaquants .= '- '.$compoDefinitive['nb_but_virtuel'].' butVirtuel ';
								}
							}
						}
					}
				}
			}
		}

		addLogEvent( $gardien);
		addLogEvent( $defenseurs);
		addLogEvent( $milieux);
		addLogEvent( $attaquants);
		addLogEvent( '########################################################');

		$req_compoDefinitive->closeCursor();
	}

	function impactCSC($journee, $short_journee, $bdd){
		//Attention id_journee vs num_journee_cal_reel
		$upd_csc = $bdd->prepare('UPDATE compo_equipe ce, joueur_compo_equipe jce, joueur_stats js, joueur_reel jr, calendrier_ligue cl SET jce.nb_csc = js.csc WHERE jce.id_compo = ce.id  AND jr.id = jce.id_joueur_reel AND js.id IN (jr.cle_roto_primaire, jr.cle_roto_secondaire) AND ce.id_cal_ligue = cl.id AND js.journee = :journee AND cl.num_journee_cal_reel = :short_journee AND js.csc > 0 AND jce.numero_definitif > 0 AND jce.numero_definitif < 12;');
		$upd_csc->execute(array('journee' => $journee, 'short_journee' => $short_journee));
	}

	function calculButVirtuel($equipeA, $equipeB, $bdd){
		$req_compoDefinitive = $bdd->prepare('SELECT t1.id_joueur_reel, t2.cle_roto_primaire, t2.position, t1.numero_definitif , t1.note, t1.nb_but_reel, t4.nb_def, t4.nb_mil, t4.nb_att
			FROM joueur_compo_equipe t1, joueur_reel t2, compo_equipe t3, nomenclature_tactique t4
			WHERE t1.id_compo = :id_compo AND t1.id_joueur_reel = t2.id AND t1.numero_definitif > 0 AND t1.numero_definitif < 12 AND t1.numero_definitif IS NOT NULL AND t3.id = t1.id_compo AND t3.code_tactique = t4.code ORDER BY t1.numero_definitif ASC;');

		$upd_butVirtuel = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_virtuel = :nb_but_virtuel WHERE joueur_compo_equipe.id_compo = :id_compo AND joueur_compo_equipe.id_joueur_reel = :id_joueur_reel;');

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


		$req_compoDefinitive->execute(array('id_compo' => $equipeA));
		//Boucle CALCUL MOYENNE ET TONTON PAT sur la compo domicile
		while ($compoDefinitive = $req_compoDefinitive->fetch())
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
		$req_compoDefinitive->closeCursor();

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

		$req_compoDefinitive->execute(array('id_compo' => $equipeB));
		//Boucle CALCUL MOYENNE ET TONTON PAT sur la compo extérieure
		while ($compoDefinitive = $req_compoDefinitive->fetch())
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
							$upd_butVirtuel->execute(array('nb_but_virtuel' => '1', 'id_compo' => $equipeB, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
							$upd_butVirtuel->closeCursor();
						}else{
							$upd_butVirtuel->execute(array('nb_but_virtuel' => NULL, 'id_compo' => $equipeB, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
							$upd_butVirtuel->closeCursor();
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
								$upd_butVirtuel->execute(array('nb_but_virtuel' => '1', 'id_compo' => $equipeB, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
								$upd_butVirtuel->closeCursor();
							}else{
								$upd_butVirtuel->execute(array('nb_but_virtuel' => NULL, 'id_compo' => $equipeB, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
								$upd_butVirtuel->closeCursor();
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
									$upd_butVirtuel->execute(array('nb_but_virtuel' => '1', 'id_compo' => $equipeB, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
									$upd_butVirtuel->closeCursor();
								}else{
									$upd_butVirtuel->execute(array('nb_but_virtuel' => NULL, 'id_compo' => $equipeB, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
									$upd_butVirtuel->closeCursor();
								}
							}
						}
					}
				}
			}
		}
		$req_compoDefinitive->closeCursor();

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


		$req_compoDefinitive->execute(array('id_compo' => $equipeA));
		//BOUCLE DE CALCUL DES BUTS VIRTUELS
		while ($compoDefinitive = $req_compoDefinitive->fetch())
		{
			if($compoDefinitive['numero_definitif'] == 1){

			}else{
				if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_def']+1){
					if(is_null($compoDefinitive['note'])){

					}else{

						if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyAttaqueB) && ($compoDefinitive['note']-1 > $moyMilieuB) && ($compoDefinitive['note']-1.5 > $moyDefenseB) && ($compoDefinitive['note']-2 > $moyGardienB)){
							//butVirtuel
							addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
							$upd_butVirtuel->execute(array('nb_but_virtuel' => '1', 'id_compo' => $equipeA, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
							$upd_butVirtuel->closeCursor();
						}else{
							$upd_butVirtuel->execute(array('nb_but_virtuel' => NULL, 'id_compo' => $equipeA, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
							$upd_butVirtuel->closeCursor();
						}
					}
				}else{
					if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
						if(is_null($compoDefinitive['note'])){

						}else{
							if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyMilieuB) && ($compoDefinitive['note']-1 > $moyDefenseB) && ($compoDefinitive['note']-1.5 > $moyGardienB)){
								//butVirtuel
								addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
								$upd_butVirtuel->execute(array('nb_but_virtuel' => '1', 'id_compo' => $equipeA, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
								$upd_butVirtuel->closeCursor();
							}else{
								$upd_butVirtuel->execute(array('nb_but_virtuel' => NULL, 'id_compo' => $equipeA, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
								$upd_butVirtuel->closeCursor();
							}
						}
					}else{
						if($compoDefinitive['numero_definitif'] <= $compoDefinitive['nb_att']+$compoDefinitive['nb_mil']+$compoDefinitive['nb_def']+1){
							if(is_null($compoDefinitive['note'])){

							}else{

								if(is_null($compoDefinitive['nb_but_reel']) && ($compoDefinitive['note'] > $moyDefenseB) && ($compoDefinitive['note']-1 > $moyGardienB)){
									//butVirtuel
									addLogEvent( 'But Virtuel de '.$compoDefinitive['cle_roto_primaire']);
								$upd_butVirtuel->execute(array('nb_but_virtuel' => '1', 'id_compo' => $equipeA, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
								$upd_butVirtuel->closeCursor();
								}else{
									$upd_butVirtuel->execute(array('nb_but_virtuel' => NULL, 'id_compo' => $equipeA, 'id_joueur_reel' => $compoDefinitive['id_joueur_reel']));
									$upd_butVirtuel->closeCursor();
								}
							}
						}
					}
				}
			}
		}
		$req_compoDefinitive->closeCursor();
	}

	function calculerNotes($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_noteJoueurCompo, $upd_noteBonus)
	{
		$req_effectifs = $bdd->prepare('SELECT t1.id, t3.id_compo, t2.id_equipe, t1.id_equipe_dom, t1.id_equipe_ext, t3.id_joueur_reel, t4.cle_roto_primaire, t3.capitaine, t4.position, t3.numero , t2.code_tactique, t2.code_bonus_malus AS \'code_bonus_malus_equipe\', t3.numero_remplacement, t3.id_joueur_reel_remplacant, t3.note_min_remplacement
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
			WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel;');

		//constante 17 pour le test uniquement
		$req_effectifs->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue));
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

		$req_noteDuJoueurJournee = $bdd->prepare('SELECT t1.note, t2.id_compo FROM joueur_stats t1, joueur_compo_equipe t2, joueur_reel t3 WHERE t2.id_joueur_reel = :id_joueur_reel AND t1.journee = :journee AND t2.id_joueur_reel = t3.id AND t1.id IN (t3.cle_roto_primaire, t3.cle_roto_secondaire) AND t2.id_compo = :id_compo;');
		$req_victoireOuDefaiteCapitaine = $bdd->prepare('SELECT t3.malus_defaite + 2*t3.bonus_victoire AS \'victoireOuDefaite\' FROM joueur_reel t1, joueur_stats t3 WHERE t1.id = :id AND t3.id IN (t1.cle_roto_primaire, t1.cle_roto_secondaire) AND t3.journee = :journee ;');
		//Renvoie 0 si NUL, 1 si Defaite et 2 si Victoire
		$req_nbDefenseur = $bdd->prepare('SELECT nb_def FROM nomenclature_tactique WHERE code = :code_tactique ;');

		while ($donnees = $req_effectifs->fetch())
		{
				$note = 0; //Note de base pour un joueur absent
				$note_bonus = 0; //Bonus de base
				$req_noteDuJoueurJournee->execute(array('id_joueur_reel' => $donnees['id_joueur_reel'], 'journee' => $constanteJourneeReelle, 'id_compo' => $donnees['id_compo']));
				$rows = $req_noteDuJoueurJournee->fetchAll();

				if (count($rows) == 0) {
					addLogEvent(  $donnees['cle_roto_primaire'].' n\'a pas joué sur la journée '.$constanteJourneeReelle);
				} else {
					foreach ($rows as $row)
					{
						if ($row['note'] == 0){
							$note = 0;
							addLogEvent(  $donnees['cle_roto_primaire'].' n\'est pas rentré '.$constanteJourneeReelle);

						}else{
							$note = $row['note'];

							//test ajout bonus capitaine

							$req_victoireOuDefaiteCapitaine->execute(array('id' => $donnees['id_joueur_reel'], 'journee' => $constanteJourneeReelle));
							$LignesVictoireOuPas = $req_victoireOuDefaiteCapitaine->fetchAll();
							foreach ($LignesVictoireOuPas as $victoireOuPas)
							{
								if($victoireOuPas['victoireOuDefaite'] == 2 && $donnees['capitaine'] == 1){
									//Le joueur est capitaine et son équipe a gagné => BONUS
									$note += 0.5;
									$note_bonus = 0.5;
									addLogEvent( 'Capitaine Victoire ');
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
							$req_nbDefenseur->execute(array('code_tactique' => $donnees['code_tactique']));
							$LignesNbDefenseur = $req_nbDefenseur->fetchAll();
							foreach ($LignesNbDefenseur as $nbDefenseur)
							{
								if($nbDefenseur['nb_def'] == 5 && $donnees['position'] == 'Defender' && $donnees['numero'] <= 11){
									//Defense à 5, les défenseurs titulaires prennent un bonus
									$note += 1;
									$note_bonus += 1;
									addLogEvent( ' Défense à 5 ');
								}else{
									if($nbDefenseur['nb_def'] == 4 && $donnees['position'] == 'Defender' && $donnees['numero'] <= 11){
										//Defense à 5, les défenseurs titulaires prennent un bonus
										$note += 0.5;
										$note_bonus += 0.5;
										addLogEvent( ' Défense à 4 ');
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

							addLogEvent(  $donnees['cle_roto_primaire'].' a eu la note de '.$note.' sur la journée '.$constanteJourneeReelle);
							/*  UPDATE DES NOTES DANS LA TABLE */
							$upd_noteJoueurCompo->execute(array('note' => $note, 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
							addLogEvent( 'MAJ note bonus '.$note_bonus.' ' );
							$upd_noteBonus->execute(array('note' => $note_bonus, 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
						}
					}
				}
			}

			$req_effectifs->closeCursor();
			$req_noteDuJoueurJournee->closeCursor();
			$req_victoireOuDefaiteCapitaine->closeCursor();
			$req_nbDefenseur->closeCursor();
	}

	function construireEquipeBoucle1($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif, $req_remplacant)
	{
		//On récupère tous les joueurs positionnés comme titulaires de la journée
		$req_effectifnote = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t1.id_equipe_dom, t1.id_equipe_ext, t3.id_joueur_reel, t4.cle_roto_primaire, t3.capitaine, t4.position, t3.numero , t3.note, t3.note_bonus, t2.code_bonus_malus AS \'code_bonus_malus_equipe\', t3.numero_remplacement, t3.id_joueur_reel_remplacant, t3.note_min_remplacement
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 ;');
		$req_effectifnote->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue));

		//On remet à Null tous les numéros définitifs d'une compo
		$upd_remiseANullDesNumérosDefinitifs = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = NULL WHERE id_compo = :id_compo ;');

		//On remet à Null tous les buts réels d'une compo
		$upd_remiseANullDesButsReels = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = NULL WHERE id_compo = :id_compo ;');

		//On compte le nombre de but réel d'un joueur sur une journée
		$req_nbButReel=$bdd->prepare('SELECT t3.but FROM joueur_compo_equipe t1, joueur_reel t2, joueur_stats t3 WHERE t1.id_joueur_reel = t2.id AND t3.id IN (t2.cle_roto_primaire, t2.cle_roto_secondaire) AND t3.journee = :journee AND t1.id_joueur_reel = :id_joueur_reel');

		//On update un but réel
		$upd_butReel= $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = :nb_but_reel WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');

		$derniereCompoMAJ = 0;
		while ($donnees = $req_effectifnote->fetch())
		{
			if($derniereCompoMAJ != $donnees['id_compo']){
				$derniereCompoMAJ = $donnees['id_compo'];
				$upd_remiseANullDesNumérosDefinitifs->execute(array('id_compo' => $donnees['id_compo']));
				$upd_remiseANullDesButsReels->execute(array('id_compo' => $donnees['id_compo']));
			}

			if($donnees['note'] == 0 || is_null($donnees['note'])){
				$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue, 'id_equipe' => $donnees['id_equipe']));
				$estRemplace = 0;
				$lignesRemplacant = $req_remplacant->fetchAll();
				if (count($lignesRemplacant) == 0) {
					//Aucun remplaçant, le joueur reste dans la compo
					addLogEvent( 'Aucun remplaçant, le joueur '.$donnees['cle_roto_primaire'].' reste dans la compo');
					//On update. Le numéro définitif devient le numéro initialement prévu
					$upd_numeroDefinitif->execute(array('numero_definitif' => $donnees['numero'], 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
				} else {
					foreach ($lignesRemplacant as $ligneRemplacant) {
						if($ligneRemplacant['position'] == $donnees['position'] && $ligneRemplacant['note'] > 0  && $estRemplace == 0){
							//Il existe un remplacement poste pour poste
							$estRemplace = 1;
							addLogEvent('Remplacement de '.$donnees['cle_roto_primaire'].' par le même poste '.$ligneRemplacant['cle_roto_primaire']);
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
						addLogEvent( 'Aucun défenseur remplaçant, le joueur '.$donnees['cle_roto_primaire'].' reste dans la compo');
						//On update. Le numéro définitif devient le numéro initialement prévu
						$upd_numeroDefinitif->execute(array('numero_definitif' => $donnees['numero'], 'id_compo' => $donnees['id_compo'], 'id_joueur_reel' => $donnees['id_joueur_reel']));
					}
				}
				$req_remplacant->closeCursor();
			}else{
				if(is_null($donnees['numero_remplacement'])){
					//Le joueur a une note et ne fait l'objet d'aucun remplacement tactique donc il est directement dans l'effectif définitif
					addLogEvent( $donnees['cle_roto_primaire'].' a joué et n\'est pas remplacé ');
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
	}

	function construireEquipeBoucle2($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif,
		$req_remplacant, $upd_noteJoueurCompo, $upd_noteBonus)
	{
		$req_effectif_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel, t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.numero_definitif
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NULL ORDER BY id_compo, t3.numero ASC ;');

		//constante 17 pour le test uniquement
		$req_effectif_nonRemplace->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue));

		while ($donneesEffectifNonRemplace = $req_effectif_nonRemplace->fetch())
		{
			$estRemplace = 0;
			$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue, 'id_equipe' => $donneesEffectifNonRemplace['id_equipe']));
			$lignesRemplacant = $req_remplacant->fetchAll();
			if (count($lignesRemplacant) == 0) {
				//Aucun remplaçant, le joueur reste dans la compo
				addLogEvent( 'Aucun remplaçant, le joueur '.$donneesEffectifNonRemplace['cle_roto_primaire'].' reste dans la compo');
				//On update. Le numéro définitif devient le numéro initialement prévu
				$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesEffectifNonRemplace['numero'], 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $donneesEffectifNonRemplace['id_joueur_reel']));
			}else{
				foreach ($lignesRemplacant as $ligneRemplacant) {
					if((($donneesEffectifNonRemplace['position'] == 'Midfielder' && $ligneRemplacant['position'] == 'Defender') || ($donneesEffectifNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Midfielder')) && $ligneRemplacant['note']>0 && $estRemplace == 0 ){
						//Il existe un remplacement par le poste du dessous
						$estRemplace = 1;
						addLogEvent( 'Remplacement de '.$donneesEffectifNonRemplace['cle_roto_primaire'].' par le poste inférieur '.$ligneRemplacant['cle_roto_primaire'] );
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
						$upd_noteBonus-> execute(array('note' => $ligneRemplacant['note_bonus']-1, 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $ligneRemplacant['id_joueur_reel']));

						addLogEvent( 'Note - 1 ');
						$upd_noteJoueurCompo->closeCursor();
						$upd_noteBonus->closeCursor();
					}
				}
				if($donneesEffectifNonRemplace['position'] == 'Midfielder' && $estRemplace == 0){
					//Si il n'existe pas de remplacement d'un défenseur pour un milieu alors le joueur ne peut pas être remplacé
					addLogEvent( 'Aucun défenseur pour remplacer le milieu '.$donneesEffectifNonRemplace['cle_roto_primaire'].' reste dans la compo');
					//On update. Le numéro définitif devient le numéro initialement prévu
					$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesEffectifNonRemplace['numero'], 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $donneesEffectifNonRemplace['id_joueur_reel']));
				}
			}
			$req_remplacant->closeCursor();
		}

		$req_effectif_nonRemplace->closeCursor();
		$upd_numeroDefinitif->closeCursor();
	}

	function construireEquipeBoucle3($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif,
		$req_remplacant, $upd_noteJoueurCompo, $upd_noteBonus)
	{
		$req_attaquant_nonRemplace = $bdd->prepare('SELECT t3.id_compo, t2.id_equipe, t3.id_joueur_reel, t4.cle_roto_primaire, t4.position, t3.numero, t3.note, t3.note_bonus, t3.numero_definitif
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
			WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NULL AND t4.position = \'Forward\' ORDER BY id_compo, t3.numero ASC ;');

		$req_attaquant_nonRemplace->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue));

		while ($donneesAttaquantNonRemplace = $req_attaquant_nonRemplace->fetch())
		{
			$estRemplace = 0;
			$req_remplacant->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue, 'id_equipe' => $donneesAttaquantNonRemplace['id_equipe']));
			$lignesRemplacant = $req_remplacant->fetchAll();
			if (count($lignesRemplacant) == 0) {
				//Aucun remplaçant, le joueur reste dans la compo
				addLogEvent( 'Aucun remplaçant, le joueur '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' reste dans la compo');
				//On update. Le numéro définitif devient le numéro initialement prévu
				$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesAttaquantNonRemplace['numero'], 'id_compo' => $donneesAttaquantNonRemplace['id_compo'], 'id_joueur_reel' => $donneesAttaquantNonRemplace['id_joueur_reel']));
			}else{
				foreach ($lignesRemplacant as $ligneRemplacant)
				{
					if($donneesAttaquantNonRemplace['position'] == 'Forward' && $ligneRemplacant['position'] == 'Defender'&& $ligneRemplacant['note']>0 && $estRemplace == 0 ){
						//Il existe un remplacement par le poste du dessous
						$estRemplace = 1;
						addLogEvent( 'Remplacement de l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' par un défenseur '.$ligneRemplacant['cle_roto_primaire'] );
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
						$upd_noteBonus-> execute(array('note' => $ligneRemplacant['note_bonus']-2, 'id_compo' => $donneesEffectifNonRemplace['id_compo'], 'id_joueur_reel' => $ligneRemplacant['id_joueur_reel']));
						addLogEvent( 'Note - 2 ');
						$upd_noteJoueurCompo->closeCursor();
						$upd_noteBonus->closeCursor();
					}
				}
				if($donneesAttaquantNonRemplace['position'] == 'Forward' && $estRemplace == 0){
					//Si il n'existe pas de remplacement d'un défenseur pour un attaquant alors le joueur ne peut pas être remplacé
					addLogEvent( 'Aucun défenseur pour remplacer l\'attaquant '.$donneesAttaquantNonRemplace['cle_roto_primaire'].' reste dans la compo');
					//On update. Le numéro définitif devient le numéro initialement prévu
					$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesAttaquantNonRemplace['numero'], 'id_compo' => $donneesAttaquantNonRemplace['id_compo'], 'id_joueur_reel' => $donneesAttaquantNonRemplace['id_joueur_reel']));
				}
			}
			$req_remplacant->closeCursor();
		}

		$req_attaquant_nonRemplace->closeCursor();
		$upd_numeroDefinitif->closeCursor();
	}

	function construireEquipeBoucle4($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif)
	{
		//Requete qui renvoie tous les joueurs titulaires, avec une note inférieur à la note minimum du remplacement tactique et un remplaçant ayant joué encore sur le banc
		$req_joueurAvecRemplacementTactiqueActif= $bdd->prepare('SELECT DISTINCT t3.id_joueur_reel, t3.id_compo, t4.cle_roto_primaire, t3.numero, t3.id_joueur_reel_remplacant
			FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
			WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL AND t3.note < t3.note_min_remplacement AND t3.id_joueur_reel_remplacant IN (
				SELECT t3.id_joueur_reel
				FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4
				WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL) ;');

		//constante 17 pour le test uniquement
		$req_joueurAvecRemplacementTactiqueActif->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue));

		while ($donneesRemplacementTactique = $req_joueurAvecRemplacementTactiqueActif->fetch())
		{
			//Il existe un remplacement tactique
			addLogEvent( 'Remplacement Tactique de '.$donneesRemplacementTactique['cle_roto_primaire'].' par le joueur avec l\'id : '.$donneesRemplacementTactique['id_joueur_reel_remplacant']) ;
			//On update. Le numéro définitif du joueur remplaçant devient le numéro du joueur remplacé
			$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesRemplacementTactique['numero'], 'id_compo' => $donneesRemplacementTactique['id_compo'], 'id_joueur_reel' => $donneesRemplacementTactique['id_joueur_reel_remplacant']));

			//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
			updateButReelDuJoueur($donneesRemplacementTactique['id_compo'], $constanteJourneeReelle, $donneesRemplacementTactique['id_joueur_reel_remplacant'], $bdd);

			//On update. Le numéro définitif du joueur remplacé devient 0 pour montrer qu'il a été remplacé
			$upd_numeroDefinitif->execute(array('numero_definitif' => 0, 'id_compo' => $donneesRemplacementTactique['id_compo'], 'id_joueur_reel' => $donneesRemplacementTactique['id_joueur_reel']));
			$upd_numeroDefinitif->closeCursor();
		}

		$req_joueurAvecRemplacementTactiqueActif->closeCursor();
	}

	function construireEquipeBoucle5($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif)
	{
		//Requete qui renvoie tous les joueurs titulaires, ayant joué mais n'ayant pas encore de numéro définitif
		$req_joueursRestants= $bdd->prepare('SELECT t3.id_compo, t3.id_joueur_reel, t4.cle_roto_primaire, t3.note, t3.numero, t3.numero_definitif FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero < 12 AND t3.numero_definitif IS NULL AND t3.note IS NOT NULL ;');
		$req_joueursRestants->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue));

		while ($donneesJoueursRestants = $req_joueursRestants->fetch())
		{
			//le remplacement tactique n'était pas possible
			addLogEvent( 'Le remplacement tactique de '.$donneesJoueursRestants['cle_roto_primaire'].' n\'était pas possible ');
			//On update. Le numéro définitif du joueur
			$upd_numeroDefinitif->execute(array('numero_definitif' => $donneesJoueursRestants['numero'], 'id_compo' => $donneesJoueursRestants['id_compo'], 'id_joueur_reel' => $donneesJoueursRestants['id_joueur_reel']));
			$upd_numeroDefinitif->closeCursor();

			//On regarde le nombre de but réel marqué par ce joueur sur cette journée et on update
			updateButReelDuJoueur($donneesJoueursRestants['id_compo'], $constanteJourneeReelle, $donneesJoueursRestants['id_joueur_reel'], $bdd);
		}
		$req_joueursRestants->closeCursor();
	}

	function construireEquipeBoucle6($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_noteBonus)
	{
		//Requete qui renvoie la liste des équipes ayant joué sur la journée ainsi que les malus appliquées
		$req_malus_bonus= $bdd->prepare('SELECT t2.id, t1.id_equipe, t2.id_equipe_dom, t2.id_equipe_ext, t1.code_bonus_malus
			FROM compo_equipe t1, calendrier_ligue t2
			WHERE t2.id_ligue = :id_ligue AND t2.num_journee_cal_reel = :num_journee_cal_reel AND t2.id = t1.id_cal_ligue ;');

		//Requete Joueur Concerné par le bonus
		$req_joueur_bonus = $bdd->prepare('SELECT t1.id_joueur_reel_equipe, t3.note, t3.note_bonus, t3.id_compo FROM bonus_malus t1, compo_equipe t4, calendrier_ligue t2, joueur_compo_equipe t3  WHERE t2.num_journee_cal_reel = :num_journee_cal_reel AND t2.id = t4.id_cal_ligue AND t4.id = t3.id_compo AND t3.numero_definitif IS NOT NULL AND t3.id_joueur_reel = t1.id_joueur_reel_equipe AND t1.id_equipe = :id_equipe AND t1.id_cal_ligue = :id_cal_ligue ;');

		//BONUS FAMILLE STADE
		$upd_noteFamille = $bdd->prepare('UPDATE compo_equipe t1, calendrier_ligue t2, joueur_compo_equipe t3 SET t3.note = :note WHERE t2.num_journee_cal_reel =  :num_journee_cal_reel AND t2.id = t1.id_cal_ligue AND t1.id = t3.id_compo AND t3.numero_definitif IS NOT NULL AND t3.id_joueur_reel = :id_joueur_reel and t3.note <= 9 ;');

		//MALUS FUMIGENE Requete note gardien d'une équipe
		$req_note_gardien = $bdd->prepare('SELECT t2.id_compo, t2.note, t2.note_bonus, t2.id_joueur_reel FROM compo_equipe t1, joueur_compo_equipe t2, calendrier_ligue t3 WHERE t1.id_equipe = :id_equipe AND t2.id_compo = t1.id AND t2.numero_definitif = 1 AND t3.id = t1.id_cal_ligue AND t1.id = t2.id_compo AND t3.num_journee_cal_reel = :num_journee_cal_reel ;');

		//MALUS FUMIGENE Update note gardien
		$upd_note_gardien = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND numero_definitif = 1 ;');

		//constante 17 pour le test uniquement
		$req_malus_bonus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel, 'id_ligue' => $idLigue));

		while ($donneesMalusBonus = $req_malus_bonus->fetch())
		{
			if(is_null($donneesMalusBonus['code_bonus_malus'])){
					//Pas de malus/bonus pour cette équipe
					addLogEvent( 'L\'équipe '.$donneesMalusBonus['id_equipe'].' n\'a pas mis de bonus/malus ');
			}else{
				if($donneesMalusBonus['code_bonus_malus'] == 'FUMIGENE'){
					if($donneesMalusBonus['id_equipe'] == $donneesMalusBonus['id_equipe_dom']){
						//Léquipe qui a mis ce malus/bonus est l'équipe domicile
						addLogEvent( 'L\'équipe '.$donneesMalusBonus['id_equipe'].' a mis un Fumigène à l\'équipe '.$donneesMalusBonus['id_equipe_ext']);
						//constante 17 pour le test uniquement
						$req_note_gardien->execute(array('id_equipe' => $donneesMalusBonus['id_equipe_ext'],'num_journee_cal_reel' => $constante_num_journee_cal_reel));
						$ligneNoteGardienAdverse = $req_note_gardien->fetchAll();
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
									$upd_note_gardien->execute(array('note' => $noteUpdateGardien, 'id_compo' => $noteGardienAdverse['id_compo']));
									addLogEvent( 'MAJ note bonus '.$note_bonus.' ' );
									$upd_noteBonus->execute(array('note' => $noteGardienAdverse['note_bonus']-1, 'id_compo' => $noteGardienAdverse['id_compo'], 'id_joueur_reel' => $noteGardienAdverse['id_joueur_reel']));
									addLogEvent( 'FUMIGENE: Malus de -1 sur le gardien de la compo '.$noteGardienAdverse['id_compo']);
									$upd_note_gardien->closeCursor();
									$upd_noteBonus->closeCursor();
								}
							}
						}
						$req_note_gardien->closeCursor();
					}else{
						//Léquipe qui a mis ce malus/bonus est l'équipe visiteur
						addLogEvent( 'L\'équipe '.$donneesMalusBonus['id_equipe'].' a mis un Fumigène à l\'équipe '.$donneesMalusBonus['id_equipe_dom']);
						//constante 17 pour le test uniquement
						$req_note_gardien->execute(array('id_equipe' => $donneesMalusBonus['id_equipe_dom'],'num_journee_cal_reel' => $constante_num_journee_cal_reel));
						$ligneNoteGardienAdverse = $req_note_gardien->fetchAll();
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
									$upd_note_gardien->execute(array('note' => $noteUpdateGardien, 'id_compo' => $noteGardienAdverse['id_compo']));
									addLogEvent( 'MAJ note bonus '.$note_bonus.' ' );
									$upd_noteBonus->execute(array('note' => $noteGardienAdverse['note_bonus']-1, 'id_compo' => $noteGardienAdverse['id_compo'], 'id_joueur_reel' => $noteGardienAdverse['id_joueur_reel']));
									addLogEvent( 'FUMIGENE: Malus de -1 sur le gardien de la compo '.$noteGardienAdverse['id_compo']);
									$upd_note_gardien->closeCursor();
									$upd_noteBonus->closeCursor();
								}
							}
						}
						$req_note_gardien->closeCursor();
					}
				}elseif($donneesMalusBonus['code_bonus_malus'] == 'FAM_STA'){
					//Bonus Famille dans le stade, +1 pour le joueur choisi si dans compo finale et si note <= 9
					$req_joueur_bonus->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel,'id_equipe' => $donneesMalusBonus['id_equipe'],'id_cal_ligue' => $donneesMalusBonus['id']));
					$lignesJoueurBonus = $req_joueur_bonus->fetchAll();
					foreach ($lignesJoueurBonus as $idJoueurBonus) {
						addLogEvent( $donneesMalusBonus['code_bonus_malus'].' sur le joueur_reel '.$idJoueurBonus['id_joueur_reel_equipe'].' qui a déjà la note de '.$idJoueurBonus['note']);
						$upd_noteFamille->execute(array('note' => $idJoueurBonus['note']+1, 'num_journee_cal_reel' => $constante_num_journee_cal_reel,'id_joueur_reel' => $idJoueurBonus['id_joueur_reel_equipe']));
						addLogEvent( 'MAJ note bonus '.$note_bonus.' ' );
						$upd_noteBonus->execute(array('note' => $idJoueurBonus['note_bonus']+1, 'id_compo' => $idJoueurBonus['id_compo'], 'id_joueur_reel' => $idJoueurBonus['id_joueur_reel_equipe']));
						$upd_noteFamille->closeCursor();
						$upd_noteBonus->closeCursor();
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
		$req_malus_bonus->closeCursor();
	}

	function construireEquipeBoucle7($bdd, $constante_num_journee_cal_reel, $idLigue, $req_listeConfrontationParJournee)
	{
		// Calcul But Virtuel

		//constante 17 pour le test uniquement
		$req_listeConfrontationParJournee->execute(array('id_ligue' => $idLigue, 'num_journee_cal_reel' => $constante_num_journee_cal_reel));
		$i=0;
		while ($listeConfrontationParJournee = $req_listeConfrontationParJournee->fetch())
		{
			if($i==0){
				$equipeA = $listeConfrontationParJournee['id'];
				$i++;
			}else{
				$equipeB = $listeConfrontationParJournee['id'];
				$i=0;
				calculButVirtuel($equipeA,$equipeB,$bdd);
			}
		}
		$req_listeConfrontationParJournee->closeCursor();
	}

	function construireEquipeBoucle8($bdd, $constante_num_journee_cal_reel, $idLigue)
	{
		$req_buteurs_impactes_par_malus_dinarb = $bdd->prepare('SELECT DISTINCT IF(t5.id_equipe = cl.id_equipe_dom, cl.id_equipe_ext, cl.id_equipe_dom) AS \'id_adversaire\', cl.id, t4.id_compo, t4.id_joueur_reel, t4.nb_but_reel
			FROM joueur_compo_equipe t4, compo_equipe t5, calendrier_ligue cl
			WHERE cl.id = t5.id_cal_ligue AND t5.id = t4.id_compo AND t4.numero_definitif IS NOT NULL AND t4.nb_but_reel > 0 AND t5.id_equipe IN(
				SELECT t1.id_equipe_dom FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3
				WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t2.code_bonus_malus = \'DIN_ARB\' AND t3.id_compo = t2.id AND t1.id_equipe_dom != t2.id_equipe
				UNION SELECT t1.id_equipe_ext FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3
				WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t2.code_bonus_malus = \'DIN_ARB\' AND t3.id_compo = t2.id AND t1.id_equipe_ext != t2.id_equipe);');

		//MALUS DIN ARB Update nb_but_reel buteur
		$upd_nb_but_buteur = $bdd->prepare('UPDATE joueur_compo_equipe SET nb_but_reel = :nb_but_reel WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');

		$req_buteurs_impactes_par_malus_dinarb->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
		$id_compo_deja_affecte=-1;

		$upd_buteur_impacte_par_malus_dinarb = $bdd->prepare('UPDATE bonus_malus SET id_joueur_reel_adverse = :id_joueur_reel_adverse WHERE id_equipe = :id_equipe AND id_cal_ligue = :id_cal_ligue;');

		while ($listeButeursImpactesMalusDinArb = $req_buteurs_impactes_par_malus_dinarb->fetch())
		{
			if($listeButeursImpactesMalusDinArb['id_compo'] != $id_compo_deja_affecte)
			{
					//Update -1 sur le but réel d'un joueur
					if($listeButeursImpactesMalusDinArb['nb_but_reel'] == 1){
						$upd_nb_but_buteur->execute(array('nb_but_reel' => NULL, 'id_compo' => $listeButeursImpactesMalusDinArb['id_compo'], 'id_joueur_reel' => $listeButeursImpactesMalusDinArb['id_joueur_reel'] ));
					}else{
						$upd_nb_but_buteur->execute(array('nb_but_reel' => $listeButeursImpactesMalusDinArb['nb_but_reel']-1, 'id_compo' => $listeButeursImpactesMalusDinArb['id_compo'], 'id_joueur_reel' => $listeButeursImpactesMalusDinArb['id_joueur_reel'] ));
					}

					$upd_buteur_impacte_par_malus_dinarb->execute(array('id_joueur_reel_adverse' => $listeButeursImpactesMalusDinArb['id_joueur_reel'],'id_equipe' => $listeButeursImpactesMalusDinArb['id_aversaire'], 'id_cal_ligue' => $listeButeursImpactesMalusDinArb['id']));
					$upd_buteur_impacte_par_malus_dinarb->closeCursor();
					$id_compo_deja_affecte = $listeButeursImpactesMalusDinArb['id_compo'];
					addLogEvent( 'Joueur avec id : '.$listeButeursImpactesMalusDinArb['id_joueur_reel'].' perd 1 but réel [MALUS DIN ARB]');
			}
		}
		$upd_nb_but_buteur->closeCursor();
		$req_buteurs_impactes_par_malus_dinarb->closeCursor();
	}

	function construireEquipeBoucle9($bdd, $constante_num_journee_cal_reel, $idLigue, $req_listeConfrontationParJournee)
	{
		//constante 17 pour le test uniquement
		$req_listeConfrontationParJournee->execute(array('id_ligue' => $idLigue, 'num_journee_cal_reel' => $constante_num_journee_cal_reel));
		$i=0;
		while ($listeConfrontationParJournee = $req_listeConfrontationParJournee->fetch())
		{
			afficherUneCompo($listeConfrontationParJournee['id'], $bdd);
			if($i==0){
				addLogEvent( ' ------ VERSUS ----------');
				$i++;
			}else{
				$i=0;
			}
		}
		$req_listeConfrontationParJournee->closeCursor();
	}

	//$idLigue = 6;
	$constanteJourneeReelle = isset($_POST['journeeCalculable']) ? $_POST['journeeCalculable'] : NULL;
	//$constante_num_journee_cal_reel = '29'; //Pour test
	$constante_num_journee_cal_reel = substr($constanteJourneeReelle, -2); //En prod

	$upd_remise_a_zero_jce = $bdd->prepare('UPDATE joueur_compo_equipe, calendrier_ligue, compo_equipe SET joueur_compo_equipe.note = NULL, joueur_compo_equipe.note_bonus = NULL, joueur_compo_equipe.nb_but_reel = NULL, joueur_compo_equipe.nb_but_virtuel = NULL, joueur_compo_equipe.numero_definitif = NULL WHERE calendrier_ligue.num_journee_cal_reel = :num_journee_cal_reel AND calendrier_ligue.id = compo_equipe.id_cal_ligue AND compo_equipe.id = joueur_compo_equipe.id_compo;');
	$upd_remise_a_zero_jce->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	addLogEvent( 'Remise à zéro des stats des compos concernees par la journée '.$constante_num_journee_cal_reel);

	$req_ligues_concernees = $bdd->prepare('SELECT distinct id_ligue FROM calendrier_ligue WHERE num_journee_cal_reel = :num_journee_cal_reel;');
	$req_ligues_concernees->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
	$ligues_concernees = $req_ligues_concernees->fetchAll();

	if (count($ligues_concernees) == 0) {
		addLogEvent('Aucune ligue sur la journee '.$constante_num_journee_cal_reel);
	} else {

		// BOUCLE MAJEURE SUR CHAQUE LIGUE
		foreach ($ligues_concernees as $ligue_concernee)
		{
			$idLigue = $ligue_concernee['id_ligue'];
			addLogEvent( ' **************************** LIGUE n°'.$idLigue.' ********************************');

			addLogEvent( ' ************************ CALCUL ET UPDATE DES NOTES **************************');

			$upd_noteBonus = $bdd->prepare('UPDATE joueur_compo_equipe SET note_bonus = :note WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');
			$upd_noteJoueurCompo = $bdd->prepare('UPDATE joueur_compo_equipe SET note = :note WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');

			calculerNotes($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_noteJoueurCompo, $upd_noteBonus);

			$upd_noteJoueurCompo->closeCursor();
			$upd_noteBonus->closeCursor();

			//################## Première boucle ############################
			//Ici on passe en revue tous les joueurs titulaires dans les compos
			// On update le numéro définitif des joueurs ayant joués et qui n'ont pas de remplacement tactique
			// On effectue les remplacements poste pour poste
			// On acte le fait que défenseurs absents n'ayant pas de remplaçant seront définitivement absents
			// On update les buts réels marqués par les joueurs ayant reçu un numéro définitif dans la compo

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 1 **************************');

			//On update le numéro définitif
			$upd_numeroDefinitif = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = :numero_definitif WHERE id_compo = :id_compo AND id_joueur_reel = :id_joueur_reel ;');

			//On récupère la liste des remplaçants
			$req_remplacant = $bdd->prepare('SELECT  t3.id_joueur_reel, t4.cle_roto_primaire,  t4.position, t3.numero, t3.note, t3.note_bonus, t3.numero_definitif
				FROM calendrier_ligue t1, compo_equipe t2, joueur_compo_equipe t3, joueur_reel t4 WHERE t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id_ligue = :id_ligue AND t2.id_cal_ligue = t1.id AND t3.id_compo = t2.id AND t4.id = t3.id_joueur_reel AND t3.numero > 11 AND t2.id_equipe = :id_equipe AND t3.numero_definitif IS NULL ORDER By t3.numero ;');

			construireEquipeBoucle1($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif, $req_remplacant);

			//################## Deuxième boucle ############################
			//Ici on passe en revue tous les joueurs titulaire dans les compos mais absents et n'ayant pas eu de remplacement poste pour poste
			// On vérifie si un joueur de la ligne inférieur a joué parmis les remplaçants encore disponibles
			// On applique une minoration de la note si il y a remplacement

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 2 **************************');

			construireEquipeBoucle2($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif,
				$req_remplacant, $upd_noteJoueurCompo, $upd_noteBonus);

			//################## Troisième boucle ############################
			//Ici on passe en revue tous les attaquants titulaires dans les compos mais absents et n'ayant pas eu de remplacement par un attaquant ou un milieu
			// On vérifie si un défenseur a joué parmis les remplaçants encore disponibles
			// On applique une minoration de la note si il y a remplacement

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 3 **************************');

			construireEquipeBoucle3($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif,
				$req_remplacant, $upd_noteJoueurCompo, $upd_noteBonus);

			//################## Quatrième boucle ############################
			//Ici on passe en revue tous les joueurs ayant un remplacement tactique programmé
			// On vérifie que le remplaçant n'est pas déjà entré dans l'effectif
			// On vérifie si le remplacement tactique s'applique
			// On update le remplacement

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 4 **************************');

			construireEquipeBoucle4($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif);

			//################## Cinquième boucle ############################
			// Ici on passe en revue tous les joueurs titulaires pour qui le remplacement tactique ne s'est pas appliqué
			// On update les numéros définitifs
			// L'équipe doit être complète

			addLogEvent( ' ************************ CONSTRUCTION EQUIPE - BOUCLE 5 **************************');

			construireEquipeBoucle5($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_numeroDefinitif);

			//################## Sixième boucle ############################
			// Ici on applique les malus infligés par l'équipe adverse et affectant les notes des joueurs

			addLogEvent( ' ************************ APPLICATION DES MALUS ADVERSAIRE - BOUCLE 6 **************************');

			construireEquipeBoucle6($bdd, $constante_num_journee_cal_reel, $idLigue, $upd_noteBonus);

			//################## Septième boucle ############################
			// Ici on passe en revue tous les joueurs titulaires pour qui le remplacement tactique ne s'est pas appliqué
			// On update les numéros définitifs
			// L'équipe doit être complète

			addLogEvent( ' ************************ CALCUL BUT VIRTUEL EQUIPE - BOUCLE 7 **************************');

			$req_listeConfrontationParJournee = $bdd->prepare('SELECT id_cal_ligue, t2.id
				FROM calendrier_ligue t1, compo_equipe t2
				WHERE t1.id_ligue = :id_ligue AND t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t1.id_equipe_dom = t2.id_equipe
				UNION SELECT id_cal_ligue, t2.id
				FROM calendrier_ligue t1, compo_equipe t2
				WHERE t1.id_ligue = :id_ligue AND t1.num_journee_cal_reel = :num_journee_cal_reel AND t1.id = t2.id_cal_ligue AND t1.id_equipe_ext = t2.id_equipe ORDER BY id_cal_ligue ;');

			construireEquipeBoucle7($bdd, $constante_num_journee_cal_reel, $idLigue, $req_listeConfrontationParJournee);

			//################## Huitième boucle ############################
			// Ici supprime le but réel d'un joueur en réponse au Malus DIN_ARB

			addLogEvent( ' ************************ MALUS DIN_ARB - BOUCLE 8 **************************');

			construireEquipeBoucle8($bdd, $constante_num_journee_cal_reel, $idLigue);

			//A FAIRE

			addLogEvent( ' ************************ AFFICHAGE EQUIPE - BOUCLE 9 **************************');

			construireEquipeBoucle9($bdd, $constante_num_journee_cal_reel, $idLigue, $req_listeConfrontationParJournee);

			//Application des malus équipe de l'adversaire (MAJ Note)

		}	//FIN DE BOUCLE FOR EACH SUR LA LIGUE
	}	//FIN DU IF SUR LA LIGUE

	addLogEvent( ' ************************ NETTOYAGE DES NUMEROS DEFINITIFS A ZERO ET DES NOTES DES REMPLACANTS **************************');;

	$upd_pas_de_note_remplacant = $bdd->prepare('UPDATE joueur_compo_equipe SET note = NULL WHERE numero > 11 AND numero_definitif IS NULL;');
	$upd_pas_de_note_remplacant->execute();

	$upd_numero_definitif_zero = $bdd->prepare('UPDATE joueur_compo_equipe SET numero_definitif = NULL WHERE (numero_definitif = 0 OR note IS NULL);');
	$upd_numero_definitif_zero->execute();

	addLogEvent( ' ************************ MISE A JOUR DES STATS **************************');

	//Mise à jour des stats

	//CSC
	impactCSC($constanteJourneeReelle, $constante_num_journee_cal_reel, $bdd);

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
		$req_set_rank = $bdd->prepare('SET @rank :=0;');
		$req_classement_ligue = $bdd->prepare('SELECT tmp.id_ligue, tmp.id, @rank := @rank+1 as \'rank\'
			FROM (Select e.id_ligue, e.id, (e.nb_victoire*3)+(e.nb_nul*2)+(e.nb_but_pour/100)-(e.nb_but_contre/100) as \'points\' from equipe e
			WHERE e.id_ligue = :id_ligue) tmp
			GROUP BY tmp.id_ligue, tmp.id
			ORDER BY tmp.points DESC;');

		$upd_classement_ligue = $bdd->prepare('UPDATE equipe SET classement = :classement WHERE id_ligue = :id_ligue and id = :id;');

		while ($listeLiguesConcernees = $req_ligues_concernees->fetch())
		{
			$req_set_rank->execute();
			$req_classement_ligue->execute(array('id_ligue' => $listeLiguesConcernees['id_ligue']));
			while ($classementCalcule = $req_classement_ligue->fetch())
			{
				$upd_classement_ligue->execute(array('classement' => $classementCalcule['rank'], 'id_ligue' => $classementCalcule['id_ligue'], 'id' => $classementCalcule['id']));
				$upd_classement_ligue->closeCursor();
			}
			$req_set_rank->closeCursor();
			$req_classement_ligue->closeCursor();
		}
		$req_ligues_concernees->closeCursor();

		$upd_statut_journee = $bdd->prepare('UPDATE calendrier_reel SET statut = 2 WHERE num_journee = :num_journee_cal_reel;');
		$upd_statut_journee->execute(array('num_journee_cal_reel' => $constante_num_journee_cal_reel));
		$upd_statut_journee->closeCursor();

		echo 'Fin script';
?>

</body>
</html>
