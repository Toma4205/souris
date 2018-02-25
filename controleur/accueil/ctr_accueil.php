<?php

if (isset($_GET['deconnexion']))
{
  $_SESSION = array();
  session_destroy();
  // Redirection du visiteur vers la page d'accueil
  header('Location: index.php');
  exit();
}

$manager = new CoachManager($bdd);

if (isset($_POST['inscription']) && isset($_POST['nomCrea']) && isset($_POST['motDePasseCrea'])
  && isset($_POST['confirmMotDePasseCrea']) && isset($_POST['mailCrea']) && !empty($_POST['nomCrea'])
  && !empty($_POST['motDePasseCrea']) && !empty($_POST['confirmMotDePasseCrea']) && !empty($_POST['mailCrea']))
{
  // TODO MPL vérifier validité (regex) du mail + envoi mail
  $coach = new Coach(['nom' => $_POST['nomCrea'],
                      'mot_de_passe' => $_POST['motDePasseCrea'],
                      'mail' => $_POST['mailCrea']]);

  if ($manager->existeByMail($coach->mail()))
  {
    $messageInscr = 'Un compte existe déjà pour cette adresse mail.';
    unset($coach);
  }
  elseif ($_POST['motDePasseCrea'] == $_POST['confirmMotDePasseCrea'])
  {
    $manager->creerCoach($coach);
    $coach = $manager->findByNomMotDePasse($coach);
  }
  else
  {
    $messageInscr = 'Les mots de passe sont différents !';
    unset($coach);
  }
}
elseif (isset($_POST['connexion']) && isset($_POST['mail']) && isset($_POST['motDePasse'])
  && !empty($_POST['mail']) && !empty($_POST['motDePasse']))
{
  if ('admin@mail.fr' == $_POST['mail'])
  {
    header('Location: admin.php');
  }
  $coach = new Coach(['mail' => $_POST['mail'],
                      'mot_de_passe' => $_POST['motDePasse']]);

  $coach = $manager->findByMailMotDePasse($coach);
  if (null == $coach->id())
  {
    $messageConn = 'Couple mail/mot de passe invalide !';
    unset($coach);
  }
}
elseif(isset($_POST['inscription']))
{
  $messageInscr = 'Pour s\'inscrire, veuillez saisir tous les champs.';
}
elseif(isset($_POST['connexion']))
{
  $messageConn = 'Couple nom/mot de passe invalide !';
}

if (isset($coach)) // Si on utilise un coach (nouveau ou pas).
{
  $_SESSION[ConstantesSession::COACH] = $coach;
  // Redirection du visiteur vers la page d'accueil
  header('Location: index.php?section=compteCoach');
}

include_once('vue/accueil.php');
?>
