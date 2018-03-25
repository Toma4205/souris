<?php

$managerConfrere = new ConfrereManager($bdd);
$managerLigue = new LigueManager($bdd);
$managerCalReel = new CalendrierReelManager($bdd);
$managerActuCoach = new ActualiteCoachManager($bdd);

$confreres = $managerConfrere->findConfreresByIdCoach($coach->id());
$_SESSION[ConstantesSession::LISTE_CONFRERES] = $confreres;
$numJourneeEnCours = $managerCalReel->findNumJourneeEnCours();
$actus = $managerActuCoach->findByCoach($coach->id());

if (isset($_POST['continuerCreaLigue']))
{
  foreach($_POST['continuerCreaLigue'] as $cle => $value)
  {
    $_SESSION[ConstantesSession::LIGUE_CREA] = $managerLigue->findLigueById($cle);

    $managerEquipe = new EquipeManager($bdd);
    $equipe = $managerEquipe->findEquipeByCoachEtLigue($coach->id(), $cle);
    if (isset($equipe))
    {
      $_SESSION[ConstantesSession::EQUIPE_CREA] = $equipe;
      // Redirection du coach vers le mercato
      header('Location: index.php?section=mercatoEquipe');
    }
    else
    {
      // Redirection du coach vers la ligue
      header('Location: index.php?section=creationLigue');
    }
  }
}
elseif (isset($_POST['accepterInvitation']))
{
  foreach($_POST['accepterInvitation'] as $cle => $value)
  {
    $managerLigue->accepterInvitationLigue($coach->id(), $cle);
  }
}
elseif (isset($_POST['refuserInvitation']))
{
  foreach($_POST['refuserInvitation'] as $cle => $value)
  {
    $managerLigue->refuserInvitationLigue($coach->id(), $cle);
  }
}
elseif (isset($_POST['rejoindre']))
{
  foreach($_POST['rejoindre'] as $cle => $value)
  {
    $_SESSION[ConstantesSession::LIGUE] = $managerLigue->findLigueById($cle);

    // Redirection du coach vers la ligue
    header('Location: index.php?section=classementLigue');
  }
}
elseif (isset($_POST['suppCreaLigue']))
{
  foreach($_POST['suppCreaLigue'] as $cle => $value)
  {
    $managerLigue->supprimerLigue($cle);
  }
}
elseif (isset($_POST['masquer']) && $_POST['masquer'] != null)
{
  foreach($_POST['masquer'] as $cle => $value)
  {
    $managerLigue->masquerLigue($cle, $coach->id());
  }
}
elseif (isset($_POST['scoreLigue']) && $_POST['scoreLigue'] != null)
{
  foreach($_POST['scoreLigue'] as $cle => $value)
  {
    $_SESSION[ConstantesSession::LIGUE] = $managerLigue->findLigueById($cle);

    // Redirection du visiteur vers la page calendrier de la ligue
    header('Location: index.php?section=calendrier');
  }
}

if ($numJourneeEnCours != null) {
  $ligues = $managerLigue->findLiguesEnCoursByIdCoach($coach->id(), $numJourneeEnCours);
} else {
  $ligues = $managerLigue->findLiguesByIdCoach($coach->id());
}
$_SESSION[ConstantesSession::LISTE_LIGUES] = $ligues;

include_once('vue/compteCoach.php');
?>
