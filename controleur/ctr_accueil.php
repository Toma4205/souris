<?php
/* On enregistre notre autoload.
function chargerClasse($classname)
{
  require $classname.'.php';
}

spl_autoload_register('chargerClasse');*/

require '/../modele/coach/coach.php';
require '/../modele/coach/coachManager.php';

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_GET['deconnexion']))
{
  session_destroy();
  // Redirection du visiteur vers la page d'accueil
  header('Location: souris.php');
  exit();
}

if (isset($_SESSION[ConstantesSession::COACH])) // Si la session coach existe, on restaure l'objet.
{
  $coach = $_SESSION[ConstantesSession::COACH];
}

$manager = new CoachManager($bdd);

if (isset($_POST['inscription']) && isset($_POST['nomCrea']) && isset($_POST['motDePasseCrea'])
  && isset($_POST['confirmMotDePasseCrea']) && !empty($_POST['nomCrea'])
  && !empty($_POST['motDePasseCrea']) && !empty($_POST['confirmMotDePasseCrea']))
{
  $coach = new Coach(['nom' => $_POST['nomCrea'],
                      'mot_de_passe' => $_POST['motDePasseCrea']]);

  if ($manager->existeByNom($coach->nom()))
  {
    $message = 'Le nom choisi est déjà pris.';
    unset($coach);
  }
  elseif ($_POST['motDePasseCrea'] == $_POST['confirmMotDePasseCrea'])
  {
    $manager->creerCoach($coach);
    $coach = $manager->findByNomMotDePasse($coach);
  }
  else
  {
    $message = 'Les mots de passe sont différents !';
    unset($coach);
  }
}
elseif (isset($_POST['connexion']) && isset($_POST['nom']) && isset($_POST['motDePasse'])
  && !empty($_POST['nom']) && !empty($_POST['motDePasse']))
{
  $coach = new Coach(['nom' => $_POST['nom'],
                      'mot_de_passe' => $_POST['motDePasse']]);

  if ($manager->existeByNomMotDePasse($coach))
  {
    $coach = $manager->findByNomMotDePasse($coach);
  }
  else
  {
    $message = 'Couple nom/mot de passe invalide !';
    unset($coach);
  }
}
elseif(isset($_POST['inscription']))
{
  $message = 'Pour s\'inscrire, veuillez saisir les 3 champs.';
}
elseif(isset($_POST['connexion']))
{
  $message = 'Pour se connecter, veuillez saisir les 2 champs.';
}

if (isset($coach)) // Si on utilise un coach (nouveau ou pas).
{
  $_SESSION[ConstantesSession::COACH] = $coach;
  // Redirection du visiteur vers la page d'accueil
  header('Location: souris.php?section=compteCoach');
}

include_once('vue/accueil.php');
?>
