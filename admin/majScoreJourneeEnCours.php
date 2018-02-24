<?php
require_once(__DIR__ . '/../modele/connexionSQL.php');

// Récupération de la connexion
$bdd = ConnexionBDD::getInstance();

function getNumJourneeEnCours($bdd)
{
  $q = $bdd->prepare('SELECT num_journee FROM calendrier_reel WHERE statut = 1');
  $q->execute();
  return $q->fetchColumn();
}

function majButeurTempJourneeEnCours($bdd, $numJournee)
{
  $journeeStat = '2017' . $numJournee;
  $joueurs = [];
  $q = $bdd->prepare('SELECT id, but, csc
    FROM joueur_stats
    WHERE journee = :journee AND (but > 0 OR csc > 0)');
  $q->execute([':journee' => $journeeStat]);

  while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  {
    $q2 = $bdd->prepare('UPDATE joueur_compo_equipe jce
      SET jce.nb_but_reel = :nbBut, jce.nb_csc = :nbCsc
      WHERE jce.numero < 12
      AND jce.id_joueur_reel = (
        SELECT id
        FROM joueur_reel
        WHERE cle_roto_primaire = :cle
      )
      AND jce.id_compo IN (
        SELECT id
        FROM compo_equipe
        WHERE id_cal_ligue IN (
          SELECT id
          FROM calendrier_ligue
          WHERE num_journee_cal_reel = :numJournee
        )
      )');
    $q2->bindValue(':nbBut', $donnees['but']);
    $q2->bindValue(':nbCsc', $donnees['csc']);
    $q2->bindValue(':cle', $donnees['id']);
    $q2->bindValue(':numJournee', $numJournee);

    $q2->execute();
  }
  $q->closeCursor();
}

function reinitScoreA0($bdd, $numJournee)
{
  $q = $bdd->prepare('UPDATE calendrier_ligue SET score_dom = 0
    WHERE num_journee_cal_reel = :numJournee AND score_dom IS NULL');
  $q->bindValue(':numJournee', $numJournee);

  $q->execute();

  $q = $bdd->prepare('UPDATE calendrier_ligue SET score_ext = 0
    WHERE num_journee_cal_reel = :numJournee AND score_ext IS NULL');
  $q->bindValue(':numJournee', $numJournee);

  $q->execute();
}

function majScoreTempJourneeEnCours($bdd, $numJournee)
{
    $q = $bdd->prepare('UPDATE calendrier_ligue cl
      SET cl.score_dom = (
        SELECT SUM(jce.nb_but_reel)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_dom
        AND ce.id_cal_ligue = cl.id
      ),
      cl.score_ext = (
        SELECT SUM(jce.nb_but_reel)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_ext
        AND ce.id_cal_ligue = cl.id
      )
      WHERE cl.num_journee_cal_reel = :numJournee');
    $q->bindValue(':numJournee', $numJournee);

    $q->execute();

    reinitScoreA0($bdd, $numJournee);

    // Prise en compte des CSC
    $q = $bdd->prepare('UPDATE calendrier_ligue cl
      SET cl.score_dom = (cl.score_dom + (
        SELECT SUM(jce.nb_csc)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_ext
        AND ce.id_cal_ligue = cl.id
      )),
      cl.score_ext = (cl.score_ext + (
        SELECT SUM(jce.nb_csc)
        FROM joueur_compo_equipe jce
        JOIN compo_equipe ce ON ce.id = jce.id_compo
        WHERE ce.id_equipe = cl.id_equipe_dom
        AND ce.id_cal_ligue = cl.id
      ))
      WHERE cl.num_journee_cal_reel = :numJournee');
      $q->bindValue(':numJournee', $numJournee);

      $q->execute();

      reinitScoreA0($bdd, $numJournee);
}

$numJournee = getNumJourneeEnCours($bdd);
if ($numJournee != null) {
  majButeurTempJourneeEnCours($bdd, $numJournee);
  majScoreTempJourneeEnCours($bdd, $numJournee);
  $messageMajScoreJourneeEnCours = 'Mise à jour des buteurs et des scores OK.';
} else {
  $messageMajScoreJourneeEnCours = 'Aucune journée en cours !';
}

?>
