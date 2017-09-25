<?php
session_start();
echo 'Session : ' . session_id() . ', statut=' . session_status();

if (isset($_GET['action']))
{
  echo ', Action : ' . $_GET['action'];
}

// Connexion BDD
try
{
    $bdd = new PDO('mysql:host=localhost;dbname=souris;charset=utf8', 'souris', 'souris',
      array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}
catch (Exception $e)
{
    die('Erreur : ' . $e->getMessage());
}

include("commun/entete.php");

// Action : Connexion
if (isset($_GET['action']) AND $_GET['action'] == 'connexion'
    AND isset($_POST['nom']) AND isset($_POST['mot_de_passe'])
    AND $_POST['nom'] != '' AND $_POST['mot_de_passe'] != '')
{
      $_SESSION['nom'] = $_POST['nom'];
      $_SESSION['nom_crea'] = NULL;

      $req = $bdd->prepare('SELECT id FROM coach WHERE nom = ? AND mot_de_passe = ?');
      $req->execute(array($_SESSION['nom'], $_POST['mot_de_passe']));

      $nomExiste = false;
      while ($donnees = $req->fetch())
      {
        $nomExiste = true;
        echo 'Bienvenu coach avec l\'id ' . $donnees['id'] . '.';
        echo '<a href="accueil.php">Retour</a></p>';
      }
      $req->closeCursor();

      if (!$nomExiste)
      {
        // Redirection du visiteur vers la page d'accueil
        header('Location: accueil.php?erreur=connexion');
      }
}
// Action : Inscription
else if (isset($_GET['action']) AND$_GET['action'] == 'inscription'
    AND isset($_POST['nom_crea']) AND isset($_POST['mot_de_passe_crea']) AND isset($_POST['confirm_mot_de_passe_crea'])
    AND $_POST['nom_crea'] != '' AND $_POST['mot_de_passe_crea'] != ''
    AND $_POST['mot_de_passe_crea'] == $_POST['confirm_mot_de_passe_crea'])
{
    $_SESSION['nom'] = NULL;
    $_SESSION['nom_crea'] = $_POST['nom_crea'];

    $req = $bdd->prepare('SELECT id FROM coach WHERE nom = ?');
    $req->execute(array($_SESSION['nom_crea']));

    $nomExiste = false;
    while ($donnees = $req->fetch())
    {
      $nomExiste = true;

      // Redirection du visiteur vers la page d'accueil
      header('Location: accueil.php?erreur=inscription');
    }
    $req->closeCursor();

    if (!$nomExiste)
    {
      $req = $bdd->prepare('INSERT INTO coach(nom, mot_de_passe) VALUES(:nom, :mot_de_passe)');
      $req->execute(array(
         'nom' => $_SESSION['nom_crea'],
         'mot_de_passe' => $_POST['mot_de_passe_crea']));

      echo 'Nouvel arrivant ' . $_SESSION['nom_crea'] . '.';
      echo '<a href="accueil.php">Retour</a></p>';
    }
}
else {
  // Redirection du visiteur vers la page d'accueil
  header('Location: accueil.php?erreur=champs');
}
?>
