<?php

$equipeManager = new EquipeManager($bdd);

$equipes = $equipeManager->findEquipeByLigue($ligue->id());

include_once('vue/classementLigue.php');
?>
