<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon super site</title>
        <link rel="stylesheet" href="web/css/commun.css" type="text/css">
        <link rel="stylesheet" href="web/css/taille.css" type="text/css">
    </head>
    <body>

      <div id="bandeau_appli">
        <div id="bandeau_appli_col1">
          <img src="./web/img/logo.jpg" alt="Logo du site" width="100px" height="50px" />
        </div>
        <div id="bandeau_appli_centre">
          <h2>T'es un homme ou une souris ?</h2>
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
      <p class="colonne<?php if($_GET['section'] == 'compteCoach') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'compteCoach') { ?><a href="souris.php?section=compteCoach"><?php } ?>Mon bureau<?php if($_GET['section'] != 'compteCoach') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'gestionConfrere') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'gestionConfrere') { ?><a href="souris.php?section=gestionConfrere"><?php } ?>Mes confrères<?php if($_GET['section'] != 'gestionConfrere') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'creationLigue') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'creationLigue') { ?><a href="souris.php?section=creationLigue"><?php } ?>Créer une ligue<?php if($_GET['section'] != 'creationLigue') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'prepaMercato') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'prepaMercato') { ?><a href="souris.php?section=prepaMercato"><?php } ?>Préparer mon mercato<?php if($_GET['section'] != 'prepaMercato') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'gestionCompteCoach') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'gestionCompteCoach') { ?><a href="souris.php?section=gestionCompteCoach"><?php } ?>Mon compte<?php if($_GET['section'] != 'gestionCompteCoach') { ?></a><?php } ?></p>
      <p class="colonne"><a href="souris.php?deconnexion=true">Déconnexion</a></p>
    </div>
  </div>
</div>
<?php
}
elseif (isset($ligue))
{
?>
<div id="bandeau_ligue">
  <div id="bandeau_ligue_col1"><?php
  echo 'Je suis ' . $coach->nom() . ' (id=' . $coach->id() . ')'; ?></div>
  <div id="bandeau_ligue_centre">
    <div class="colonnes">
      <p class="colonne<?php if($_GET['section'] == 'compteCoach') echo ' menuEnCours'; ?>"><a href="souris.php?section=compteCoach">Mon bureau</a></p>
      <p class="colonne<?php if($_GET['section'] == 'equipe') echo ' menuEnCours'; ?>"><a href="souris.php?section=equipe">Mon équipe</a></p>
      <p class="colonne<?php if($_GET['section'] == 'classementLigue') echo ' menuEnCours'; ?>"><a href="souris.php?section=classementLigue">Classement</a></p>
      <p class="colonne<?php if($_GET['section'] == 'calendrier') echo ' menuEnCours'; ?>"><a href="souris.php?section=calendrier">Calendrier</a></p>
      <p class="colonne<?php if($_GET['section'] == 'forum') echo ' menuEnCours'; ?>"><a href="souris.php?section=forum">Forum</a></p>
      <p class="colonne"><a href="souris.php?deconnexion=true">Déconnexion</a></p>
    </div>
  </div>
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
<div id="contenu">
