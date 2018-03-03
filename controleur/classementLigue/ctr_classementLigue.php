<?php

$equipeManager = new EquipeManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);

$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $ligue->id());
$equipes = $equipeManager->findEquipeByLigue($ligue->id());
$buteurs = $joueurEquipeManager->findButeurByLigue($ligue->id());

$joueursLigue = $joueurEquipeManager->findJoueursPourEffectifsByLigue($ligue->id());

$tabEffectif = [];
foreach ($joueursLigue as $cle => $joueur)
{
  if (isset($tabEffectif[$joueur->idEquipe()])) {
    $tabEffectif[$joueur->idEquipe()][] = $joueur;
  } else {
    $tabEffectif[$joueur->idEquipe()] = array($joueur);
  }
}

$joueursLigue = $joueurEquipeManager->findJoueursPourStatsByLigue($ligue->id());

$topGB = [];
$topDEF = [];
$topMIL = [];
$topATT = [];
foreach ($joueursLigue as $cle => $joueur)
{
  if ($joueur->position() == ConstantesAppli::GARDIEN && sizeof($topGB) < 10) {
    $topGB[] = $joueur;
  } elseif ($joueur->position() == ConstantesAppli::DEFENSEUR && sizeof($topDEF) < 10) {
    $topDEF[] = $joueur;
  } elseif ($joueur->position() == ConstantesAppli::MILIEU && sizeof($topMIL) < 10) {
    $topMIL[] = $joueur;
  } elseif ($joueur->position() == ConstantesAppli::ATTAQUANT && sizeof($topATT) < 10) {
    $topATT[] = $joueur;
  }
}

include_once('vue/classementLigue.php');
?>
