<html>
<body>
	<h1>Page Administrateur</h1>
		<p>
			<a class="bouton" href="#titreImportCSV">Importer les statistiques des joueurs à partir du CSV</a>
		</p>
		<p>
			<a class="bouton" href="#titreMAJResultatsEquipe">Mettre à jour les résultats des équipes</a>
		</p> 
		<p>
			<a class="bouton" href="#titreCalculDesNotes">Calcul des notes</a>
		</p>
	
	<h2 id="titreImportCSV">Importer les statistiques des joueurs à partir du CSV</h2>
	<form method="post" id="importCSV" action="admin/importJourneeCSV.php" enctype="multipart/form-data">
		 <label for="mon_fichier">Fichier Stat à Importer (format csv | max. 1 Mo) :</label><br />
		 <label for="mon_fichier">(fichier brut Rotowire nommé AAAAJJ (exemple 201714 pour la saison 2017/2018 et la journée 14)</label><br />
		 <input type="file" name="mon_fichier" id="mon_fichier" /><br />
		 <input type="submit" name="submit" value="Importer" />
	</form>
	
	<h2 id="titreMAJResultatsEquipe">Mettre à jour les résultats des équipes</h2>
	<form method="post" id="MAJResultatsEquipe" action="admin/MAJResultatL1.php" enctype="multipart/form-data">
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
					<input type="submit" name="MAJ" value="Mettre à Jour" />
				</td>
			 </tr>
			
	</form>
	<table border="1">
	<h3 id="titreResultatsEquipeEnBase">Resultats L1 presents en BDD</h3>
	<tbody>
	<?php
		echo "<br />\n";
		//Récupération des résultats de L1 déjà présent en base de donnée
		require_once(__DIR__ . '/modele/connexionSQL.php');
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
		
		$req = $bdd->query('SELECT * FROM resultatsL1_reel ORDER BY journee DESC');
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
	
	<h2 id="titreCalculDesNotes">Calcul des notes pour une journée</h2>
	<form method="post" id="calculNote" action="admin/calculNoteJoueur.php" enctype="multipart/form-data">
		 <select name="idJourneeCalculNote" size="4" style="height:200px">
				<option>201709
				<option>201710
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
		 <input type="submit" name="submit" value="Lancer le calcul des notes des joueurs" /><br />
	</form>

</body>
</html>