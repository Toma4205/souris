<?php
session_start();
echo 'Session : ' . session_id() . ', statut=' . session_status();

include("commun/entete.php");

if (isset($_POST['nom']) AND isset($_POST['mot_de_passe']) AND $_POST['nom'] != '' AND $_POST['mot_de_passe'] != '')
{
      $_SESSION['nom'] = $_POST['nom'];
      $_SESSION['mot_de_passe'] = $_POST['mot_de_passe'];
      $_SESSION['code_postal'] = $_POST['code_postal'];
?>

<p>Bonjour !</p>

<p>Tu t'appelles <?php echo $_SESSION['nom']; ?> !</p>
<p>Ton code postal est <?php echo $_POST['code_postal']; ?> !</p>

<p>Si tu veux changer de nom, <a href="accueil.php">clique ici</a> pour revenir à la page formulaire.php.</p>
<?php
}
else // Sinon, on affiche un message d'erreur
{
    echo '<p>Nom et mot de passe obligatoires !! <a href="accueil.php">Retour</a></p>';
}
?>
