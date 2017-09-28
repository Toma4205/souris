<?php
/* On enregistre notre autoload.
function chargerClasse($classname)
{
  require $classname.'.php';
}

spl_autoload_register('chargerClasse');*/

require 'modele/coach/coach.php';
require 'modele/coach/coachManager.php';

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_GET['deconnexion']))
{
  session_destroy();
  // Redirection du visiteur vers la page d'accueil
  header('Location: accueil2.php');
  exit();
}

if (isset($_SESSION['coach'])) // Si la session coach existe, on restaure l'objet.
{
  $coach = $_SESSION['coach'];
}

$bdd = new PDO('mysql:host=localhost;dbname=souris;charset=utf8', 'souris', 'souris',
  array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$manager = new CoachManager($bdd);

if (isset($_POST['inscription']) && isset($_POST['nomCrea']) && isset($_POST['motDePasseCrea'])
  && isset($_POST['confirmMotDePasseCrea']) && !empty($_POST['nomCrea'])
  && !empty($_POST['motDePasseCrea']) && !empty($_POST['confirmMotDePasseCrea']))
{
  $coach = new Coach(['nom' => $_POST['nomCrea'],
                      'motDePasse' => $_POST['motDePasseCrea']]);

  if ($manager->existeByNom($coach->nom()))
  {
    $message = 'Le nom choisi est déjà pris.';
    unset($coach);
  }
  elseif ($_POST['motDePasseCrea'] == $_POST['confirmMotDePasseCrea'])
  {
    $manager->add($coach);
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
                      'motDePasse' => $_POST['motDePasse']]);

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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon super site</title>
    </head>

    <body>

  <body>
    <p>Nombre de coachs : <?= $manager->count() ?></p>
<?php
if (isset($message)) // On a un message à afficher ?
{
  echo '<p>', $message, '</p>'; // Si oui, on l'affiche.
}

if (isset($coach)) // Si on utilise un coach (nouveau ou pas).
{
?>
    <p><a href="?deconnexion=true">Déconnexion</a></p>

    <fieldset>
      <legend>Mes informations</legend>
      <p>
        Nom : <?= htmlspecialchars($coach->nom()) ?><br />
        Mail : <?= $coach->mail() ?>
      </p>
    </fieldset>

<?php
}
else
{
?>
    <form action="" method="post">
      <h1>Déjà inscrit ?</h1>
      <p>Nom coach : <input type="text" name="nom" size="40" value="<?php
          if(isset($_POST['nom']))
          {
            echo htmlspecialchars($_POST['nom']);
          }
        ?>" /></p>
      <p>Mot de passe : <input type="password" name="motDePasse" /></p>
      <br/>
      <input type="submit" value="Se connecter" name="connexion" />
      <br/>
      <h1>Nouveau coach ?</h1>
      <p>Nom coach : <input type="text" name="nomCrea" size="40" value="<?php
          if(isset($_POST['nomCrea']))
          {
            echo htmlspecialchars($_POST['nomCrea']);
          }
        ?>" /></p>
      <p>Mot de passe : <input type="password" name="motDePasseCrea" /></p>
      <p>Confirmation mot de passe : <input type="password" name="confirmMotDePasseCrea" /></p>
      <br/>
      <input type="submit" value="S'inscrire" name="inscription" />
    </form>
<?php
}

// Le pied de page
include("commun/pied_de_page.php");

?>
  </body>
</html>

<?php
if (isset($coach))
{
  $_SESSION['coach'] = $coach;
}
