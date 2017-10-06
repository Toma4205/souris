<?php

$coachManager = new CoachManager($bdd);
$amiManager = new AmiManager($bdd);

if (isset($_SESSION[ConstantesSession::LISTE_RECH_AMIS]))
{
  $coachsRech = $_SESSION[ConstantesSession::LISTE_RECH_AMIS];
}

$amis = $_SESSION[ConstantesSession::LISTE_AMIS];

// Recherche des coachs par nom
if (isset($_POST['rechercher']))
{
  if (isset($_POST['nomCoach']) && !empty($_POST['nomCoach']))
  {
    if (!isset($_SESSION[ConstantesSession::RECH_AMI]) || $_SESSION[ConstantesSession::RECH_AMI] != $_POST['nomCoach'])
    {
      $coachsRech = $coachManager->findByNom($_POST['nomCoach'], $coach->id());
      $_SESSION[ConstantesSession::RECH_AMI] = $_POST['nomCoach'];
      $_SESSION[ConstantesSession::LISTE_RECH_AMIS] = $coachsRech;
    }
  }
  else
  {
    $message = 'La recherche ne doit pas être vide.';
  }
}
// Envoi d'une demande d'ajout à un autre coach
elseif (isset($_POST['ajouter']))
{
  foreach($_POST['ajouter'] as $cle => $value)
  {
    $amiManager->creerAmi($coach, $cle);
  }
}
// Acceptation d'une demande d'ajout d'un autre coach
elseif (isset($_POST['accepter']))
{
  foreach($_POST['accepter'] as $cle => $value)
  {
    $amiManager->accepterAmi($coach, $cle);
  }

  // Maj liste des amis
  $amis = $amiManager->findAmisByIdCoach($coach->id());
  $_SESSION[ConstantesSession::LISTE_AMIS] = $amis;
}
// Refus d'une demande d'ajout d'un autre coach
elseif (isset($_POST['refuser']))
{
  foreach($_POST['refuser'] as $cle => $value)
  {
    $amiManager->refuserAmi($coach, $cle);
  }
}
// Suppression d'un coach ami
elseif (isset($_POST['supprimer']))
{
  foreach($_POST['supprimer'] as $cle => $value)
  {
    $amiManager->supprimerAmi($coach, $cle);
  }

  // Maj liste des amis
  $amis = $amiManager->findAmisByIdCoach($coach->id());
  $_SESSION[ConstantesSession::LISTE_AMIS] = $amis;
}

$demandesAjout = $amiManager->findDemandeAjoutByIdCoach($coach->id());
$amisDemandesEnCours = $amiManager->findAmiDemandeEnCoursByIdCoach($coach->id());

include_once('vue/gestionAmis.php');
?>
