<?php

$equipeManager = new EquipeManager($bdd);
$calReelManager = new CalendrierReelManager($bdd);
$calLigueManager = new CalendrierLigueManager($bdd);

$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $ligue->id());
$calReel = $calReelManager->findProchaineJournee();
$calLigue = $calLigueManager->findProchaineJourneeByEquipe($equipe->id());

include_once('vue/equipe.php');
?>
