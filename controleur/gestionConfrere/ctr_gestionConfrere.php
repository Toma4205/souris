<?php

$coachManager = new CoachManager($bdd);
$confrereManager = new ConfrereManager($bdd);

if (isset($_SESSION[ConstantesSession::LISTE_RECH_CONFRERES]))
{
  $coachsRech = $_SESSION[ConstantesSession::LISTE_RECH_CONFRERES];
}

$confreres = $_SESSION[ConstantesSession::LISTE_CONFRERES];

// Recherche des coachs par nom
if (isset($_POST['rechercher']))
{
  if (isset($_POST['nomCoach']) && strlen(trim($_POST['nomCoach'])) > 0)
  {
    if (!isset($_SESSION[ConstantesSession::RECH_CONFRERE]) || $_SESSION[ConstantesSession::RECH_CONFRERE] != $_POST['nomCoach'])
    {
      $coachsRech = $coachManager->findByNom($_POST['nomCoach'], $coach->id());
      $_SESSION[ConstantesSession::RECH_CONFRERE] = $_POST['nomCoach'];
      $_SESSION[ConstantesSession::LISTE_RECH_CONFRERES] = $coachsRech;
    }
  }
  else
  {
    $message = 'La recherche ne doit pas être vide.';
  }
}
// Ajout d'un confrère dans la liste
elseif (isset($_POST['ajouter']))
{
  foreach($_POST['ajouter'] as $cle => $value)
  {
    $confrereManager->creerConfrere($coach, $cle);
  }
  
  // Maj liste des confreres
  $confreres = $confrereManager->findConfreresByIdCoach($coach->id());
  $_SESSION[ConstantesSession::LISTE_CONFRERES] = $confreres;
  
  $coachsRech = $coachManager->findByNom($_POST['nomCoach'], $coach->id());
  $_SESSION[ConstantesSession::LISTE_RECH_CONFRERES] = $coachsRech;
}
// Suppression d'un coach confrere
elseif (isset($_POST['supprimer']))
{
  foreach($_POST['supprimer'] as $cle => $value)
  {
    $confrereManager->supprimerConfrere($coach, $cle);
  }

  // Maj liste des confreres
  $confreres = $confrereManager->findConfreresByIdCoach($coach->id());
  $_SESSION[ConstantesSession::LISTE_CONFRERES] = $confreres;
}

include_once('vue/gestionConfreres.php');
?>
