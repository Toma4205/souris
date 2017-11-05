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
function buildTableauJournee($journee,$row_resultatFile) {
	if (($resultatFile = fopen("csvResultatsEquipes/resultatsL1.csv", "r")) !== FALSE) {
		$tmp_var=0;
		while (($data_resultatsL1 = fgetcsv($resultatFile, 1000, ";")) !== FALSE) {
			$num_resultats = count($data_resultatsL1);
			for ($c=0; $c < $num_resultats; $c++) {
				
				if($c>0 and $data_resultatsL1[0]==$journee){
					$resultatsJourneeTab[$row_resultatFile][] = str_replace(' ','',$data_resultatsL1[$c]);
					$tmp_var++;
				}
			}
			if($tmp_var!==0){
				$row_resultatFile++;
			}
		}	
			/*
			Exemple format tableau
			$resultatsJourneeTab[] = array('LYO','Dom','3','W','0','MON','Visit','2','L','0');
			$resultatsJourneeTab[] = array('BDX','Dom','1','D','0','NTE','Visit','1','D','0');
			$resultatsJourneeTab[] = array('CAE','Dom','0','L','0','ANG','Visit','2','W','0');
			$resultatsJourneeTab[] = array('LIL','Dom','2','D','0','TRO','Visit','2','D','0');
			$resultatsJourneeTab[] = array('MTP','Dom','2','W','0','NIC','Visit','0','L','0');
			$resultatsJourneeTab[] = array('DIJ','Dom','1','L','0','PSG','Visit','2','W','0');
			$resultatsJourneeTab[] = array('GUI','Dom','2','W','0','REN','Visit','0','L','0');
			$resultatsJourneeTab[] = array('ETI','Dom','3','W','0','MET','Visit','1','L','0');
			$resultatsJourneeTab[] = array('STR','Dom','3','D','0','MAR','Visit','3','D','0');
			$resultatsJourneeTab[] = array('TOU','Dom','1','W','0','AMN','Visit','0','L','0');*/
	

	fclose($resultatFile);
	//print_r($resultatsJourneeTab);
	return $resultatsJourneeTab;
	}
}

$resultatsJournee = buildTableauJournee(substr($fichierName,0,6),0);
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

//Création fichier csv
$chemin = 'csvStatsJoueurs/import'.$fichierName;
$delimiteur = ';';
$fichier_csv = fopen($chemin, 'w+');
fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));
foreach($tableau as $ligne){
	// chaque ligne en cours de lecture est insérée dans le fichier
	// les valeurs présentes dans chaque ligne seront séparées par $delimiteur
	fputcsv($fichier_csv, $ligne, $delimiteur);
}

// fermeture du fichier csv
fclose($fichier_csv);

echo 'Le fichier import '.$fichierName.' a été créé avec succès';
//print_r($tableau[1]);
//var_dump($array);

?>

	<form method="post" action="../admin.php" enctype="multipart/form-data">
		<input type="submit" name="retourAdmin" value="Retour page Admin" />
	</form>

</body>
</html>