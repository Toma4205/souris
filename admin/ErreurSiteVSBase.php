<html>
<body>

<?php

	function preparerJoueurAEnvoyer($tabInfoJoueur){
		$joueurAInserer[] = $tabInfoJoueur[1].$tabInfoJoueur[2].$tabInfoJoueur[0];
		$joueurAInserer[] = $tabInfoJoueur[2];
		$joueurAInserer[] = $tabInfoJoueur[1];
		$joueurAInserer[] = $tabInfoJoueur[0];
		$joueurAInserer[] = $tabInfoJoueur[4];
		$joueurAInserer[] = $tabInfoJoueur[3];
		$joueurAInserer[] = '';
		
		return $joueurAInserer;
	}

	session_start();
	$erreurs = $_SESSION['erreurs'];

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
	
	$req_liste_joueurEquipe = $bdd->prepare('SELECT id,prenom,nom FROM joueur_reel WHERE equipe = :equipe;');
	$teamRech = '';
	$modifProbable;
	$closestIDTab;
?>					
	<form action="ajoutJoueurReelEnBase.php">
<?php	
	echo '---------------- Liste des Joueurs absents de la base de données ----------------';
	echo "<br />\n";
	echo '============== ATTENTION AUX DOUBLONS ======================';
	echo "<br />\n";
	echo '(avec les accents, les tirets et les guillemets notamment)';
	echo "<br />\n";
	foreach($erreurs as $erreurIndiv){
			$req_liste_joueurEquipe->execute(array('equipe' => $erreurIndiv[0]));
			$teamRech = $erreurIndiv[0];
			$lignesJoueurEquipe = $req_liste_joueurEquipe->fetchAll();
			if(count($lignesJoueurEquipe) > 0) {
				//Ok lequipe existe
				$shortest_lev_nom = 50 ;
				$closestNom = '';
				$max_lenght_nom = strlen($erreurIndiv[1]);
				$closestPrenom = '';
				$closestID ='';
				$coef = 0;
				foreach ($lignesJoueurEquipe as $ligneJoueurEquipe) {
					
					//Nom
					$lev_nom = levenshtein($erreurIndiv[1], $ligneJoueurEquipe['nom']);
					if($shortest_lev_nom > $lev_nom)
					{
						$shortest_lev_nom = $lev_nom;
						$max_lenght_nom = max($max_lenght_nom,strlen($ligneJoueurEquipe['nom'])); 
						$closestNom = $ligneJoueurEquipe['nom'];
						$closestID = $ligneJoueurEquipe['id'];
						$closestPrenom = $ligneJoueurEquipe['prenom'];
					}
				}
				
				if(strlen($closestPrenom)>1){
					$shortest_lev_prenom = levenshtein($erreurIndiv[2], $closestPrenom);
					$coef = ((1-($shortest_lev_nom/$max_lenght_nom)) + (1-($shortest_lev_prenom/max(strlen($erreurIndiv[2]), strlen($closestPrenom)))))/2;
				}else{
					$coef = (1-($shortest_lev_nom/$max_lenght_nom));
				}
				
				//coefficient de similarité à définir
				if($coef > 0.7){
					//echo '--[MODIF EN TABLE A FAIRE] Il est très probable ('.round($coef*100,0).'%) que '.$erreurIndiv[1].' de la source soit '.$closestNom.' (ID = '.$closestID.')  dans la table Joueur_Reel';
					//echo "<br />\n";
					$modifProbable[] = $erreurIndiv;
					$closestIDTab[] = $closestID;
				}else{
					
					$listeErreurCheckBox = implode(",",$erreurIndiv);
					echo '<input type="checkbox" name="listeErreurCheckBox[]" checked = "checked" value="'.$listeErreurCheckBox.'" />';
					echo $erreurIndiv[1].$erreurIndiv[2].$erreurIndiv[0].' | ';
					echo $erreurIndiv[2].' | ';
					echo $erreurIndiv[1].' | ';
					echo $erreurIndiv[0].' | ';
					echo $erreurIndiv[4].' | ';
					echo $erreurIndiv[3].' | ';
					echo '<br/>';
				}
				
			}else{
				echo 'En voulant vérifier les erreurs, nous n\'avons pas trouvé l\'équipe '.$teamRech;
				echo "<br />\n";
			}
			$req_liste_joueurEquipe->closeCursor();
	}
	
?>
		<input type="submit" value="Effectuer ces INSERT en BDD"/>
		</form>	

<?php
	if(!empty($modifProbable){
		echo "<br />\n";
		echo "<br />\n";
		echo '---------------- Liste des Joueurs probablement déjà dans la base de données ----------------';
		echo "<br />\n";
		echo '(le mieux est d\'insérer en cle_roto_secondaire)';
		echo "<br />\n";
		$i = 0;
		
		foreach($modifProbable as $modifProbableIndiv){
			echo 'Il est très probable que '.$modifProbableIndiv[1].$modifProbableIndiv[2].$modifProbableIndiv[0].' de la source corresponde au joueur ayant l\'ID : '.$closestIDTab[$i];
			echo ' | Ajouter '.$modifProbableIndiv[1].$modifProbableIndiv[2].$modifProbableIndiv[0].' en cle_roto_secondaire et vérifier que le prénom est '.$modifProbableIndiv[2];
			echo "<br />\n";
			$i++;
		}
	}
?>
	<form method="post" action="../admin.php" enctype="multipart/form-data">
			<input type="submit" name="retourAdmin" value="Retour page Admin" />
	</form>
	
</html>
</body>