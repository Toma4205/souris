<?php
	
	
	// ------------------------------------------------------------------------
	// ------------ Partie 1 --------------------------------------------------
	// ------------ Récupération des données sur le web -----------------------
	
	//URL constantes
	$adressPart1 = 'https://www.transfermarkt.fr/';
	$adressPart2 = '/startseite/verein/';
	$saison_id = '/saison_id/2017';
	
	
	//----------------Initialisation des urls équipes 
	$team[] = 'fc-paris-saint-germain';
	$idTeam[] = '583';
	$teamAbrev[] = 'PSG';
	$team[] = 'as-monaco';
	$idTeam[] = '162';
	$teamAbrev[] = 'MON';
	$team[] = 'olympique-lyon';
	$idTeam[] = '1041';
	$teamAbrev[] = 'LYO';
	$team[] = 'olympique-marseille';
	$idTeam[] = '244';
	$teamAbrev[] = 'MAR';
	$team[] = 'fc-nantes';
	$idTeam[] = '995';
	$teamAbrev[] = 'NTE';
	$team[] = 'ogc-nizza';
	$idTeam[] = '417';
	$teamAbrev[] = 'NIC';
	$team[] = 'hsc-montpellier';
	$idTeam[] = '969';
	$teamAbrev[] = 'MTP';
	$team[] = 'ea-guingamp';
	$idTeam[] = '855';
	$teamAbrev[] = 'GUI';
	$team[] = 'fc-stade-rennes';
	$idTeam[] = '273';
	$teamAbrev[] = 'REN';
	$team[] = 'fco-dijon';
	$idTeam[] = '2969';
	$teamAbrev[] = 'DIJ';
	$team[] = 'racing-strassburg';
	$idTeam[] = '667';
	$teamAbrev[] = 'STR';
	$team[] = 'sm-caen';
	$idTeam[] = '1162';
	$teamAbrev[] = 'CAE';
	$team[] = 'sc-amiens';
	$idTeam[] = '1416';
	$teamAbrev[] = 'AMN';
	$team[] = 'es-troyes-ace';
	$idTeam[] = '1087';
	$teamAbrev[] = 'TRO';
	$team[] = 'fc-girondins-bordeaux';
	$idTeam[] = '40';
	$teamAbrev[] = 'BDX';
	$team[] = 'as-saint-etienne';
	$idTeam[] = '618';
	$teamAbrev[] = 'ETI';
	$team[] = 'fc-toulouse';
	$idTeam[] = '415';
	$teamAbrev[] = 'TOU';
	$team[] = 'osc-lille';
	$idTeam[] = '1082';
	$teamAbrev[] = 'LIL';
	$team[] = 'sco-angers';
	$idTeam[] = '1420';
	$teamAbrev[] = 'ANG';
	$team[] = 'fc-metz';
	$idTeam[] = '347';
	$teamAbrev[] = 'MET';
	
	
	
	$joueursInfo ;
	
	//Boucle pour chaque équipe
	$i = 0;
	$j=0;
	while($i < count($team))
	{
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$adressPart1.$team[$i].$adressPart2.$idTeam[$i].$saison_id);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
		$query = curl_exec($curl_handle);
		curl_close($curl_handle);
		
		//Affiche la page en brute
		//echo htmlspecialchars($query);

		$debutTableau = '<table class="items">';
		$pos1 = stripos($query, $debutTableau);
		$finTableau = 'Effectif detaillé';
		$pos2 = stripos($query, $finTableau);
		
		//echo htmlspecialchars(substr($query,$pos1,$pos2-$pos1));
		$lignes = explode("posrela", htmlspecialchars(substr($query,$pos1,$pos2-$pos1)));
		
		foreach ($lignes as $ligne) {
			$pos3 = stripos($ligne,'athlete');
			if($pos3>0){
				$decoupage1 = substr($ligne,$pos3,strlen($ligne)-$pos3);
				$pos3 = stripos(htmlspecialchars($decoupage1),'td');
				$prenomNomJoueur = substr(htmlspecialchars($decoupage1),25,$pos3-34);
				
				$nom = substr($prenomNomJoueur,stripos($prenomNomJoueur,' '),strlen($prenomNomJoueur)-stripos($prenomNomJoueur,' '));
				$prenom = substr($prenomNomJoueur,0,strlen($prenomNomJoueur)-strlen($nom));

				$pos4 = stripos(htmlspecialchars($decoupage1),'zentriert');
				$pos3 = stripos(htmlspecialchars($decoupage1),'(');
				$anniversaire = substr(htmlspecialchars($decoupage1),$pos4+27,$pos3-$pos4-28);
				
				$pos3 = stripos(htmlspecialchars($decoupage1),'alt',100);
				$pos4 = stripos(htmlspecialchars($decoupage1),'flaggenrahmen');
				$pays = substr(htmlspecialchars($decoupage1),$pos3+14,$pos4-$pos3-14-27);
				
				$pos3 = stripos(htmlspecialchars($decoupage1),'hauptlink');
				$pos4 = stripos(htmlspecialchars($decoupage1),'€');
				$valeurBrute = substr(htmlspecialchars($decoupage1),$pos3+27,$pos4-$pos3-27);
				
				//Vérification si la valeur est en Million ou en Millier
				$pos3 = stripos($valeurBrute,'K');
				if($pos3>0){
					//On est en K€
					$valeur = substr($valeurBrute,0,$pos3-1);
				}else{
						$pos3 = stripos($valeurBrute,'mio');
						if($pos3>0){
							//On est en M€
							$valeur = substr($valeurBrute,0,$pos3-1)*1000;
						}else{
							//Si on arrive ici alors erreur
							//echo 'Ni K ni M?';
							$valeur = 0;
						}	
				}
				
				$joueursInfo[$j][] = $teamAbrev[$i];
				$joueursInfo[$j][] = $prenomNomJoueur;
				$joueursInfo[$j][] = $nom;
				$joueursInfo[$j][] = $prenom;
				$joueursInfo[$j][] = $anniversaire;
				$joueursInfo[$j][] = $pays;
				$joueursInfo[$j][] = $valeur;
				$j++;
				//echo $prenomNomJoueur.';'.$anniversaire.';'.$pays.';'.$valeur;
				//echo "<br />\n";
			}
		}
		$i++;
	}
	
	// ------------------------------------------------------------------------
	// ------------ Partie 2 --------------------------------------------------
	// ------------ Update de la base de données ------------------------------
	
	foreach($joueursInfo as $joueurInfo){
		
	}
	
	print_r($joueursInfo);
	echo "<br />\n";
	


?>