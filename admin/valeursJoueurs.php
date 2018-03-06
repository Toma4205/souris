<html>
<body>

<?php
	
	set_time_limit(500);
	
	function algoCalculValeurJoueur($valeur)
	{
		//Constantes
		$moyenneValeurWeb = 3841.23314;
		$c1 = 1000;
		$c2 = 2;
		$c3 = 40;
		$c4 = 103;
		$c5 = 10;
		$c6 = 800;
		$c7 = 15000;
		
		//Variable
		$prix = 0;
		$sous_prix1 = 0;
		$sous_prix2 = 0;
		$sous_prix3 = 0;
		
		$sous_prix1 = log(log($valeur/$c1+1)+1)*$c2;
		
		$sous_prix2 = (pow($valeur+1,1/$c3)-1)*$c4-$c5;
		if($sous_prix2 < 0){ $sous_prix2 = 1;}
		
		if($valeur - $c6 >= 0)
		{
			$sous_prix3 = $valeur/$c7;
		}
				
		$prix = $sous_prix1 + $sous_prix2 + $sous_prix3 - 3;
		if($prix <= 0){$prix = 1;}
		
		return round($prix);
	}
	
	
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
	$idTeam[] = '1095';
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
		$postePrec = '';
		
		foreach ($lignes as $ligne) {
			
			
			//Trouve le poste du joueur
			if(stripos($ligne,'odd') > 0){
				$pos5 = stripos($ligne,'odd');
			}else{
				$pos5 = stripos($ligne,'even');
				//Traitement de l'exception d'une personne rEVENu de prêt
				if(substr($ligne,$pos5-1,1) == 'R'){
					$pos5 = stripos($ligne,'even',$pos5+1000);
				}elseif(substr($ligne,$pos5-1,1) == 't'){ 
					//Traitement de l'exception d'une personne nommée stEVEN
					$pos5 = stripos($ligne,'even',strlen($ligne)-400);
				}
			}
			
			$decoupageInt = substr($ligne,$pos5,strlen($ligne)-$pos5);
			$pos6 = stripos($decoupageInt,'title');
			$pos7 = stripos($decoupageInt,'class');
			$posteSuivant = substr($decoupageInt,$pos6+strlen('title')+7,4);
			
			if($posteSuivant=='Gard'){
				$posteSuivant = 'Goalkeeper';
			}elseif($posteSuivant=='Déf'){
					$posteSuivant = 'Defender';
			}elseif($posteSuivant=='Mili'){
					$posteSuivant = 'Midfielder';
			}elseif($posteSuivant=='Atta'){	
					$posteSuivant = 'Forward';
			}else{
					$posteSuivant = '';
			}
			
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
				$joueursInfo[$j][] = $postePrec;
				$j++;
				//echo $teamAbrev[$i].';'.$prenomNomJoueur.';'.$nom.';'.$prenom.';'.$anniversaire.';'.$pays.';'.$valeur.';'.$postePrec;
				//echo "<br />\n";
			}
			$postePrec = $posteSuivant;
		}
		$i++;
	}
	
	// ------------------------------------------------------------------------
	// ------------ Partie 2 --------------------------------------------------
	// ------------ Vérification et Update de la base de données ------------------------------
	
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
	
	$req_joueurReel = $bdd->prepare('SELECT id,cle_roto_primaire,prix FROM joueur_reel WHERE equipe = :equipe AND (((cle_roto_primaire LIKE :cle_roto_primaire OR cle_roto_secondaire LIKE :cle_roto_secondaire) AND prenom LIKE :prenom) OR (prenom = \'\' AND nom = :nom)) ;');
	
	//$upd_prixJoueurReel = $bdd->prepare('UPDATE joueur_reel SET prix = :prix WHERE id = :id ;');
	
	$erreurs;
	$i = 0;
	$tabIdPrix;
	$k = 0;
	echo '---------------- Recherche MAJ prix et detection des Erreurs / Multiples ----------------';
	echo "<br />\n";
	foreach($joueursInfo as $joueurInfo){
		
		if(stripos(trim($joueurInfo[2]),' ')>0)
		{
			$joueurInfo[2] = substr(trim($joueurInfo[2]),stripos(trim($joueurInfo[2]),' '),strlen(trim($joueurInfo[2]))-stripos(trim($joueurInfo[2]),' '));
		}
		
		$req_joueurReel->execute(array('equipe' => $joueurInfo[0], 'cle_roto_primaire' => '%'.trim($joueurInfo[2]).'%', 'prenom' => '%'.trim($joueurInfo[3]).'%', 'cle_roto_secondaire' => '%'.trim($joueurInfo[2]).'%', 'nom' => trim($joueurInfo[1])));
				
		$lignesJoueurReel = $req_joueurReel->fetchAll();
			if(count($lignesJoueurReel) == 1) {
				//Ok on a trouvé LE joueur qui correspond
				foreach ($lignesJoueurReel as $ligneJoueurReel) {
					$nouveauPrix = algoCalculValeurJoueur($joueurInfo[6]);
					if($nouveauPrix != $ligneJoueurReel['prix']){
						$tabIdPrix[$k][] = $ligneJoueurReel['id'];
						$tabIdPrix[$k][] = $nouveauPrix;
						$k++;
						//$upd_prixJoueurReel->execute(array('prix' => $nouveauPrix, 'id' => $ligneJoueurReel['id']));
						//echo 'Le prix de '.$ligneJoueurReel['cle_roto_primaire'].' passe de '.$ligneJoueurReel['prix'].' à '.$nouveauPrix;
						//echo "<br />\n";
					
					}
				}
			}else{
				if(count($lignesJoueurReel) == 0){
					//Aucun joueur trouvé
					//echo 'ERREUR : joueur inconnu : '.$joueurInfo[0].' '.$joueurInfo[1].' le nom : '.$joueurInfo[2].' le prénom : '.$joueurInfo[3];
					//echo "<br />\n";
					$erreurs[$i][] = $joueurInfo[0];
					$erreurs[$i][] = $joueurInfo[2];
					$erreurs[$i][] = $joueurInfo[3];
					$erreurs[$i][] = algoCalculValeurJoueur($joueurInfo[6]);
					$erreurs[$i][] = ( strlen($joueurInfo[7])>1 ? $joueurInfo[7] : '\'Goalkeeper/Defender/MidFielder/Forward\'');
					$i++;
					
				}else{
					//Plusieurs joueurs trouvés
					echo 'MULTIPLE EN BASE : il y a plusieurs : '.$joueurInfo[0].' '.$joueurInfo[1].' le nom : '.$joueurInfo[2];
					echo "<br />\n";
				}
			}
		
		//$upd_prixJoueurReel->closeCursor();
		$req_joueurReel->closeCursor();
		//algoCalculValeurJoueur($joueurInfo[6]);
	}
	
	session_start();
	if(empty($tabIdPrix)){
		echo 'Aucun prix de joueur à mettre à jour';
		echo "<br />\n";
	}else{
		echo 'Il y a '.count($tabIdPrix).' prix de joueurs à mettre à jour';
		echo "<br />\n";

		$_SESSION['tabIdPrix'] = $tabIdPrix;
?>
	<form method="post" id="MAJPrixJoueur" action="MAJPrixJoueur.php" enctype="multipart/form-data">
		 <input type="submit" name="submit" value="Mettre à jour les valeurs des joueurs" />
	</form>
	
<?php	
	}
	
	
	
	if(empty($erreurs)){
		echo 'Tous les joueur du site web analysé ont trouvé leur correspondance en base de données';
		echo "<br />\n";
	}else{
		echo 'Il y a '.count($erreurs).' joueurs sur le site web SANS correspondance en base de données';
		echo "<br />\n";
		
		$_SESSION['erreurs'] = $erreurs;
?>
	<form method="post" id="ErreurSiteVSBase" action="ErreurSiteVSBase.php" enctype="multipart/form-data">
		 <input type="submit" name="submit" value="Afficher les erreurs" />
	</form>
	
<?php
	
	}
?>

</body>
</html>