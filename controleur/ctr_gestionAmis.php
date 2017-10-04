<?php
/* On enregistre notre autoload.
function chargerClasse($classname)
{
  require $classname.'.php';
}

spl_autoload_register('chargerClasse');*/

require '/../modele/coach/coach.php';
require '/../modele/coach/coachManager.php';
require '/../modele/ami/amiManager.php';

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_SESSION['coach']))
{
  if (isset($_POST['retour']))
  {
    header('Location: souris.php?section=compteCoach');
  }
  $coach = $_SESSION['coach'];
}
else {
  header('Location: souris.php');
  exit();
}

$coachManager = new CoachManager($bdd);
$amiManager = new AmiManager($bdd);

if (isset($_POST['rechercher']))
{
  if (isset($_POST['nomCoach']) && !empty($_POST['nomCoach']))
  {
    if (!isset($_SESSION['rechAmi']) || $_SESSION['rechAmi'] != $_POST['nomCoach'])
    {
      $coachs = $coachManager->findByNom($_POST['nomCoach'], $coach->id());
      $_SESSION['rechAmi'] = $_POST['nomCoach'];
      $_SESSION['listeRechAmi'] = $coachs;
    }
    else
    {
      $coachs = $_SESSION['listeRechAmi'];
    }
  }
  else
  {
    $message = 'La recherche ne doit pas être vide.';
  }
}
elseif (isset($_POST['ajouter']))
{
  $coachs = $_SESSION['listeRechAmi'];

  foreach($_POST['ajouter'] as $cle => $value)
  {
    $amiManager->creerAmi($coach, $cle);
  }

  $amis = $manager->findCoachAmiById($coach->id());
  $_SESSION['listeAmis'] = $amis;
  // TODO MPL supprimer coach ajouté de la liste
}

include_once('vue/gestionAmis.php');
?>
