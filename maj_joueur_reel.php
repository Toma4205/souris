<?php
include_once('admin/fonctions_admin.php');
require_once(__DIR__ . '/modele/joueurreel/joueurReel.php');

/////////////////CONNEXION BASE
///// Utilisation du mot clé "global" pour avoir accès à la bdd dans toutes les fonctions
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
	addLogEvent($e);
}

$nbElemParPage = 100;

function compterComplet()
{
    global $bdd;
	
	$q = $bdd->prepare('SELECT count(j.id)
		FROM joueur_reel j
		WHERE j.anniv IS NOT NULL AND j.nationalite IS NOT NULL AND j.position_expert IS NOT NULL');
    $q->execute();

	return $q->fetchColumn();
}

function findComplet($page)
{
    $joueurs = [];

	global $bdd;
	global $nbElemParPage;
	
	$q = $bdd->prepare('SELECT j.id, j.nom, j.prenom, j.position, j.position_expert, j.position_secondaire, n.libelle as libelleEquipe, count(je.id_equipe) as nbCC 
		FROM joueur_reel j
		JOIN nomenclature_equipe n ON j.equipe = n.code 
		LEFT JOIN joueur_equipe je ON je.id_joueur_reel = j.id
		WHERE j.anniv IS NOT NULL AND j.nationalite IS NOT NULL AND j.position_expert IS NOT NULL
		GROUP BY j.id, j.nom, j.prenom, j.position, j.position_expert, j.position_secondaire, libelleEquipe
		ORDER BY j.nom, j.prenom
		LIMIT ' . $nbElemParPage . ' OFFSET ' . (($page - 1) * $nbElemParPage));
    $q->execute();

	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
	{
		$joueurs[] = ['joueur' => new JoueurReel($donnees), 'nbCC' => $donnees['nbCC']];
	}

	$q->closeCursor();

	return $joueurs;
}

function findIncomplet()
{
    $joueurs = [];

	global $bdd;
	$q = $bdd->prepare('SELECT j.*, n.libelle as libelleEquipe
		FROM joueur_reel j
        JOIN nomenclature_equipe n ON j.equipe = n.code 
		WHERE j.anniv IS NULL OR j.nationalite IS NULL or j.position_expert IS NULL
		ORDER BY j.nom, j.prenom');
    $q->execute();

	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
	{
		$joueurs[] = new JoueurReel($donnees);
	}

	$q->closeCursor();

	return $joueurs;
}

function findNationalite()
{
    global $bdd;
	$q = $bdd->prepare('SELECT DISTINCT(nationalite) as nat FROM joueur_reel ORDER BY nationalite ASC');
    $q->execute();
	$donnees = $q->fetchAll();

	$nat = [];
	foreach ($donnees as $cle => $value)
	{
		$nat[$cle] = $value['nat'];
	}

	$q->closeCursor();

	return $nat;
}

function findPosition()
{
    global $bdd;
	$q = $bdd->prepare('SELECT code, libelle FROM nomenclature_position ORDER BY libelle ASC');
    $q->execute();
	$donnees = $q->fetchAll();

	$pos = [];
	foreach ($donnees as $cle => $value)
	{
		$pos[$value['code']] = $value['libelle'];
	}

	$q->closeCursor();

	return $pos;
}

function findCategorie()
{
    global $bdd;
	$q = $bdd->prepare('SELECT DISTINCT(position) as pos FROM joueur_reel ORDER BY position ASC');
    $q->execute();
	$donnees = $q->fetchAll();

	$pos = [];
	foreach ($donnees as $cle => $value)
	{
		$pos[$cle] = $value['pos'];
	}

	$q->closeCursor();

	return $pos;
}

function compterPresenceDansEquipe($id)
{
	global $bdd;
	$q = $bdd->prepare('SELECT COUNT(*) 
		FROM joueur_equipe
		WHERE id_joueur_reel = :id');
    $q->execute([':id' => $id]);
	
	return $q->fetchColumn();
}

function supprimerJoueurReel($id)
{
	global $bdd;
	$q = $bdd->prepare('DELETE FROM joueur_reel WHERE id = :id');
    $q->execute([':id' => $id]);
	echo 'Supp joueur avec id ' . $id;
}

function majJoueurReel($id, $anniv, $nat, $posExpert, $posSecond)
{
	global $bdd;
	$q = $bdd->prepare('UPDATE joueur_reel SET anniv = :anniv, nationalite = :nat, position_expert = :posExpert, position_secondaire = :posSecond WHERE id = :id');
    $q->execute([':anniv' => $anniv, ':nat' => $nat, ':posExpert' => $posExpert, ':posSecond' => $posSecond, ':id' => $id]);
	echo 'Maj joueur avec id ' . $id . ' => anniv:' . $anniv . ', nat:' . $nat . ', posExpert:' . $posExpert . ', posSecond:' . $posSecond;
}

function majJoueurReelComplet($id, $cat, $posExpert, $posSecond)
{
	global $bdd;
	$q = $bdd->prepare('UPDATE joueur_reel SET position = :cat, position_expert = :posExpert, position_secondaire = :posSecond WHERE id = :id');
    $q->execute([':cat' => $cat, ':posExpert' => $posExpert, ':posSecond' => $posSecond, ':id' => $id]);
	echo 'Maj joueur avec id ' . $id . ' => cat:' . $cat . ', posExpert:' . $posExpert . ', posSecond:' . $posSecond;
}

function afficherSelectNationalite($nat, $id, $natJoueur)
{
	$log = '<select name="nat_' . $id . '" >';
	foreach ($nat as $value)
	{
		if ($natJoueur != null && $natJoueur == $value) {
			$log = $log . '<option value="' . $value . '" selected="selected">' . $value . '</option>';
		}
		else {
			$log = $log . '<option value="' . $value . '">' . $value . '</option>';
		}
	}
	$log = $log . '</select>';
	return $log;
}

function afficherSelectPositionExpert($pos, $id, $posJoueur)
{
	$log = '<select name="pos_expert_' . $id . '" >';
	foreach ($pos as $cle => $value)
	{
		if ($posJoueur != null && $posJoueur == $cle) {
			$log = $log . '<option value="' . $cle . '" selected="selected">' . $value . '</option>';
		}
		else {
			$log = $log . '<option value="' . $cle . '">' . $value . '</option>';
		}
	}
	$log = $log . '</select>';
	return $log;
}

function afficherSelectPositionSecondaire($pos, $id, $posJoueur)
{
	$log = '<select name="pos_second_' . $id . '" >';
	$log = $log . '<option value="-1"></option>';
	foreach ($pos as $cle => $value)
	{
		if ($posJoueur != null && $posJoueur == $cle) {
			$log = $log . '<option value="' . $cle . '" selected="selected">' . $value . '</option>';
		}
		else {
			$log = $log . '<option value="' . $cle . '">' . $value . '</option>';
		}
	}
	$log = $log . '</select>';
	return $log;
}

function afficherSelectCategorie($cat, $id, $catJoueur)
{
	$log = '<select name="cat_' . $id . '" >';
	foreach ($cat as $value)
	{
		if ($catJoueur != null && $catJoueur == $value) {
			$log = $log . '<option value="' . $value . '" selected="selected">' . $value . '</option>';
		}
		else {
			$log = $log . '<option value="' . $value . '">' . $value . '</option>';
		}
	}
	$log = $log . '</select>';
	return $log;
}

function afficherJoueursIncomplets($joueursIncomplets, $nat, $pos)
{
	if (sizeof($joueursIncomplets) > 0)
	{	echo sizeof($joueursIncomplets) . ' joueur(s) à traiter. <br/><br/>';
		echo '<table border="1">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Id</th>';
		echo '<th>Nom</th>';
		echo '<th>Equipe</th>';
		echo '<th>Position</th>';
		echo '<th>Anniv</th>';
		echo '<th>Nationalité</th>';
		echo '<th>Position expert</th>';
		echo '<th>Position secondaire</th>';
		echo '<th>Nb équipes CC</th>';
		echo '<th>Mettre à jour</th>';
		echo '<th>Supprimer</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach($joueursIncomplets as $value)
		{
			$nbEquipe = compterPresenceDansEquipe($value->id());
			$anniv = '';
			if ($value->anniv() != null) {
				$anniv = date_format(date_create($value->anniv()), 'd/m/Y');
			}
			
			echo '<tr><td>' . $value->id() . '</td>';
			echo '<td>' . $value->nom() . ' ' . $value->prenom() . '</td>';
			echo '<td>' . $value->libelleEquipe() . '</td>';
			echo '<td>' . $value->position() . '</td>';
			echo '<td><input name="anniv_' . $value->id() . '" type="text" value="' . $anniv . '"></td>';
			echo '<td>' . afficherSelectNationalite($nat, $value->id(), $value->nationalite()) . '</td>';
			echo '<td>' . afficherSelectPositionExpert($pos, $value->id(), $value->positionExpert()) . '</td>';
			echo '<td>' . afficherSelectPositionSecondaire($pos, $value->id(), $value->positionSecondaire()) . '</td>';
			echo '<td>' . $nbEquipe . '</td>';
			echo '<td><input type="submit" value="Maj" name="maj[' . $value->id() . ']" 
				onclick="return confirm(\'Mettre à jour ' . $value->nom() . ' ' . $value->prenom() . '(' . $value->libelleEquipe() . ')' . '\');" /></td>';
			echo '<td><input type="submit" value="Supprimer" name="supp[' . $value->id() . ']" 
				onclick="return confirm(\'Supprimer ' . $value->nom() . ' ' . $value->prenom() . '(' . $value->libelleEquipe() . ')' . '\');" /></td></tr>';
		}
    
		echo '</tbody>';
		echo '</table>';
	}
	else
	{
		echo '<br/>';
		echo 'Aucun joueur à traiter.';
	}
}

function afficherJoueursComplets($joueurs, $cat, $pos)
{
	if (sizeof($joueurs) > 0)
	{	
		echo '<table border="1">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Id</th>';
		echo '<th>Nom</th>';
		echo '<th>Equipe</th>';
		echo '<th>Position</th>';
		echo '<th>Position expert</th>';
		echo '<th>Position secondaire</th>';
		echo '<th>Nb équipes CC</th>';
		echo '<th>Mettre à jour</th>';
		echo '<th>Supprimer</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach($joueurs as $joueur)
		{
			$value = $joueur['joueur'];
			$nbEquipe = $joueur['nbCC'];
			
			echo '<tr><td>' . $value->id() . '</td>';
			echo '<td>' . $value->nom() . ' ' . $value->prenom() . '</td>';
			echo '<td>' . $value->libelleEquipe() . '</td>';
			echo '<td>' . afficherSelectCategorie($cat, $value->id(), $value->position()) . '</td>';
			echo '<td>' . afficherSelectPositionExpert($pos, $value->id(), $value->positionExpert()) . '</td>';
			echo '<td>' . afficherSelectPositionSecondaire($pos, $value->id(), $value->positionSecondaire()) . '</td>';
			echo '<td>' . $nbEquipe . '</td>';
			echo '<td><input type="submit" value="Maj" name="maj_complet[' . $value->id() . ']" 
				onclick="return confirm(\'' . $value->id() . '- Mettre à jour ' . $value->nom() . ' ' . $value->prenom() . ' (' . $value->libelleEquipe() . ')' . '\');" /></td>';
			echo '<td><input type="submit" value="Supprimer" name="supp[' . $value->id() . ']" 
				onclick="return confirm(\'' . $value->id() . '- Supprimer ' . $value->nom() . ' ' . $value->prenom() . '(' . $value->libelleEquipe() . ')' . '\');" /></td></tr>';
		}
    
		echo '</tbody>';
		echo '</table>';
	}
}

if (isset($_POST['supp']))
{
	foreach($_POST['supp'] as $cle => $value)
	{
		supprimerJoueurReel($cle);
	}
}
elseif (isset($_POST['maj']))
{
	foreach($_POST['maj'] as $cle => $value)
	{
		$posSecond = NULL;
		if ($_POST['pos_second_' . $cle] != -1) {
			$posSecond = $_POST['pos_second_' . $cle];
		}
		
		$anniv = NULL;
		if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $_POST['anniv_' . $cle]) != 0) {
			$anniv = $_POST['anniv_' . $cle];
		}
		
		majJoueurReel($cle, $anniv, $_POST['nat_' . $cle], $_POST['pos_expert_' . $cle], $posSecond);
	}
}
elseif (isset($_POST['maj_complet']))
{
	foreach($_POST['maj_complet'] as $cle => $value)
	{
		$posSecond = NULL;
		if ($_POST['pos_second_' . $cle] != -1) {
			$posSecond = $_POST['pos_second_' . $cle];
		}
		majJoueurReelComplet($cle, $_POST['cat_' . $cle], $_POST['pos_expert_' . $cle], $posSecond);
	}
}

$nat = findNationalite();
$cat = findCategorie();
$pos = findPosition();

$pageComplet = 1;
if (isset($_POST['page_complet'])) {
	$pageComplet = $_POST['page_complet'];
}

$totalComplets = compterComplet();
$joueursComplets = findComplet($pageComplet);
$joueursIncomplets = findIncomplet();

?>

<html>
	<body>
		<h2>Joueurs avec infos incomplètes</h2>
		<form method="post" action="">
			<?php afficherJoueursIncomplets($joueursIncomplets, $nat, $pos); ?>
		</form>
		<br/>
		<h2>Joueurs avec infos complètes</h2>
		<form id="formComplet" method="post" action="">
			<div><?php echo $totalComplets ?> joueur(s) complet(s).</div>
			<br/>
			<div>Page : 
				<select name="page_complet" onchange="javascript:submit();">
				<?php
					$max = ceil($totalComplets / $nbElemParPage);
					for ($i = 1; $i <= $max; $i++)
					{
						if ($i == $pageComplet) {
							echo '<option value="' . $i . '" selected="selected">' . $i . '</option>';
						} else {
							echo '<option value="' . $i . '">' . $i . '</option>';
						}
					}
				?>
				</select>
			</div>
			<?php afficherJoueursComplets($joueursComplets, $cat, $pos); ?>
		</form>
	</body>
</html>
