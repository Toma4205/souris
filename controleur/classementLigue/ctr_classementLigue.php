<?php

$equipeManager = new EquipeManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);

$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $ligue->id());
$equipes = $equipeManager->findEquipeByLigue($ligue->id());
$tabEffectif;
foreach ($equipes as $cle => $value)
{
  $tabEffectif[$value->id()] = $joueurEquipeManager->findByEquipe($value->id());
}

$buteurs = $joueurEquipeManager->findButeurByLigue($ligue->id());

include_once('vue/classementLigue.php');
?>
