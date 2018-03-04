<?php
require_once(__DIR__ . '/../modele/connexionSQL.php');
require_once(__DIR__ . '/../modele/joueurequipe/joueurEquipe.php');
require_once(__DIR__ . '/../modele/compoequipe/compoEquipe.php');
require_once(__DIR__ . '/../modele/compoequipe/joueurCompoEquipe.php');
require_once(__DIR__ . '/../controleur/constantesAppli.php');

// Récupération de la connexion
$bdd = ConnexionBDD::getInstance();

function getEquipeSansCompo($bdd, $numJournee)
{
  $q = $bdd->prepare('SELECT cl.id_equipe_dom as id FROM calendrier_ligue cl
    WHERE cl.num_journee_cal_reel = :num AND NOT EXISTS (
      SELECT ce.id FROM compo_equipe ce
      WHERE ce.id_equipe = cl.id_equipe_dom AND ce.id_cal_ligue = cl.id)');
  $q->execute([':num' => $numJournee]);

  $equipes = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $equipes[] = $donnees['id'];
  }
  $q->closeCursor();

  $q = $bdd->prepare('SELECT cl.id_equipe_ext as id FROM calendrier_ligue cl
    WHERE cl.num_journee_cal_reel = :num AND NOT EXISTS (
      SELECT ce.id FROM compo_equipe ce
      WHERE ce.id_equipe = cl.id_equipe_ext AND ce.id_cal_ligue = cl.id)');
  $q->execute([':num' => $numJournee]);

  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $equipes[] = $donnees['id'];
  }
  $q->closeCursor();

  return $equipes;
}

function getCompoEquipePrecedente($bdd, $idEquipe, $numJournee)
{
  $q = $bdd->prepare('SELECT ce.* FROM compo_equipe ce
    WHERE ce.id_cal_ligue = (SELECT cl.id FROM calendrier_ligue cl
      WHERE cl.num_journee_cal_reel = :num
      AND (cl.id_equipe_dom = :id OR cl.id_equipe_ext = :id))');
  $q->execute([':num' => $numJournee, ':id' => $idEquipe]);

  $donnees = $q->fetch(PDO::FETCH_ASSOC);
  $q->closeCursor();

  // Si la compo n'est pas trouvée
  if (is_bool($donnees))
  {
    return null;
  }
  else
  {
    return new CompoEquipe($donnees);
  }
}

function getTitulairesByCompo($bdd, $idCompo)
{
  $q = $bdd->prepare('SELECT * FROM joueur_compo_equipe
    WHERE numero < 12
    AND id_compo = :id');
  $q->execute([':id' => $idCompo]);

  $joueurs = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $joueurs[] = new JoueurCompoEquipe($donnees);
  }
  $q->closeCursor();

  return $joueurs;
}

function getCalLigueByEquipeEtJournee($bdd, $idEquipe, $numJournee)
{
  $q = $bdd->prepare('SELECT id FROM calendrier_ligue
    WHERE num_journee_cal_reel = :num
    AND (id_equipe_dom = :id OR id_equipe_ext = :id)');
  $q->execute([':num' => $numJournee, ':id' => $idEquipe]);

  return $q->fetchColumn();
}

function creerCompoCommePrecedente($bdd, $compo, $idEquipe, $idCalLigue, $joueurs)
{
  $q = $bdd->prepare('INSERT INTO compo_equipe(id_cal_ligue, id_equipe, code_tactique)
      VALUES(:calLigue, :equipe, :tactique)');
  $q->bindValue(':calLigue', $idCalLigue);
  $q->bindValue(':equipe', $idEquipe);
  $q->bindValue(':tactique', $compo->codeTactique());

  $q->execute();

  $idCompo = $bdd->lastInsertId();

  foreach($joueurs as $cle => $joueur)
  {
    $q = $bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine)
        VALUES(:idCompo, :idJoueur, :num, :cap)');
    $q->bindValue(':idCompo', $idCompo);
    $q->bindValue(':idJoueur', $joueur->idJoueurReel());
    $q->bindValue(':num', $joueur->numero());
    $q->bindValue(':cap', $joueur->capitaine());

    $q->execute();
  }

  return $idCompo;
}

function getJoueurEquipeByEquipe($bdd, $idEquipe)
{
  $q = $bdd->prepare('SELECT je.*, j.position, j.id
    FROM joueur_equipe je
    JOIN joueur_reel j ON je.id_joueur_reel = j.id
    WHERE id_equipe = :id');
  $q->execute([':id' => $idEquipe]);

  $joueurs = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $joueurs[] = new JoueurEquipe($donnees);
  }
  $q->closeCursor();

  return $joueurs;
}

function creerPremiereCompo($bdd, $idEquipe, $idCalLigue, $joueurs)
{
  $q = $bdd->prepare('INSERT INTO compo_equipe(id_cal_ligue, id_equipe, code_tactique)
      VALUES(:calLigue, :equipe, :tactique)');
  $q->bindValue(':calLigue', $idCalLigue);
  $q->bindValue(':equipe', $idEquipe);
  $q->bindValue(':tactique', ConstantesAppli::TACTIQUE_DEFAUT);

  $q->execute();

  $idCompo = $bdd->lastInsertId();

  $nbGB = 0;
  $nbDef = 0;
  $nbMil = 0;
  $nbAtt = 0;
  foreach($joueurs as $cle => $joueur)
  {
    $numero = 1;
    if ($joueur->position() == ConstantesAppli::GARDIEN && $nbGB < 1) {
      $nbGB++;
    } elseif ($joueur->position() == ConstantesAppli::DEFENSEUR && $nbDef < 4) {
      $nbDef++;
      $numero += $nbDef;
    } elseif ($joueur->position() == ConstantesAppli::MILIEU && $nbMil < 4) {
      $nbMil++;
      $numero += 4 + $nbMil;
    } elseif ($joueur->position() == ConstantesAppli::ATTAQUANT && $nbAtt < 2) {
      $nbAtt++;
      $numero += 8 + $nbAtt;
    } else {
      $numero = 0;
    }

    if ($numero > 0) {
      $q = $bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine)
          VALUES(:idCompo, :idJoueur, :num, 0)');
      $q->bindValue(':idCompo', $idCompo);
      $q->bindValue(':idJoueur', $joueur->id());
      $q->bindValue(':num', $numero);

      $q->execute();
    }
  }

  return $idCompo;
}

$q = $bdd->prepare('SELECT num_journee FROM calendrier_reel WHERE statut = 0 AND date_heure_debut <= NOW()');
$q->execute();
$numJournee = $q->fetchColumn();

if ($numJournee != null)
{
  $equipes = getEquipeSansCompo($bdd, $numJournee);
  if (sizeof($equipes) > 0) {
    $messageInitDebutJournee = sizeof($equipes) . ' compo manquantes. <br/>';
    foreach($equipes as $cle => $idEquipe)
    {
      $idCalLigue = getCalLigueByEquipeEtJournee($bdd, $idEquipe, $numJournee);
      $compo = getCompoEquipePrecedente($bdd, $idEquipe, $numJournee - 1);

      if ($compo != null) {
        $joueurs = getTitulairesByCompo($bdd, $compo->id());
        $idCompo = creerCompoCommePrecedente($bdd, $compo, $idEquipe, $idCalLigue, $joueurs);
      } else {
        $joueurs = getJoueurEquipeByEquipe($bdd, $idEquipe);
        $idCompo = creerPremiereCompo($bdd, $idEquipe, $idCalLigue, $joueurs);
      }

      $messageInitDebutJournee = $messageInitDebutJournee . 'Création compo ' .
        $idCompo . ' pour le match ' . $idCalLigue . ' pour l\'équipe ' . $idEquipe . '. <br/>';
    }
  } else {
    $messageInitDebutJournee = 'Aucune compo manquante. <br/>';
  }

  // On passe le statut de la journée à "en cours" = 1
  $q = $bdd->prepare('UPDATE calendrier_reel SET statut = 1 WHERE num_journee = :num');
  $q->execute([':num' => $numJournee]);

  // On initialise les scores des matchs à 0-0
  $q = $bdd->prepare('UPDATE calendrier_ligue SET score_dom = 0, score_ext = 0
    WHERE num_journee_cal_reel = :num');
  $q->execute([':num' => $numJournee]);

  $messageInitDebutJournee = $messageInitDebutJournee . "Initialisation de la journée " . $numJournee . " réussie. <br/>";
}
else {
  $messageInitDebutJournee = "Aucune journée à initialiser.";
}
?>
