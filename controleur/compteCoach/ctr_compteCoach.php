<?php

$managerConfrere = new ConfrereManager($bdd);

$confreres = $managerConfrere->findConfreresByIdCoach($coach->id());
$_SESSION[ConstantesSession::LISTE_CONFRERES] = $confreres;

include_once('vue/compteCoach.php');
?>
