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

		echo 'Liste des joueurs non notés en base de données : ';
		echo "<br />\n";
		$req1 = $bdd->query('SELECT id, journee FROM joueur_stats WHERE note IS NULL');
		while ($donnees = $req1->fetch())
		{
			echo $donnees['id'].' pour la journée '.$donnees['journee'];
			echo "<br />\n";
		}
		
		$req1->closeCursor();	
		
		
?>