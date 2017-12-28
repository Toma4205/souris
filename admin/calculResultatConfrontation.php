<?php

	$constanteConfrontation = 39;
	$constanteJourneeReelle = 201715;
	
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
		
		$req_effectifs = $bdd->prepare('SELECT t4.id_equipe_dom, t4.id_equipe_ext, t1.id_equipe, t3.cle_roto_primaire, t3.position, t2.numero, t1.code_tactique, t1.code_bonus_malus, t2.capitaine, t2.numero_remplacant FROM compo_equipe t1, joueur_compo_equipe t2, joueur_reel t3, calendrier_ligue t4 WHERE id_cal_ligue = 39 AND t1.id = t2.id_compo AND t2.id_joueur_reel = t3.id AND t4.id = t1.id_cal_ligue ORDER BY t1.id_equipe, t2.numero');
		
		$req_effectifs->execute(array('id_cal_ligue' => $constanteConfrontation));
		/*
			Permet d'obtenur un tableau au format suivant : id_equipe_dom / id_equipe_ext / id_equipe / cle_roto_primaire / position / numero / code_tactique / code_bonus_malus / capitaine / numero_remplacant
		*/
		
		/*
			Boucle pour 
						- update des notes obtenues après-bonus dans la table joueur_compo_equipe
						- cumul des buts réels
						- calculs des moyennes par ligne
						- détection des buts virtuels
						- update des scores de la confrontation
		*/
		
		while ($donnees = $req_effectifs->fetch())
		{
			$donnees['
			if($donnees['a_joue'] == '0'){
				$noteObtenue = 0;
				echo ' || '.$donnees['id'].' sur la journee '.$donnees['journee'].' obtient la note de'.$noteObtenue;
				echo "<br />\n";
				$upd_note->execute(array('note' => $noteObtenue,'id' => $donnees['id'],'journee' => $donnees['journee']));
			}else{
				$req_regleCalcul->execute(array('position' => $donnees['position']));
				while ($tableauReglesCalcul = $req_regleCalcul->fetch())
				{
					$scoreCalcule += $donnees[$tableauReglesCalcul['StatName']] * $tableauReglesCalcul['Ponderation'];
				}
				
				$req_scoreToNote->execute(array('scoreObtenu' => round($scoreCalcule,2),'position' => $donnees['position']));
				$noteObtenue=$req_scoreToNote->fetch(PDO::FETCH_ASSOC);
				echo ' || '.$donnees['id'].' sur la journee '.$donnees['journee'].' obtient la note de '.$noteObtenue['note'];
				echo "<br />\n";
				$upd_note->execute(array('note' => $noteObtenue['note'],'id' => $donnees['id'],'journee' => $donnees['journee']));
			}
			
		}
		$req->closeCursor();
		$req_regleCalcul->closeCursor();
		$req_scoreToNote->closeCursor();
		$upd_note->closeCursor();
	
?>