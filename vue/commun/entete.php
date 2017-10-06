<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon super site</title>
        <link rel="stylesheet" href="web/css/commun.css" type="text/css">
    </head>
    <body>

<?php
if (isset($coach))
{
  echo '<p><b><a href="souris.php?section=compteCoach">Mon compte</a> - <a href="souris.php?deconnexion=true">Déconnexion</a></b> Je suis ' . $coach->nom() . ' (id=' . $coach->id() . ')</p>';
}
if (isset($message)) // On a un message à afficher ?
{
  echo '<p><b>', $message, '</b></p>'; // Si oui, on l'affiche.
}
 ?>
