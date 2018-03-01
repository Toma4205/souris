<?php
require_once(__DIR__ . '/../modele/connexionSQL.php');

// Récupération de la connexion
$bdd = ConnexionBDD::getInstance();

function getDerniereJourneeEffectuee($bdd)
{
  $q = $bdd->prepare('SELECT num_journee FROM calendrier_reel WHERE statut = 2
    ORDER BY date_heure_debut DESC LIMIT 1');
  $q->execute();
  return $q->fetchColumn();
}

function getLiguesATraiter($bdd, $numJournee)
{
  $q = $bdd->prepare('SELECT DISTINCT(id_ligue) FROM calendrier_ligue WHERE num_journee_cal_reel = :num');
  $q->execute([':num' => $numJournee]);

  $ligues = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $ligues[] = $donnees['id_ligue'];
  }
  $q->closeCursor();

  return $ligues;
}

function getEquipesParLigue($bdd, $idLigue)
{
  $q = $bdd->prepare('SELECT id FROM equipe WHERE id_ligue = :id');
  $q->execute([':id' => $idLigue]);

  $equipes = [];
  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $equipes[] = $donnees['id'];
  }
  $q->closeCursor();

  return $equipes;
}

function majMoyenneEquipe($bdd, $idEquipe)
{
  $q = $bdd->prepare('SELECT id_joueur_reel as idJoueur, (SUM(note)/count(*)) as moyenne
    FROM joueur_compo_equipe
    WHERE id_compo IN (SELECT id FROM compo_equipe WHERE id_equipe = :id)
    AND numero_definitif IS NOT NULL
    GROUP BY id_joueur_reel');
  $q->execute([':id' => $idEquipe]);

  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $q2 = $bdd->prepare('UPDATE joueur_equipe SET moy_note = :moy
      WHERE id_equipe = :idEquipe AND id_joueur_reel = :idJoueur');
    $q2->bindValue(':moy', $donnees['moyenne']);
    $q2->bindValue(':idJoueur', $donnees['idJoueur']);
    $q2->bindValue(':idEquipe', $idEquipe);

    $q2->execute();
  }
  $q->closeCursor();
}

$numJournee = getDerniereJourneeEffectuee($bdd);
if ($numJournee != null) {
  $messageMajMoyenneJoueur = 'Dernière journée terminée = ' . $numJournee . '<br/>';

  $ligues = getLiguesATraiter($bdd, $numJournee);
  if ($ligues != null) {
    $messageMajMoyenneJoueur = $messageMajMoyenneJoueur . sizeof($ligues) . ' ligue(s) à traiter.<br/>';

    foreach($ligues as $cle => $idLigue)
    {
      $equipes = getEquipesParLigue($bdd, $idLigue);

      $messageMajMoyenneJoueur = $messageMajMoyenneJoueur . sizeof($equipes) . ' équipes pour la ligue ' . $idLigue . '.<br/>';

      foreach($equipes as $cle2 => $idEquipe)
      {
        $message = majMoyenneEquipe($bdd, $idEquipe);
        if ($message != '') {
          $messageMajMoyenneJoueur = $messageMajMoyenneJoueur . $message;
        }
      }
    }

  } else {
    $messageMajMoyenneJoueur = $messageMajMoyenneJoueur . 'Aucune ligue à traiter pour cette journée !';
  }
} else {
  $messageMajMoyenneJoueur = 'Aucune journée dans le calendrier réel à l\'état Terminé (statut = 2)';
}

?>
