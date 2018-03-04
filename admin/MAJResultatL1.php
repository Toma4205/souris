<?php

	function ajoutResultatDansBDD($messageMajScore){
		$idJournee = isset($_POST['idJournee']) ? $_POST['idJournee'] : NULL;
		$teamDom = isset($_POST['teamDom']) ? $_POST['teamDom'] : NULL;
		$butsDom = isset($_POST['butsDom']) ? $_POST['butsDom'] : NULL;
		$penaltyDom = isset($_POST['penaltyDom']) ? $_POST['penaltyDom'] : NULL;
		$teamExt = isset($_POST['teamExt']) ? $_POST['teamExt'] : NULL;
		$butsExt = isset($_POST['butsExt']) ? $_POST['butsExt'] : NULL;
		$penaltyExt = isset($_POST['penaltyExt']) ? $_POST['penaltyExt'] : NULL;

		$messageMajScore = $messageMajScore . 'Journée n°'.substr($idJournee,strlen($idJournee)-2,2) . '<br/>';

		if($butsDom == 'ANNULE'){
			$etatDom = 'ANNULE';
			$etatExt = 'ANNULE';
			$messageMajScore = $messageMajScore . '/ '.$teamDom . ' Match Annulé : '.$butsdom .
				' ('.$penaltyDom . ' pen) - '.$butsExt . ' ('.$penaltyExt . ' pen) : '.$teamExt . '<br/>';
			$tableau[] = array(substr($idJournee,strlen($idJournee)-6,6),$teamDom,'Dom','0',$etatDom,'0',$teamExt,'Visit','0',$etatExt,'0');
		}else{
			if($butsDom>$butsExt){

				$etatDom = 'W';
				$etatExt = 'L';
				$messageMajScore = $messageMajScore . '/ '.$teamDom.' Victoire : '.$butsDom.' ('.$penaltyDom.' pen) - '.$butsExt.' ('.$penaltyExt.' pen) : '.$teamExt.' Défaite' . '<br/>';
			}elseif($butsDom<$butsExt){

				$etatDom = 'L';
				$etatExt = 'W';
				$messageMajScore = $messageMajScore . '/ '.$teamDom.' Défaite : '.$butsDom.' ('.$penaltyDom.' pen) - '.$butsExt.' ('.$penaltyExt.' pen) : '.$teamExt.' Victoire' . '<br/>';
			}else{

				$etatDom = 'D';
				$etatExt = 'D';
				$messageMajScore = $messageMajScore . '/ '.$teamDom.' Nul : '.$butsDom.' ('.$penaltyDom.' pen) - '.$butsExt.' ('.$penaltyExt.' pen) : '.$teamExt.' Nul' . '<br/>';
			}

			$tableau[] = array(substr($idJournee,strlen($idJournee)-6,6),$teamDom,'Dom',$butsDom,$etatDom,$penaltyDom,$teamExt,'Visit',$butsExt,$etatExt,$penaltyExt);
		}


		//Ajout à la suite du fichier csv
		/*$chemin = 'csvResultatsEquipes/resultatsL1.csv';
		$delimiteur = ';';
		$fichier_csv = fopen($chemin, 'a');
		//fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

		foreach($tableau as $ligne){
			// chaque ligne en cours de lecture est insérée dans le fichier
			// les valeurs présentes dans chaque ligne seront séparées par $delimiteur
			fputcsv($fichier_csv, $ligne, $delimiteur);
		}
		// fermeture du fichier csv
		fclose($fichier_csv);
		*/

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


		$resultat_nouveau = $bdd->prepare('SELECT id FROM resultatsl1_reel WHERE journee = :journee AND equipeDomicile = :equipeDomicile AND equipeVisiteur = :equipeVisiteur');
		$resultat_nouveau->execute (array('journee' => $tableau[0][0],'equipeDomicile' => $tableau[0][1],'equipeVisiteur' => $tableau[0][6]));
		$nbResultat_nouveau = $resultat_nouveau->rowCount();
		$resultat_nouveau->closeCursor();

		if($nbResultat_nouveau < 1){
			$req = $bdd->prepare('INSERT INTO resultatsl1_reel( journee,equipeDomicile,homeDomicile,butDomicile,winOrLoseDomicile,penaltyDomicile,equipeVisiteur,homeVisiteur,butVisiteur,WinOrLoseVisiteur,penaltyVisiteur) VALUES(
			:journee,
			:equipeDomicile,
			:homeDomicile,
			:butDomicile,
			:winOrLoseDomicile,
			:penaltyDomicile,
			:equipeVisiteur,
			:homeVisiteur,
			:butVisiteur,
			:WinOrLoseVisiteur,
			:penaltyVisiteur)');
			$req->execute(array(
				'journee' => $tableau[0][0],
				'equipeDomicile' => $tableau[0][1],
				'homeDomicile' => $tableau[0][2],
				'butDomicile' => $tableau[0][3],
				'winOrLoseDomicile' => $tableau[0][4],
				'penaltyDomicile' => $tableau[0][5],
				'equipeVisiteur' => $tableau[0][6],
				'homeVisiteur' => $tableau[0][7],
				'butVisiteur' => $tableau[0][8],
				'WinOrLoseVisiteur' => $tableau[0][9],
				'penaltyVisiteur' => $tableau[0][10]
				));
				$req->closeCursor();
			$messageMajScore = $messageMajScore . 'INSERT en BDD => OK <br/>';
		}else{
			$messageMajScore = $messageMajScore . 'Ce résultat est déjà présent en base de donnée. <br/>';
		}

		return $messageMajScore;
	}

	function testResultatsCorrect(){

		$idJournee = isset($_POST['idJournee']) ? $_POST['idJournee'] : NULL;
		$teamDom = isset($_POST['teamDom']) ? $_POST['teamDom'] : NULL;
		$butsDom = isset($_POST['butsDom']) ? $_POST['butsDom'] : NULL;
		$penaltyDom = isset($_POST['penaltyDom']) ? $_POST['penaltyDom'] : NULL;
		$teamExt = isset($_POST['teamExt']) ? $_POST['teamExt'] : NULL;
		$butsExt = isset($_POST['butsExt']) ? $_POST['butsExt'] : NULL;
		$penaltyExt = isset($_POST['penaltyExt']) ? $_POST['penaltyExt'] : NULL;
		$messageRetour = '';

		if(is_null($idJournee)){
				$messageRetour .= 'ERREUR : Aucune Journée sélectionnée';
				$messageRetour .=  "<br />\n";
		}
		if(is_null($teamDom)){
				$messageRetour .= 'ERREUR : Aucune Equipe Domicile sélectionnée';
				$messageRetour .=  "<br />\n";
		}
		if(!is_null($teamDom) and ($teamDom==$teamExt)){
				$messageRetour .= 'ERREUR : Les équipes Domicile et Visiteur sont les mêmes';
				$messageRetour .=  "<br />\n";
		}
		if(is_null($butsDom)){
				$messageRetour .= 'ERREUR : Le nombre de but à domicile est vide';
				$messageRetour .=  "<br />\n";
		}
		if(is_null($penaltyDom)){
				$messageRetour .= 'ERREUR : Le nombre de penalty à domicile est vide';
				$messageRetour .=  "<br />\n";
		}
		if(!is_null($butsDom) and $butsDom !== 'ANNULE' and !is_null($penaltyDom) and $butsDom<$penaltyDom){
				$messageRetour .= 'ERREUR : Le nombre de penalty à domicile est supérieur au nombre de but';
				$messageRetour .=  "<br />\n";
		}
		if(!is_null($butsDom) and !is_null($butsExt) and ($butsDom == 'ANNULE' xor $butsExt == 'ANNULE')){
				$messageRetour .= 'ERREUR : Une seule équipe est notée comme ANNULE';
				$messageRetour .=  "<br />\n";
		}
		if(is_null($teamExt)){
				$messageRetour .= 'ERREUR : Aucune Equipe Visiteur sélectionnée';
				$messageRetour .=  "<br />\n";
		}
		if(is_null($butsExt)){
				$messageRetour .= 'ERREUR : Le nombre de but des visiteurs est vide';
				$messageRetour .=  "<br />\n";
		}
		if(!is_null($butsExt) and $butsExt !== 'ANNULE' and !is_null($penaltyExt) and $butsExt<$penaltyExt){
				$messageRetour .= 'ERREUR : Le nombre de penalty des visiteurs est supérieur au nombre de but';
				$messageRetour .=  "<br />\n";
		}
		if(is_null($penaltyExt)){
				$messageRetour .= 'ERREUR : Le nombre de penalty des visiteurs est vide';
				$messageRetour .=  "<br />\n";
		}

		return $messageRetour;
	}

	$messageMajScore = 'Statut du processus "Ajout du Nouveau Résultat" : <br/>';
	if(strlen(testResultatsCorrect())>1){
		$messageMajScore = $messageMajScore . testResultatsCorrect();
	}else{
		$messageMajScore = ajoutResultatDansBDD($messageMajScore);
	}

?>
