<?php

if (isset($_GET['deconnexion']))
{
  $_SESSION = array();
  session_destroy();
  // Redirection du visiteur vers la page d'accueil
  header('Location: souris.php');
  exit();
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
  if ('admin' == $_POST['nom'])
  {
    header('Location: admin.php');
  }
  $coach = new Coach(['nom' => $_POST['nom'],
                      'mot_de_passe' => $_POST['motDePasse']]);

  $coach = $manager->findByNomMotDePasse($coach);
  if (null == $coach->id())
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
  $message = 'Couple nom/mot de passe invalide !';
}

if (isset($coach)) // Si on utilise un coach (nouveau ou pas).
{
  $_SESSION[ConstantesSession::COACH] = $coach;
  // Redirection du visiteur vers la page d'accueil
  header('Location: souris.php?section=compteCoach');
}

include_once('vue/accueil.php');
?>
