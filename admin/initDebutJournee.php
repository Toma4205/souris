<?php
require_once(__DIR__ . '/../modele/connexionSQL.php');

// Récupération de la connexion
$bdd = ConnexionBDD::getInstance();

$q = $bdd->prepare('SELECT num_journee FROM calendrier_reel WHERE statut = 0 AND date_heure_debut <= NOW()');
$q->execute();
$numJournee = $q->fetchColumn();

if ($numJournee != null)
{
  // On passe le statut de la journée à "en cours" = 1
  $q = $bdd->prepare('UPDATE calendrier_reel SET statut = 1 WHERE num_journee = :num');
  $q->execute([':num' => $numJournee]);

  // On initialise les scores des matchs à 0-0
  $q = $bdd->prepare('UPDATE calendrier_ligue SET score_dom = 0, score_ext = 0
    WHERE num_journee_cal_reel = :num');
  $q->execute([':num' => $numJournee]);

  $messageInitDebutJournee = "Initialisation de la journée " . $numJournee . " réussie.";
}
else {
  $messageInitDebutJournee = "Aucune journée à initialiser.";
}
?>
