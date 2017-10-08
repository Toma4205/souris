<?php

$managerConfrere = new ConfrereManager($bdd);
$managerLigue = new LigueManager($bdd);

$confreres = $managerConfrere->findConfreresByIdCoach($coach->id());
$_SESSION[ConstantesSession::LISTE_CONFRERES] = $confreres;

$ligues = $managerLigue->findLiguesByIdCoach($coach->id());
$_SESSION[ConstantesSession::LISTE_LIGUES] = $ligues;

if (isset($_POST['modifier']))
{
  foreach($_POST['modifier'] as $cle => $value)
  {
    $confrereManager->creerConfrere($coach, $cle);
  }
}
elseif (isset($_POST['rejoindre']))
{
  foreach($_POST['rejoindre'] as $cle => $value)
  {
    $ligue = $managerLigue->findLigueById($cle);
    $_SESSION[ConstantesSession::LIGUE] = $ligue;

    // Redirection du coach vers la ligue
    header('Location: souris.php?section=classementLigue');
  }
}
elseif (isset($_POST['masquer']))
{
  foreach($_POST['masquer'] as $cle => $value)
  {
    $confrereManager->creerConfrere($coach, $cle);
  }
}

include_once('vue/compteCoach.php');
?>
