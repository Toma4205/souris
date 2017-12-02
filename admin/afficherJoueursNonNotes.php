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

		echo 'Requêtes pour ajouter les joueurs manquants en base de données : ';
		echo "<br />\n";
		echo 'ATTENTION : Avant de faire un INSERT, s\'assurer que le calcul des notes a été tenté';
		echo "<br />\n";
		echo 'ATTENTION : vérifier les informations Nom et Prénom';
		echo "<br />\n";
		echo 'ATTENTION : ne pas modifier la clé roto primaire';
		echo "<br />\n";
		echo 'ATTENTION : choisir la position et le prix';
		echo "<br />\n";
		
		$req1 = $bdd->query('SELECT id, journee FROM joueur_stats WHERE note IS NULL');
		while ($donnees = $req1->fetch())
		{
			//echo $donnees['id'].' pour la journée '.$donnees['journee'];
			echo "<br />\n";
			echo 'INSERT INTO `joueur_reel` (`id`, `cle_roto_primaire`, `prenom`, `nom`, `equipe`, `position`, `prix`, `cle_roto_secondaire`)
				VALUES (
					NULL,
					\''.$donnees['id'].'\',
					\''.substr((string)$donnees['id'], strlen(substr((string)$donnees['id'], 0, 1+strcspn(substr($donnees['id'],1,strlen($donnees['id'])), 'ABCDEFGHJIJKLMNOPQRSTUVWXYZ'))), strlen($donnees['id'])-3-strlen(substr((string)$donnees['id'], 0, 1+strcspn(substr($donnees['id'],1,strlen($donnees['id'])), 'ABCDEFGHJIJKLMNOPQRSTUVWXYZ')))).'\',
					\''.substr((string)$donnees['id'], 0, 1+strcspn(substr($donnees['id'],1,strlen($donnees['id'])), 'ABCDEFGHJIJKLMNOPQRSTUVWXYZ')).'\',
					\''.substr((string)$donnees['id'],strlen($donnees['id'])-3,strlen($donnees['id'])).'\',
					\'Goalkeeper/Defender/MidFielder/Forward\',
					10,
					NULL);';
		}
		
		$req1->closeCursor();	
		
		
?>