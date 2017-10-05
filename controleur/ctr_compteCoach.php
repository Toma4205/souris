<?php
/* On enregistre notre autoload.
function chargerClasse($classname)
{
  require $classname.'.php';
}

spl_autoload_register('chargerClasse');*/

require '/../modele/coach/coach.php';
require '/../modele/coach/coachManager.php';
require '/../modele/ami/ami.php';
require '/../modele/ami/amiManager.php';

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_SESSION[ConstantesSession::COACH]))
{
  if (isset($_POST['creerLigue'])) {
    header('Location: souris.php?section=creationLigue');
  }
  elseif (isset($_POST['gererAmis'])) {
    header('Location: souris.php?section=gestionAmis');
  }
  $coach = $_SESSION[ConstantesSession::COACH];
}
else {
  header('Location: souris.php');
  exit();
}

$managerCoach = new CoachManager($bdd);
$managerAmi = new AmiManager($bdd);

$amis = $managerAmi->findAmisByIdCoach($coach->id());
$nbDemandeAjout = (int) $managerAmi->compterNbDemandeAjout($coach->id());
$_SESSION[ConstantesSession::LISTE_AMIS] = $amis;

include_once('vue/compteCoach.php');
?>
