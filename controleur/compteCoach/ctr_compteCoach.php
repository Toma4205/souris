<?php

if (isset($_POST['creerLigue']))
{
    header('Location: souris.php?section=creationLigue');
}
elseif (isset($_POST['gererConfreres']))
{
    header('Location: souris.php?section=gestionConfreres');
}

$managerConfrere = new ConfrereManager($bdd);

$confreres = $managerConfrere->findConfreresByIdCoach($coach->id());
$_SESSION[ConstantesSession::LISTE_CONFRERES] = $confreres;

include_once('vue/compteCoach.php');
?>
