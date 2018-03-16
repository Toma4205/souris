<?php
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


		$req = $bdd->prepare('SELECT * FROM coach');
		$req->execute();
		$upd = $bdd->prepare('UPDATE coach SET mot_de_passe = :mdp WHERE id = :id; ');

		while ($donnees = $req->fetch())
		{
			$upd->execute(array('mdp'=> password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT),'id' => $donnees['id']));
			$upd->closeCursor();
		}
		$req->closeCursor();


?>