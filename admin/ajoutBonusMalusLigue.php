<html>
	<body>
		<form method="post" id="formPrincipal" action="">
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
		
		if (isset($_POST['validerBonus']))
		{
			try
			{
				$req = $bdd->prepare('SELECT id, id_coach FROM equipe WHERE id_ligue = :id');
				$req->execute(['id' => $_POST['choixLigue']]);
			
				$req2 = $bdd->prepare('INSERT INTO bonus_malus (code, id_equipe) VALUES (:code, :id)');
				$req3 = $bdd->prepare('INSERT INTO actualite_coach (id_coach, id_equipe, libelle, date_creation) VALUES (:id_c, :id_e, :lib, NOW())');
			
				while ($donnees = $req->fetch())
				{
					$req2->execute(['code' => $_POST['choixBonus'], 'id' => $donnees['id']]);
					$req3->execute(['id_c' => $donnees['id_coach'], 'id_e' => $donnees['id'], 'lib' => $_POST['libActu']]);
				}
				$req->closeCursor();
				$req2->closeCursor();
				$req3->closeCursor();
			
				echo 'Validation OK : Ligue='.$_POST['choixLigue'].', Bonus='.$_POST['choixBonus'].', LibActu='.$_POST['libActu'];
			}
			catch (Exception $e)
			{
				$message = 'Erreur lors création actualité et bonus pour la ligue '.$_POST['choixLigue'].' : '.$e->getMessage();
				die($message);
				echo $e;
			}
		}
		
		$req = $bdd->prepare('SELECT id, nom FROM ligue WHERE etat = 2 ORDER BY nom');
		$req->execute();

		$optionsLigues = '<option value="-1"></option>';
		while ($donnees = $req->fetch())
		{
			$optionsLigues = $optionsLigues.'<option value="'.$donnees['id'].'">'.$donnees['nom'].'</option>';
		}
		$req->closeCursor();
		
		$req = $bdd->prepare('SELECT code FROM nomenclature_bonus_malus WHERE NOW() > date_debut AND (date_fin IS NULL OR date_fin > NOW()) ORDER BY code');
		$req->execute();
		
		$optionsBonus = '';
		while ($donnees = $req->fetch())
		{
			$optionsBonus = $optionsBonus.'<option value="'.$donnees['code'].'">'.$donnees['code'].'</option>';
		}
		$req->closeCursor();
		
		echo '<div>';
		echo '<div>Ligue : <select name="choixLigue">'.$optionsLigues.'</select></div><br/>';
		echo '<div>Bonus : <select name="choixBonus">'.$optionsBonus.'</select></div><br/>';
		echo '<div>Libellé actu : <textarea rows="4" cols="50" name="libActu"></textarea><br/>';
		echo '<input type="submit" name="validerBonus" value="Ajouter bonus/malus" />';
		echo '</div>';
		
		?>
		</form>
	</body>
</html>