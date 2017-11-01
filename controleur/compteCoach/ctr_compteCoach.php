<?php

$managerConfrere = new ConfrereManager($bdd);
$managerLigue = new LigueManager($bdd);

$confreres = $managerConfrere->findConfreresByIdCoach($coach->id());
$_SESSION[ConstantesSession::LISTE_CONFRERES] = $confreres;

if (isset($_POST['continuerCreaLigue']))
{
  foreach($_POST['continuerCreaLigue'] as $cle => $value)
  {
    $_SESSION[ConstantesSession::LIGUE_CREA] = $managerLigue->findLigueById($cle);

    // Redirection du coach vers la ligue
    header('Location: souris.php?section=creationLigue');
  }
}
elseif (isset($_POST['accepterInvitation']))
{
  foreach($_POST['accepterInvitation'] as $cle => $value)
  {
    $managerLigue->accepterInvitationLigue($coach->id(), $cle);

    $message = 'Vous avez rejoint la ligue ' . $cle . '.';
  }
}
elseif (isset($_POST['refuserInvitation']))
{
  foreach($_POST['refuserInvitation'] as $cle => $value)
  {
    $managerLigue->refuserInvitationLigue($coach->id(), $cle);

    $message = 'Vous avez décliné l\'invitation de la ligue ' . $cle . '.';
  }
}
elseif (isset($_POST['rejoindre']))
{
  foreach($_POST['rejoindre'] as $cle => $value)
  {
    $_SESSION[ConstantesSession::LIGUE] = $managerLigue->findLigueById($cle);

    // Redirection du coach vers la ligue
    header('Location: souris.php?section=classementLigue');
  }
}
elseif (isset($_POST['masquer']))
{
  foreach($_POST['masquer'] as $cle => $value)
  {
    // TODO MPL
  }
}

$ligues = $managerLigue->findLiguesByIdCoach($coach->id());
$_SESSION[ConstantesSession::LISTE_LIGUES] = $ligues;

include_once('vue/compteCoach.php');
?>
