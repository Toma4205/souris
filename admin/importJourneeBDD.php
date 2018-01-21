<html>
<body>

<?php

$row = 0;
$fichier = $_FILES['mon_fichier']['tmp_name'];
$fichierName = $_FILES['mon_fichier']['name'];

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
	$resultatsJourneeTab = null;
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
	$req = $bdd->prepare('SELECT * FROM resultatsL1_reel WHERE journee = :journee');
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

$resultatsJournee = buildTableauJournee(substr($fichierName,0,6));
if (($handle = fopen($fichier, "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		$num = count($data);
		// echo "<p> $num champs à la ligne $row: <br /></p>\n";
		if($row==0) {
			$tableau[$row][0] = 'IDRECHERCHE';
			$tableau[$row][1] = 'JOURNEE';
			$tableauEnTete[0] = 'IDRECHERCHE';
			$tableauEnTete[1] = 'JOURNEE';
		}else {
			$tableau[$row][0] = str_replace(' ','_',rtrim(ltrim($data[0])).rtrim(ltrim($data[1])).$data[2]);
			$tableau[$row][1] = substr($fichierName,0,6);
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
		//echo '---'. $tableau[$row][0];
		//echo '## '.count($tableau[$row]).' ##';
		
		//echo 'Calculs buts encaissés : ';
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
		//echo '## '.count($tableau[$row]).' ##';
		//echo 'Calculs cartons rouge : ';
		
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
		//echo '## '.count($tableau[$row]).' ##';
		//echo 'Calculs Centre Raté : ';
		$tableau[$row][] = ($data[21]-$data[22]);//'CentreRate';
		
		//echo '## '.count($tableau[$row]).' ##';
		//echo 'Calculs CleanSheet W : ';
		if(isCleanSheet($row,$data[2],$resultatsJournee,$data)){
			$tableau[$row][] = 1; //'Clean60';
		}else{
			$tableau[$row][] = 0; //'Clean60';
		}
		
		//echo '## '.count($tableau[$row]).' ##';
		//echo 'Calculs CleanSheet D : ';
		if(isCleanSheetNul($row,$data[2],$resultatsJournee,$data)){
			$tableau[$row][] = 1; //'Clean60D';
		}else{
			$tableau[$row][] = 0; //'Clean60D';
		}
		
		//echo '## '.count($tableau[$row]).' ##';
		//echo 'Calculs Ecarts But : ';
		//echo '>>>>>>> '.$data[2];
		//echo 'FONCTION =( '.ecartScore($row,$data[2],$resultatsJournee,$data).' )';
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
		
		
		$supprimerStatsAncienne = $bdd->prepare('DELETE FROM joueur_stats WHERE journee = :journee');
		$supprimerStatsAncienne->execute (array('journee' => substr($fichierName,0,6)));
		$supprimerStatsAncienne->closeCursor();
		$premiereLigne=0;
		foreach($tableau as $ligneDeStats)
		{
			if($premiereLigne==0){
				$premiereLigne++;
			}else{
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
			}
		}
		echo 'INSERT des stats De la Journee en BDD => OK';
		$req->closeCursor();
//print_r($tableau[1]);
//var_dump($array);

?>

	<form method="post" action="../admin.php" enctype="multipart/form-data">
		<input type="submit" name="retourAdmin" value="Retour page Admin" />
	</form>

</body>
</html>