<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon super site</title>
        <link rel="stylesheet" href="web/css/commun.css" type="text/css">
        <link rel="stylesheet" href="web/css/taille.css" type="text/css">
        <link rel="stylesheet" href="web/css/jquery.dataTables.min.css" type="text/css">
        <script src="web/js/jquery-1.12.4.js"></script>
        <script src="web/js/jquery.dataTables.min.js"></script>
        <script src="web/js/mercato.js"></script>
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
if (isset($creaLigue) && $creaLigue->etat() == EtatLigue::MERCATO)
{
?>
      <div id="bandeau_compte">
        <div id="bandeau_compte_col1"><?php
        echo 'Je suis ' . $coach->nom() . ' (id=' . $coach->id() . ')'; ?></div>
        <div id="bandeau_compte_centre">
          <div class="colonnes">
            <p class="colonne<?php if($_GET['section'] == 'compteCoach') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'compteCoach') { ?><a href="souris.php?section=compteCoach"><?php } ?>Mon bureau<?php if($_GET['section'] != 'compteCoach') { ?></a><?php } ?></p>
            <p class="colonne<?php if($_GET['section'] == 'creationLigue') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'creationLigue') { ?><a href="souris.php?section=creationLigue"><?php } ?>Ligue/Equipe<?php if($_GET['section'] != 'creationLigue') { ?></a><?php } ?></p>
            <p class="colonne<?php if($_GET['section'] == 'mercatoEquipe') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'mercatoEquipe') { ?><a href="souris.php?section=mercatoEquipe"><?php } ?>Mon mercato<?php if($_GET['section'] != 'mercatoEquipe') { ?></a><?php } ?></p>
            <p class="colonne<?php if($_GET['section'] == 'mercatoLigue') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'mercatoLigue') { ?><a href="souris.php?section=mercatoLigue"><?php } ?>Mercato ligue<?php if($_GET['section'] != 'mercatoLigue') { ?></a><?php } ?></p>
            <p class="colonne"><a href="souris.php?deconnexion=true">Déconnexion</a></p>
          </div>
        </div>
</div>
<?php
}
elseif (isset($coach) && !isset($ligue))
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
      <p class="colonne<?php if($_GET['section'] == 'compteCoach') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'compteCoach') { ?><a href="souris.php?section=compteCoach"><?php } ?>Mon bureau<?php if($_GET['section'] != 'compteCoach') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'equipe') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'equipe') { ?><a href="souris.php?section=equipe"><?php } ?>Mon équipe<?php if($_GET['section'] != 'equipe') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'classementLigue') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'classementLigue') { ?><a href="souris.php?section=classementLigue"><?php } ?>Classement<?php if($_GET['section'] != 'classementLigue') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'calendrier') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'calendrier') { ?><a href="souris.php?section=calendrier"><?php } ?>Calendrier<?php if($_GET['section'] != 'calendrier') { ?></a><?php } ?></p>
      <p class="colonne<?php if($_GET['section'] == 'forum') echo ' menuEnCours'; ?>"><?php if($_GET['section'] != 'forum') { ?><a href="souris.php?section=forum"><?php } ?>Conférence de presse<?php if($_GET['section'] != 'forum') { ?></a><?php } ?></p>
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
