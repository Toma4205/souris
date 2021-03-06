<?php

if (isset($_POST['lancerCron']))
{
	include_once(__DIR__ .'/../testCRON.php');
} elseif (isset($_POST['initJournee']))
{
	include_once('initDebutJournee.php');
} elseif (isset($_POST['majScoreJourneeEnCours']))
{
	include_once('majScoreJourneeEnCours.php');
} elseif (isset($_POST['majMoyenneJoueur']))
{
	include_once('majMoyenneJoueur.php');
} elseif (isset($_POST['majFinLigue']))
{
	include_once('majFinLigue.php');
} elseif (isset($_POST['majResultatsL1']))
{
	include_once('MAJResultatL1.php');
} elseif (isset($_POST['importerStatsBDD']))
{
	include_once('importJourneeBDD.php');
}

function afficherLog()
{
    date_default_timezone_set('Europe/Paris');
	$year_month = date("YF");
	$fichier = 'logs/'.$year_month.'.log';
	
    //$file = './admin/logs/2018March.log';
    $data = file_get_contents($fichier);
    $newdata = explode("\n", $data); //create array separate by new lines
    rsort($newdata); 
    foreach($newdata as $line){ 
        echo $line."\n"; 
    }
    
    return $fichier;
}

?>
<html>
<body>
	<h1>Page Administrateur</h1>
		<p>
			<a class="bouton" href="#titreLancerCron">Lancer CRON</a>
		</p>
		<p>
			<a class="bouton" href="#titreScrapValeur">Vérification Prix des Joueurs</a>
		</p>
		<p>
			<a class="bouton" href="#titreMAJAutoResultatsEquipe">MAJ Auto des résultats des équipes</a>
		</p>
		<p>
			<a class="bouton" href="#titreImportAutoStatsJournee">Import Auto des stats de la journée</a>
		</p>
		<p>
			<a class="bouton" href="#titreMAJResultatsEquipe">Mettre à jour les résultats des équipes</a>
		</p>
		<p>
			<a class="bouton" href="#titreImportCSV">Importer les statistiques des joueurs à partir du CSV</a>
		</p>
		<p>
			<a class="bouton" href="#titreCalculDesNotes">Calcul des notes</a>
		</p>
		<p>
			<a class="bouton" href="#titreCalculerLesConfrontations">Calculer les Confrontations</a>
		</p>
		<p>
			<a class="bouton" href="#titreInitDebutJournee">Initialiser le début de journée</a>
		</p>
		<p>
			<a class="bouton" href="#titreMajScoreJourneeEnCours">Maj buteurs/scores journée en cours</a>
		</p>
		<p>
			<a class="bouton" href="#titreMajMoyenneJoueur">Maj des moyennes des joueurs</a>
		</p>
		<p>
			<a class="bouton" href="#titreMajFinLigue">Maj fin ligue</a>
		</p>
		<p>
			<a class="bouton" href="#titreAjouterBonusLigue">Ajouter bonus/malus ligue</a>
		</p>

	<HR size=2 align=center width="100%">
	<h2 id="titreLancerCron">Lancer CRON</h2>
	<form method="post" id="lancerCron" action="">
			<input type="submit" name="lancerCron" value="J'me lance" />
			<br/>
			<?php 
			    echo '<textarea style="width:900px;height:400px;" name="fichierLog">';
			    $fichier = afficherLog(); 
			    echo '</textarea><br/>';
			    echo '<div>Fichier de log : ' . $fichier . '</div>';
			?>
	</form>

	<HR size=2 align=center width="100%">
	<h2 id="titreScrapValeur">Vérification Prix des Joueurs</h2>
	<form method="post" id="scrapValeur" action="valeursJoueurs.php" enctype="multipart/form-data">
		 <input type="submit" name="submit" value="Etudier les valeurs" />
	</form>

	<HR size=2 align=center width="100%">
	<h2 id="titreMAJAutoResultatsEquipe">MAJ Auto des résultats des équipes (encore en test)</h2>
	<form method="post" id="MAJAutoResultatsEquipe" action="maj_auto_resultats.php" enctype="multipart/form-data">
		 <input type="submit" name="submit" value="Mettre à jour les résultats" />
	</form>

	<HR size=2 align=center width="100%">
	<h2 id="titreImportAutoStatsJournee">Import Auto des stats de la journée (encore en test)</h2>
	<form method="post" id="importAutoStatsJournee" action="import_auto_stats.php" enctype="multipart/form-data">
		<select name="idJournee" size=1>
				<option>201721
				<option>201722
				<option>201723
				<option>201724
				<option>201725
				<option>201726
				<option>201727
				<option>201728
				<option>201729
				<option>201730
				<option>201731
				<option>201732
				<option>201733
				<option>201734
				<option>201735
				<option>201736
				<option>201737
				<option>201738
			  </select>
		<input type="submit" name="submit" value="Import Auto des stats de la journée" />
	</form>

	<HR size=2 align=center width="100%">
	<h2 id="titreMAJResultatsEquipe">Mettre à jour les résultats des équipes</h2>
	<form method="post" id="MAJResultatsEquipe" action="" enctype="multipart/form-data">

		<?php
		if (isset($messageMajScore))
		{
			echo '<p>' . $messageMajScore . '</p>';
		}
		 ?>

		 <label for="Ajout">Ajouter des résultats de Ligue 1 :</label><br />
		 <table border="1">
		 <tbody>
		 <tr align=CENTER>
		 <td>
		 <label for="Ajout">N° de Journée :</label><br />
		 </td>
		 <td>
		 <label for="Ajout">Equipe Domicile :</label><br />
		 </td>
		 <td>
		 <label for="Ajout">But Domicile :</label><br />
		 </td>
		 <td>
		 <label for="Ajout">Penalty Domicile :</label><br />
		 </td>
		 <td>
		 <label for="Ajout">Equipe Visiteur :</label><br />
		 </td>
		 <td>
		 <label for="Ajout">But Visiteur :</label><br />
		 </td>
		 <td>
		 <label for="Ajout">Penalty Visiteur :</label><br />
		 </td>
		 <tr align=CENTER>
		 <td>
		 <select name="idJournee" size="4" style="height:200px">
				<option>201711
				<option>201712
				<option>201713
				<option>201714
				<option>201715
				<option>201716
				<option>201717
				<option>201718
				<option>201719
				<option>201720
				<option>201721
				<option>201722
				<option>201723
				<option>201724
				<option>201725
				<option>201726
				<option>201727
				<option>201728
				<option>201729
				<option>201730
				<option>201731
				<option>201732
				<option>201733
				<option>201734
				<option>201735
				<option>201736
				<option>201737
				<option>201738
			  </select>
			</td>
			<td>
			  <select name="teamDom" size="4" style="height:200px">
				<option>AMN
				<option>ANG
				<option>BDX
				<option>CAE
				<option>DIJ
				<option>ETI
				<option>GUI
				<option>LIL
				<option>LYO
				<option>MAR
				<option>MET
				<option>MON
				<option>MTP
				<option>NIC
				<option>NTE
				<option>PSG
				<option>REN
				<option>STR
				<option>TOU
				<option>TRO
			 </select>
			 </td>
			<td>
			  <select name="butsDom" size="4" style="height:200px">
				<option>0
				<option>1
				<option>2
				<option>3
				<option>4
				<option>5
				<option>6
				<option>7
				<option>8
				<option>9
				<option>ANNULE
			  </select>
			  </td>
			<td>
			   <select name="penaltyDom" size="4" style="height:200px">
				<option>0
				<option>1
				<option>2
				<option>3
				<option>4
				<option>5
				<option>6
				<option>7
				<option>8
				<option>9
			  </select>
			  </td>
			<td>
			  <select name="teamExt" size="4" style="height:200px">
				<option>AMN
				<option>ANG
				<option>BDX
				<option>CAE
				<option>DIJ
				<option>ETI
				<option>GUI
				<option>LIL
				<option>LYO
				<option>MAR
				<option>MET
				<option>MON
				<option>MTP
				<option>NIC
				<option>NTE
				<option>PSG
				<option>REN
				<option>STR
				<option>TOU
				<option>TRO
			 </select>
			 </td>
			<td>
			   <select name="butsExt" size="4" style="height:200px">
				<option>0
				<option>1
				<option>2
				<option>3
				<option>4
				<option>5
				<option>6
				<option>7
				<option>8
				<option>9
				<option>ANNULE
			  </select>
			  </td>
			<td>
			   <select name="penaltyExt" size="4" style="height:200px">
				<option>0
				<option>1
				<option>2
				<option>3
				<option>4
				<option>5
				<option>6
				<option>7
				<option>8
				<option>9
			  </select>
			 </td>
			 </tr>
			 <tr align=CENTER>
				<td colspan=7>
					<input type="submit" name="majResultatsL1" value="Mettre à Jour" />
				</td>
			 </tr>

	</form>
	<table border="1">
	<h4 id="titreResultatsEquipeEnBase">Resultats L1 presents en BDD</h4>
	<tbody>
	<?php
		echo "<br />\n";
		//Récupération des résultats de L1 déjà présent en base de donnée
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

		function getDerniereNumJournee($bdd)
		{
		  $q = $bdd->prepare('SELECT num_journee FROM calendrier_reel
				WHERE statut != 0 ORDER BY date_heure_debut DESC LIMIT 1');
		  $q->execute();
		  return $q->fetchColumn();
		}

		$numJournee = '2017' . getDerniereNumJournee($bdd);

		$req = $bdd->prepare('SELECT * FROM resultatsl1_reel WHERE journee = :num');
		$req->execute(['num' => $numJournee]);

		// On affiche chaque entrée une à une
		while ($donnees = $req->fetch())
		{
		 ?>
		 <tr><td>
		<?php
			echo $donnees['journee'];
		?>
		</td><td>
		<?php
			echo $donnees['equipeDomicile'];
		?>
		</td><td>
		<?php
			echo $donnees['homeDomicile'];
		?>
		</td><td>
		<?php
			echo $donnees['butDomicile'];
		?>
		</td><td>
		<?php
			echo $donnees['winOrLoseDomicile'];
		?>
		</td><td>
		<?php
			echo $donnees['penaltyDomicile'];
		?>
		</td><td>
		<?php
			echo $donnees['equipeVisiteur'];
		?>
		</td><td>
		<?php
			echo $donnees['homeVisiteur'];
		?>
		</td><td>
		<?php
			echo $donnees['butVisiteur'];
		?>
		</td><td>
		<?php
			echo $donnees['winOrLoseVisiteur'];
		?>
		</td><td>
		<?php
			echo $donnees['penaltyVisiteur'];
		?>
		</td></tr>
		<?php
		}
		$req->closeCursor();
		?>
	</tbody>
	</table>

	<HR size=2 align=center width="100%">
	<h2 id="titreImportCSV">Importer les statistiques des joueurs à partir du CSV</h2>
	<?php
	if (isset($messageImportBDD))
	{
		echo '<p>' . $messageImportBDD . '</p>';
	}
	 ?>
	<form method="post" id="importCSV" action="" enctype="multipart/form-data">
		 <label for="mon_fichier">Fichier Stat à Importer (format csv | max. 1 Mo) :</label><br />
		 <label for="mon_fichier">(fichier brut Rotowire nommé AAAAJJ (exemple 201714 pour la saison 2017/2018 et la journée 14)</label><br />
		 <input type="file" name="mon_fichier" id="mon_fichier" /><br />
		 <input type="submit" name="importerStatsBDD" value="Importer" />
	</form>

	<HR size=2 align=center width="100%">
	<h2 id="titreCalculDesNotes">Calcul des notes des joueurs</h2>
	<?php
		$req1 = $bdd->query('SELECT COUNT(*) FROM joueur_stats WHERE note IS NULL');
		$nbJoueurSansNote=$req1->fetch(PDO::FETCH_ASSOC);
		echo 'Actuellement, '.$nbJoueurSansNote['COUNT(*)'].' joueurs ne sont pas notés dans la base de données';
		echo "<br />\n";
		$req1->closeCursor();
	?>
	<form method="post" id="afficheNonNotes" action="afficherJoueursNonNotes.php" enctype="multipart/form-data">
		 <input type="submit" name="submit" value="Qui sont les joueurs non notés ?" /><br />
	</form>

	<form method="post" id="calculNote" action="calculNoteJoueurBDD.php" enctype="multipart/form-data">
		 <input type="submit" name="submit" value="Lancer le calcul des notes des joueurs non notés" /><br />
	</form>

	<HR size=2 align=center width="100%">
	<h2 id="titreCalculerLesConfrontations">Calculer les Confrontations</h2>
	<h4 id="titreDiagDonneesEnBase">Diagnostique des données en base : </h4>
	<h5 id="titreVerif11Titulaires">Vérification de la présence de 11 titulaires</h5>
	<?php
		$req1 = $bdd->query('SELECT t1.journee, t2.equipe, count(t1.id) FROM joueur_stats t1, joueur_reel t2 WHERE t1.id IN (t2.cle_roto_primaire, t2.cle_roto_secondaire) AND t1.titulaire = 1 GROUP BY t1.journee, t2.equipe HAVING count(t1.id)<>11;');
		$equipesErreurTitulaires=$req1->fetchAll();
		if (count($equipesErreurTitulaires) == 0) {
			echo "\t".'OK toutes les équipes ont bien 11 titulaires';
			echo "<br />\n";
		}else{
			foreach ($equipesErreurTitulaires as $equipeErreurTitulaires) {
				echo "\t".'Sur la journee '.$equipeErreurTitulaires['journee'].', '.$equipeErreurTitulaires['equipe'].' a '.$equipeErreurTitulaires['count(t1.id)'].' titulaires. (ERREUR NON BLOQUANTE A Vérifier en base)';
				echo "<br />\n";
			}
		}
		$req1->closeCursor();
	?>
	<h5>Liste des titulaires sans correspondance dans "joueur_reel"</h5>
	<?php
		$req1 = $bdd->query('SELECT t1.journee, t1.id 
		    FROM joueur_stats t1  
		    WHERE t1.titulaire = 1 AND NOT EXISTS (
		        SELECT t2.cle_roto_primaire 
		        FROM joueur_reel t2
		        WHERE t1.id IN (t2.cle_roto_primaire, t2.cle_roto_secondaire)
		    ) ORDER BY t1.journee DESC, t1.id ASC');
		$erreurTitulaires=$req1->fetchAll();
		if (count($erreurTitulaires) == 0) {
			echo "\t".'OK tous les titulaires sont bien identifiés en BDD.';
			echo "<br />\n";
		}else{
			foreach ($erreurTitulaires as $titu) {
				echo "\t".'Sur la journee '.$titu['journee'].', '.$titu['id'].' n\'a pas été identifié en BDD.';
				echo "<br />\n";
			}
		}
		$req1->closeCursor();
	?>
	<h5 id="titreVerifResultatsEtStats">Vérification de la saisie des résultats L1</h5>
	<?php
		$req1 = $bdd->query('SELECT t2.journee, count(t2.journee) FROM resultatsl1_reel t2 GROUP BY t2.journee HAVING COUNT(t2.journee) <> 10;');
		$resultatsSaisisParJournee=$req1->fetchAll();
		if (count($resultatsSaisisParJournee) == 0) {
			echo "\t".'OK il y a bien 10 résultats saisis pour chaque journée';
			echo "<br />\n";
		}else{
			foreach ($resultatsSaisisParJournee as $resultatSaisiParJournee) {
				echo "\t".'Sur la journee '.$resultatSaisiParJournee['journee'].', il y a '.$resultatSaisiParJournee['count(t2.journee)'].' résultats de saisis. (ERREUR A Vérifier en base)';
				echo "<br />\n";
			}
		}
		$req1->closeCursor();
	?>


	<?php
		$req1 = $bdd->query('SELECT t2.journee FROM resultatsl1_reel t2 WHERE t2.journee IN (SELECT t1.journee FROM joueur_stats t1 WHERE t1.note IS NOT NULL  GROUP BY t1.journee HAVING COUNT(t1.journee) > 340) GROUP BY t2.journee HAVING COUNT(t2.journee) = 10 ORDER BY t2.journee DESC;');
		$journeesCalculables=$req1->fetchAll();
		if (count($journeesCalculables) == 0) {
			//Aucune journee calculable pour confrontation : manque résultats L1 ou Import Stats ou Calcul Note
		}else{
			echo "<br />\n";
			echo 'Liste des journées valides :  ';
?>
	<form method="post" id="calculConfrontation" action="calculResultatConfrontation.php" enctype="multipart/form-data">
		<SELECT name="journeeCalculable" size="1">
<?php
			foreach ($journeesCalculables as $journeeCalculable) {
?>
			<OPTION>
<?php
				echo $journeeCalculable['journee'];
			}
?>
		</SELECT>
<?php
		echo "<br />\n";
		echo "<br />\n";
?>
		<input type="submit" name="submit" value="Lancer le calcul des confrontations sur la journée sélectionnée" /><br />
	</form>
<?php
		}
?>

<HR size=2 align=center width="100%">
<h2 id="titreInitDebutJournee">Initialiser le début de journée</h2>
<form method="post" id="initDebutJournee" action="">
	<?php
	if (isset($messageInitDebutJournee))
	{
		echo '<p>' . $messageInitDebutJournee . '</p>';
	}
	 ?>
	 <div>
		 <input type="submit" name="initJournee" value="Init. journée" />
	</div>
</form>

<HR size=2 align=center width="100%">
<h2 id="titreMajScoreJourneeEnCours">Maj buteurs/scores journée en cours</h2>
<form method="post" id="majScoreJourneeEnCours" action="">
	<?php
	if (isset($messageMajScoreJourneeEnCours))
	{
		echo '<p>' . $messageMajScoreJourneeEnCours . '</p>';
	}
	 ?>
	 <div>
		 <input type="submit" name="majScoreJourneeEnCours" value="Maj buteurs/scores" />
	</div>
</form>

<HR size=2 align=center width="100%">
<h2 id="titreMajMoyenneJoueur">Maj des moyennes des joueurs</h2>
<form method="post" id="majMoyenneJoueur" action="">
	<?php
	if (isset($messageMajMoyenneJoueur))
	{
		echo '<p>' . $messageMajMoyenneJoueur . '</p>';
	}
	 ?>
	 <div>
		 <input type="submit" name="majMoyenneJoueur" value="Maj moy." />
	</div>
</form>

<HR size=2 align=center width="100%">
<h2 id="titreMajFinLigue">Maj fin ligue</h2>
<form method="post" id="majFinLigue" action="">
	<?php
	if (isset($messageMajFinLigue))
	{
		echo '<p>' . $messageMajFinLigue . '</p>';
	}
	 ?>
	 <div>
		 <input type="submit" name="majFinLigue" value="Maj fin ligue" />
	</div>
</form>

<HR size=2 align=center width="100%">
<h2 id="titreAjouterBonusLigue">Ajouter bonus/malus ligue</h2>
<form method="post" id="ajoutBonusMalusLigue"  action="ajoutBonusMalusLigue.php">
	<div>
		 <input type="submit" name="ajoutBonusMalusLigue" value="Ajouter bonus/malus" />
	</div>
</form>

</body>
</html>
