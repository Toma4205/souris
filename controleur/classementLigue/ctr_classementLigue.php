<?php

$equipeManager = new EquipeManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);

$equipes = $equipeManager->findEquipeByLigue($ligue->id());
$buteurs = $joueurEquipeManager->findButeurByLigue($ligue->id());

include_once('vue/classementLigue.php');
?>
