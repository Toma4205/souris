<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon super site</title>
        <link rel="stylesheet" href="web/css/commun.css" type="text/css">
    </head>
    <body>

      <div id="bandeau_appli">
        <div id="bandeau_appli_col1">
          <img src="./web/img/logo.jpg" alt="Logo du site" width="100px" height="50px" />
        </div>
        <div id="bandeau_appli_centre">
        Titre du site ?
        </div>
      </div>
<?php
if (isset($coach) && !isset($ligue))
{
?>
<div id="bandeau_compte">
  <div id="bandeau_compte_col1"><?php
  echo 'Je suis ' . $coach->nom() . ' (id=' . $coach->id() . ')'; ?></div>
  <div id="bandeau_compte_centre">
    <div class="colonnes">
      <p class="colonne"><a href="souris.php?section=compteCoach">Mon compte</a></p>
      <p class="colonne"><a href="souris.php?section=gestionConfrere">Mes confrères</a></p>
      <p class="colonne"><a href="souris.php?section=creationLigue">Créer une ligue</a></p>
      <p class="colonne"><a href="souris.php?section=prepaMercato">Préparer mon mercato</a></p>
      <p class="colonne"><a href="souris.php?deconnexion=true">Déconnexion</a></p>
    </div>
  </div>
</div>
<?php
  //echo 'Je suis ' . $coach->nom() . ' (id=' . $coach->id() . ')</p></div>';
}
elseif (isset($ligue))
{
?>
      <div id="bandeau_ligue">
      </div>
 <?php
 }
 ?>


<?php
if (isset($message)) // On a un message à afficher ?
{
  echo '<p><b>', $message, '</b></p>'; // Si oui, on l'affiche.
}
 ?>
