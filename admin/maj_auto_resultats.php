<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" >
</head>
<body>

<?php


function CallAPI()
{
	
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
	
	$req_derniere_journee = $bdd->prepare('SELECT IF(count(journee)<10,journee,journee+1) AS \'next_resultat_a_saisir\' FROM resultatsl1_reel WHERE butDomicile IS NOT NULL AND butVisiteur IS NOT NULL AND journee IN (SELECT MAX(rr.journee) FROM resultatsl1_reel rr)  GROUP BY journee;');
	
	
	// ------------------------------------------------------------------------
	// ------------ Partie 1 --------------------------------------------------
	// ------------ Récupération des données sur le web -----------------------

	$adresseMaxi = 'http://www.maxifoot.fr/resultat-ligue-1.htm';
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,$adresseMaxi);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
	$query = curl_exec($curl_handle);
	curl_close($curl_handle);
	
	$debutTableau = 'id=lastres';
	$pos1 = strstr($query, $debutTableau);
	$finTableau = 'Tous les r';
	$pos2 = strpos($pos1, $finTableau	);
	
	$journeeRecherchee=27; //EN TEST
	//EN PROD
	/*
	$req_derniere_journee->exectute();
	$prochaines_journees = $req_derniere_journee->fetchAll();
	if(count($prochaines_journees) == 1) {
		//Ok on a trouvé LA journee
		foreach ($prochaines_journees as $prochaine_journee) {
			$journeeRecherchee = substr($prochaine_journee['next_resultat_a_saisir'],-2);
		}
	}
	$req_derniere_journee->closeCursor();	
	*/
	
	//Variables de l'algo
	$journee;
	$resultats; // Tableau contenant pour chaque ligne : ville_dom, nb_but_dom, nb_but_dom_sur_penalty, nb_but_ext, ville_ext, nb_but_ext_sur_penalty
	$buteurs; //Tableau contenant pour chaque ligne : ville, nom_buteur
	$nb_buteurs=0; 
	$i_match = 0;
	
		$lignes = explode("&lt;tr", htmlspecialchars(substr($pos1,0,$pos2),ENT_HTML401,'ISO-8859-1', true));
		$i = 0;
		foreach ($lignes as $ligne) { 
			$j=0;
			//echo $ligne;
			$lignes1 = explode("&lt;td",$ligne);
			foreach ($lignes1 as $ligne1) { 
				$k=0;
				if($j == 4 || $j == 2){
					$nb_penalty = 0;
				}
				$lignes2 = explode("htm\"&gt;",$ligne1);
				foreach ($lignes2 as $ligne2) {
					$m=0;
					$lignes3 = explode("&lt;/a",$ligne2);
					foreach ($lignes3 as $ligne3) {
						$n=0;
						$lignes4 = explode("br&gt;&lt;span class",$ligne3);
						foreach ($lignes4 as $ligne4) {
							$p=0;
							$lignes5 = explode("&gt;",$ligne4);
							foreach ($lignes5 as $ligne5) {
								//Debug
								echo 'Ligne '.$i.'|'.$j.'|'.$k.'|'.$m.'|'.$n.'|'.$p.' '.$ligne5;
								echo "<br />\n";
								
								if($i>=1 && $j==2 && $k==0 && $m==0 && $n==0&& $p==1 && strpos($ligne5,"journ")>0){ //JOURNEE
									if($journee > substr($ligne5,0,2)){
									
									}else{
										$journee = substr($ligne5,0,2);
									}
									echo 'journee : '.$journee;
								}
								
								if($i>1 && $j==2 && $k==1 && $m==0 && $n==0&& $p==0 && $journee == $journeeRecherchee){ // EQUIPE DOM
									$resultats[$i_match][]=$ligne5;
								}
								
								if($i>1 && $j==2 && $k==1 && $m==1 && $n==1 && $p>=1 && $journee == $journeeRecherchee){ //CALCUL PENALTY DOM
									$pos = strpos($ligne5,", sp");
									$cibletexte = $ligne5;
									if ($pos === false) {
										//pas de penalty
										$pos = strpos($ligne5,", csc");
										if ($pos === false) {
											//pas de csc
										}else{
											$cibletexte = substr($ligne5,0,$pos);
										}
									}else{
										$nb_penalty++;
										$cibletexte = substr($ligne5,0,$pos);
										
									}
									$pos = strpos($ligne5,"'&lt;"); //COLLECTE BUTEUR
									$pos1 = strpos($ligne5,"', sp&lt;"); //COLLECTE BUTEUR penalty
									$pos2 = strpos($ligne5,"', csc&lt;"); //COLLECTE BUTEUR CSC
									if ($pos === false && $pos1 === false && $pos2 === false) {
										//pas de penalty
									}else{
										//echo 'penalty';
										echo 'cibletexte '.$cibletexte;
										$posEspace = strrpos($cibletexte," "); 
										$lastRow = end($resultats);
										$buteurs[$nb_buteurs][0]= $lastRow[0];
										$buteurs[$nb_buteurs][1]= substr($ligne5,0,$posEspace);
										$nb_buteurs++;
									}
									
									
								}
								
								if($i>1 && $j==4 && $k==1 && $m==1 && $n==1 && $p>=1 && $journee == $journeeRecherchee){ //CALCUL PENALTY EXT
									$pos = strpos($ligne5, ", sp");
									$cibletexte = $ligne5;
									if ($pos === false) {
										//pas de penalty
										$pos = strpos($ligne5,", csc");
										if ($pos === false) {
											//pas de csc
										}else{
											$cibletexte = substr($ligne5,0,$pos);
										}
									}else{
										$nb_penalty++;
										$cibletexte = substr($ligne5,0,$pos);
									}
									$posSpan = strpos($ligne5, "span");
									if ($posSpan === false) {
										//pas de penalty
									}else{
										$resultats[$i_match][]=$nb_penalty;
									}
									$pos = strpos($ligne5,"'&lt;"); //COLLECTE BUTEUR
									$pos1 = strpos($ligne5,"', sp&lt;"); //COLLECTE BUTEUR penalty
									$pos2 = strpos($ligne5,"', csc&lt;"); //COLLECTE BUTEUR CSC
									if ($pos === false && $pos1 === false && $pos2 === false) {
										//pas de penalty
									}else{
										$posEspace = strrpos($cibletexte," "); 
										$lastRow = end($resultats);
										$buteurs[$nb_buteurs][0]= $lastRow[4];
										$buteurs[$nb_buteurs][1]= substr($ligne5,0,$posEspace);
										$nb_buteurs++;
									}
								}
								
								if($i>1 && $j==3 && $k==1 && $m==0 && $n==0&& $p==0 && $journee == $journeeRecherchee){ //SCORE DOM et EXT (ajouts penalty DOM)
									$resultats[$i_match][]=substr($ligne5,0,1);
									$resultats[$i_match][]=$nb_penalty;
									$resultats[$i_match][]=substr($ligne5,-1);
								}
								if($i>1 && $j==4 && $k==1 && $m==0 && $n==0&& $p==0 && $journee == $journeeRecherchee){
									$resultats[$i_match][]=$ligne5;
								}							
								
								$p++;
							}
							$n++;
						}
						$m++;
					}
					$k++;
					
				}
				$j++;
			}
			$i++;
			if($n>1){$i_match++;}			
		}
		
	echo 'Resultat de la dernière journée n°'.$journee;
	echo "<br />\n";
	foreach($resultats as $rencontre){
		if($rencontre[1]>$rencontre[3]){
			$statusDOM = 'W';
			$statusEXT = 'L';
		}elseif($rencontre[1]<$rencontre[3]){
			$statusDOM = 'L';
			$statusEXT = 'W';
		}else{
			$statusDOM = 'D';
			$statusEXT = 'D';
		}
		echo '2017'.$journee.' | '.$rencontre[0].' | '.$statusDOM.$rencontre[1].'('.$rencontre[2].' sp) - '.$rencontre[3].' ('.$rencontre[5].' sp) '.$statusEXT.' | '.$rencontre[4];
		echo "<br />\n";
	}
	
	echo 'Buteur de la journée n°'.$journee;
	echo "<br />\n";
	foreach($buteurs as $buteur){
		echo $buteur[0].' - '.$buteur[1];
		echo "<br />\n";
	}
	
	// ------------------------------------------------------------------------
	// ------------ Partie 2 --------------------------------------------------
	// ------------ Création des requêtes pour insertion en base --------------
	
	//Pour chaque confrontation, on vérifie qu'elle n'est pas déjà présente
	//Si pas présente, proposition de l'INSERT en base
	$req_rencontre_deja_saisie = $bdd->prepare('SELECT journee, equipeDomicile, butDomicile, winOrLoseDomicile, penaltyDomicile, equipeVisiteur, butVisiteur, winOrLoseVisiteur, penaltyVisiteur FROM resultatsl1_reel WHERE journee = :journee AND equipeDomicile IN (SELECT ner1.trigramme FROM nomenclature_equipes_reelles ner1 WHERE ner1.ville_maxi = :ville1) AND equipeVisiteur IN (SELECT ner2.trigramme FROM nomenclature_equipes_reelles ner2 WHERE ner2.ville_maxi = :ville2);');
	
	foreach($resultats as $rencontre)
	{
		$req_rencontre_deja_saisie->execute(array('journee' => '2017'.$journee, 'ville1' => $rencontre[0], 'ville2' => $rencontre[4]));
		$rencontreSimilaire = $req_rencontre_deja_saisie->fetchAll();
		if (count($rencontreSimilaire) > 1) {
			//Erreur, il ne doit y avoir deux lignes d'une même rencontre
			echo 'Erreur il ne doit y avoir deux lignes d\'une même rencontre : '.$rencontreSimilaire['equipeDomicile'].' vs '.$rencontreSimilaire['equipeVisiteur'].' sur la journée '.$rencontreSimilaire['journee'];
			echo "<br />\n";
		}elseif(count($rencontreSimilaire) == 1){
			//La rencontre est déjà saisie, on vérifie si le résultat est différent
			foreach ($rencontreSimilaire as $larencontreSimilaire) {
				if($rencontre[1] == $larencontreSimilaire['butDomicile'] && $rencontre[2] == $larencontreSimilaire['penaltyDomicile']  && $rencontre[3] == $larencontreSimilaire['butVisiteur'] && $rencontre[5] == $larencontreSimilaire['penaltyVisiteur'])
				{
					//Le résultat est similaire
					echo 'Ligne déjà présente : '.$larencontreSimilaire['equipeDomicile'].' vs '.$larencontreSimilaire['equipeVisiteur'].' sur la journée '.$larencontreSimilaire['journee'];
					echo "<br />\n";
				}else{
					echo 'INSERT INTO resultatsl1_reel(journee,equipeDomicile,homeDomicile,butDomicile,winOrLoseDomicile,penaltyDomicile,equipeVisiteur,homeVisiteur,butVisiteur,WinOrLoseVisiteur,penaltyVisiteur) VALUES(2017'.$journee.','.$larencontreSimilaire['equipeDomicile'].',Dom,'.$rencontre[1].','.$statusDOM.','.$rencontre[2].','.$larencontreSimilaire['equipeVisiteur'].',Visit,'.$rencontre[3].','.$statusEXT.','.$rencontre[5].');';
					echo "<br />\n";
				}
			}	
		}else{
			//Rencontre non saisie : GO pour INSERT
			
			if($rencontre[1]>$rencontre[3]){
				$statusDOM = 'W';
				$statusEXT = 'L';
			}elseif($rencontre[1]<$rencontre[3]){
				$statusDOM = 'L';
				$statusEXT = 'W';
			}else{
				$statusDOM = 'D';
				$statusEXT = 'D';
			}
			
			$req_trigramme = $bdd->prepare('SELECT trigramme FROM nomenclature_equipes_reelles WHERE ville_maxi = :ville_maxi;');
			$req_trigramme->execute(array('ville_maxi' => $rencontre[0]));
			$trigrammes_obtenus = $req_trigramme->fetchAll();
			
			foreach ($trigrammes_obtenus as $trigramme_obtenu) {
				$trigrammeDom = $trigramme_obtenu['trigramme'];
			}
			$req_trigramme->closeCursor();
			
			$req_trigramme->execute(array('ville_maxi' => $rencontre[4]));
			$trigrammes_obtenus = $req_trigramme->fetchAll();
			foreach ($trigrammes_obtenus as $trigramme_obtenu) {
				$trigrammeExt = $trigramme_obtenu['trigramme'];
			}
			$req_trigramme->closeCursor();
			
			echo 'INSERT INTO resultatsl1_reel(journee,equipeDomicile,homeDomicile,butDomicile,winOrLoseDomicile,penaltyDomicile,equipeVisiteur,homeVisiteur,butVisiteur,WinOrLoseVisiteur,penaltyVisiteur) VALUES(\'2017'.$journee.'\',\''.$trigrammeDom.'\',\'Dom\','.$rencontre[1].',\''.$statusDOM.'\','.$rencontre[2].',\''.$trigrammeExt.'\',\'Visit\','.$rencontre[3].',\''.$statusEXT.'\','.$rencontre[5].');';
			echo "<br />\n";
		}
		$req_rencontre_deja_saisie->closeCursor();
	}
	
?>

</body>
</html>