<html>
<body>

<?php
	session_start();
	$tabIdPrix = $_SESSION['tabIdPrix'];

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
	
	
	$upd_prixJoueurReel = $bdd->prepare('UPDATE joueur_reel SET prix = :prix WHERE id = :id ;');
	
	foreach($tabIdPrix as $ligneIdPrix){
		$upd_prixJoueurReel->execute(array('prix' => $ligneIdPrix[1], 'id' => $ligneIdPrix[0]));
		echo 'Le prix du joueur '.$ligneIdPrix[0].' passe à '.$ligneIdPrix[1];
		echo "<br />\n";
	}
	
	$upd_prixJoueurReel->closeCursor();
	

?>

<form method="post" action="../admin.php" enctype="multipart/form-data">
		<input type="submit" name="retourAdmin" value="Retour page Admin" />
</form>
	
</html>
</body>