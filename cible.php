<p>Bonjour !</p>

<p>Je sais comment tu t'appelles, hé hé. Tu t'appelles <?php echo $_POST['nom']; ?> !</p>
<p>Ton code postal est <?php echo $_POST['code_postal']; ?> !</p>

<?php
    $mot_de_passe = $_POST['mot_de_passe'];
    if (isset($mot_de_passe) AND $mot_de_passe ==  "kangourou") // Si le mot de passe est bon
    {
    // On affiche les codes
?>
<p>Si tu veux changer de nom, <a href="accueil.php">clique ici</a> pour revenir à la page formulaire.php.</p>
<?php
    }
    else // Sinon, on affiche un message d'erreur
    {
        echo '<p>Mot de passe incorrect !! <a href="accueil.php">Retour</a></p>';
    }
?>
