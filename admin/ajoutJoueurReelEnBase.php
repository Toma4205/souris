
<?php
	
	$listeErreurCheckBox = $_GET["listeErreurCheckBox"];
	
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
	
	
	$ins_joueurReel = $bdd->prepare('INSERT INTO `joueur_reel` (`id`, `cle_roto_primaire`, `prenom`, `nom`, `equipe`, `position`, `prix`, `cle_roto_secondaire`) VALUES (NULL,:cle_roto_primaire,:prenom,:nom,:equipe,:position,:prix,NULL);');
	
    for ($i=0; $i<count($listeErreurCheckBox); $i++) {
		$valeursAInserer = explode(",",$listeErreurCheckBox[$i]);
		$ins_joueurReel->execute(array('cle_roto_primaire' => $valeursAInserer[1].$valeursAInserer[2].$valeursAInserer[0],'prenom' => $valeursAInserer[2], 'nom' => $valeursAInserer[1], 'equipe' => $valeursAInserer[0], 'position' => $valeursAInserer[4], 'prix' => $valeursAInserer[3]));
		echo 'INSERT de ';
		echo $valeursAInserer[1].$valeursAInserer[2].$valeursAInserer[0].' | ';
		echo $valeursAInserer[2].' | ';
		echo $valeursAInserer[1].' | ';
		echo $valeursAInserer[0].' | ';
		echo $valeursAInserer[4].' | ';
		echo $valeursAInserer[3].' | ';
		echo '  => OK ';		
		echo "<br />";
    
		$ins_joueurReel->closeCursor();
	}
	
?>