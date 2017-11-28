<?php
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