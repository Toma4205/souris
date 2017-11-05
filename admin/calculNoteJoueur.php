<html>
<body>
<table border="1">
<tbody>

<?php
	$idJourneeDemandee = isset($_POST['idJourneeCalculNote']) ? $_POST['idJourneeCalculNote'] : NULL;
	function importTableJoueursReels(){
		$row=0;
		if (($joueursReelFile = fopen("csvJoueursReels/ListeJoueursReels.csv", "r")) !== FALSE) {
			while (($fichier_joueursReel = fgetcsv($joueursReelFile, 1000, ";")) !== FALSE) {
				$num_resultats = count($fichier_joueursReel);
				for ($c=0; $c < $num_resultats; $c++) {
					$tableau_joueursReel[$row][] = $fichier_joueursReel[$c];
				}
				$row++;
			}
		}	
		fclose($joueursReelFile);
		return $tableau_joueursReel;
	}
	
	function getPositionJoueursReels($idJoueur){
		$tableauJoueursReels = importTableJoueursReels();
		$positionJoueur = 0;
		foreach($tableauJoueursReels as $ligneJoueursReels){
			if($ligneJoueursReels[5]==$idJoueur or $ligneJoueursReels[6]==$idJoueur){
				switch ($ligneJoueursReels[4]) {
				    case "Goalkeeper":
						$positionJoueur = 1;
						break;
					case "Defender":
						$positionJoueur = 2;
						break;
					case "Midfielder":
						$positionJoueur = 3;
						break;
					case "Forward" :
						$positionJoueur = 4;
					break;
				}
			}
		}
		return $positionJoueur;
	}
	
	function getTeamJoueursReels($idJoueur){
		$tableauJoueursReels = importTableJoueursReels();
		$teamJoueur = 0;
		foreach($tableauJoueursReels as $ligneJoueursReels){
			if($ligneJoueursReels[5]==$idJoueur or $ligneJoueursReels[6]==$idJoueur){
				$teamJoueur = $ligneJoueursReels[3];
			}
		}
		return $teamJoueur;
	}
	
	function importTableRegle(){
		$row=0;
		if (($reglesFile = fopen("csvRegleEtScore/tableRegles.csv", "r")) !== FALSE) {
			while (($fichier_regles = fgetcsv($reglesFile, 1000, ";")) !== FALSE) {
				$num_resultats = count($fichier_regles);
				for ($c=0; $c < $num_resultats; $c++) {
					$tableau_regles[$row][] = $fichier_regles[$c];
				}
				$row++;
			}
		}	
		fclose($reglesFile);
		return $tableau_regles;
	}
	
	function importTableScoreToNote(){
		$row=0;
		if (($scoreToNoteFile = fopen("csvRegleEtScore/tableScoreToNote.csv", "r")) !== FALSE) {
			while (($fichier_scoreToNote = fgetcsv($scoreToNoteFile, 1500, ";")) !== FALSE) {
				$num_resultats = count($fichier_scoreToNote);
				for ($c=0; $c < $num_resultats; $c++) {
					$tableau_scoreToNote[$row][] = $fichier_scoreToNote[$c];
				}
				$row++;
			}
		}	
		fclose($scoreToNoteFile);
		return $tableau_scoreToNote;
	}
	
	function calculNoteJoueurJournee($idJoueur,$idJournee){
		//echo 'Fonction calculNoteJoueurJournee('.$idJoueur.','.$idJournee.')';
		//echo "<br />\n";
		$tableau_scoreToNote = importTableScoreToNote();
		$tableau_regles = importTableRegle();
		$positionJoueur = getPositionJoueursReels($idJoueur);
		$scoreJoueur = 0;
		$noteJoueur = -1; //valeur par défaut si le joueur n'a pas joué
		//$positionJoueur c'est 1 Gardien, 2 Défenseur, 3 Milieu et 4 Attaquant
		//$statsDuJoueur[];
		$row = 0;
		if($positionJoueur==0){
			echo 'Erreur: position du joueur ('.$idJoueur.') inconnue';
			echo "<br />\n";
		}
		if (($statsJoueurFile = fopen("csvStatsJoueurs/tableGeneraleStatsJoueur.csv", "r")) !== FALSE) {
			while (($fichier_statsJoueur = fgetcsv($statsJoueurFile, 3000, ";")) !== FALSE) {

				if($row==0 or ($fichier_statsJoueur[0]== $idJoueur and $fichier_statsJoueur[1]== $idJournee)){
					for ($c=0; $c <count($fichier_statsJoueur); $c++) {
						$statsDuJoueur[$row][] = $fichier_statsJoueur[$c];
					}
					$row++;
				}
			}
			fclose($statsJoueurFile);
			if(count($statsDuJoueur)<=1){
				echo 'Les stats de ce joueur n\'ont pas été importées sur cette journée';
				echo "<br />\n";
			}else{
				//multiplie le tableau statsDuJoueur par le tableau tableau_regles
				for($c=0; $c<count($statsDuJoueur[0]);$c++){
					foreach($tableau_regles as $ligneRegle){
						if($ligneRegle[0]==$statsDuJoueur[0][$c]){
							$scoreJoueur+=($ligneRegle[$positionJoueur]*$statsDuJoueur[1][$c]);
						}
					}		
				}
				foreach($tableau_scoreToNote as $ligneScore){
					if($ligneScore[0]==round($scoreJoueur,2)){
						$noteJoueur = $ligneScore[$positionJoueur];
					}
				}	
			}
		}
		return $noteJoueur;
	}
	
	function getNotesDesJoueursDuneJournee($idJournee){
		$idJoueursParticipantALaJournee;
		$tableauFinalNotesJoueursJournee;
		$row=0;
		if (($statsJoueurFile = fopen("csvStatsJoueurs/tableGeneraleStatsJoueur.csv", "r")) !== FALSE) {
			while (($fichier_statsJoueur = fgetcsv($statsJoueurFile, 3000, ";")) !== FALSE) {
				if($fichier_statsJoueur[1]== $idJournee){
					$idJoueursParticipantALaJournee[$row] = $fichier_statsJoueur[0];
					$row++;
				}
			}
			fclose($statsJoueurFile);
		}
		$row=0;
		foreach($idJoueursParticipantALaJournee as $ligneJoueurAyantJoueSurLaJournee){
			$tableauFinalNotesJoueursJournee[$row][] = $ligneJoueurAyantJoueSurLaJournee;
			$tableauFinalNotesJoueursJournee[$row][] = getTeamJoueursReels($ligneJoueurAyantJoueSurLaJournee);
			$tableauFinalNotesJoueursJournee[$row][] = calculNoteJoueurJournee($ligneJoueurAyantJoueSurLaJournee,$idJournee);
			$row++;
		}
				
		return $tableauFinalNotesJoueursJournee;
	}
	
	function afficherLesNotes($tableauDesNotes){
		echo "<br />\n";
		foreach($tableauDesNotes as $tab){
?>
	<tr>
<?php			
			for ($c=0; $c < count($tab); $c++){
?>
	<td>
<?php					
					echo $tab[$c];
?>
	</td>
<?php					
			}
?>
	</tr>
<?php		
		}
?>
</table>
</tbody>
<?php
	}
	
	afficherLesNotes(getNotesDesJoueursDuneJournee($idJourneeDemandee));		

?>

</body>
</html>