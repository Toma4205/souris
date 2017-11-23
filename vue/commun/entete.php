<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>CDD ou RDS</title>
        <link rel="stylesheet" href="web/css/commun.css" type="text/css">
        <link rel="stylesheet" href="web/css/taille.css" type="text/css">
        <link rel="stylesheet" href="web/css/jquery.dataTables.min.css" type="text/css">
        <script src="web/js/jquery-1.12.4.js"></script>
        <script src="web/js/jquery.dataTables.min.js"></script>
        <?php
          if (isset($vueJs))
          {
            echo '<script src="web/js/' . $vueJs . '"></script>';
          }
         ?>
    </head>
    <body>
      <div id="contenu_body">
        <header id="bandeau_appli">
            <div id="bandeau_appli_image">
              <img src="./web/img/logo.jpg" alt="Logo du site" width="100px" height="inherit" />
            </div>
            <p>T'es un homme ou une souris ?</p>
        </header>

<?php
// Si authentification
if (isset($coach))
{
  // Affichage des menus
  function afficherMenu($section, $libMenu, $nav1Ligne)
  {
    echo '<p';
    if($_GET['section'] == $section || $nav1Ligne) {
      echo ' class="';
      if ($_GET['section'] == $section) echo 'menuEnCours';
      if ($nav1Ligne) echo ' nav1ligne';
      echo '"';
    }
    echo '>';
    if($_GET['section'] != $section) echo '<a href="souris.php?section=' . $section . '">';
    echo $libMenu;
    if($_GET['section'] != $section) echo '</a>';
    echo '</p>';
  }

  echo '<nav>';

  if (isset($creaLigue) && $creaLigue->etat() == EtatLigue::MERCATO)
  {
    afficherMenu('compteCoach', 'Mon bureau', true);
    afficherMenu('creationLigue', 'Ligue/Equipe', true);
    afficherMenu('mercatoEquipe', 'Mon mercato', true);
    afficherMenu('mercatoLigue', 'Mercato ligue', true);
  }
  elseif (isset($coach) && !isset($ligue))
  {
    afficherMenu('compteCoach', 'Mon bureau', false);
    afficherMenu('gestionConfrere', 'Mes confrères', false);
    afficherMenu('creationLigue', 'Créer une ligue', false);
    afficherMenu('prepaMercato', 'Préparer mon mercato', false);
    afficherMenu('gestionCompteCoach', 'Mon compte', false);
  }
  elseif (isset($ligue))
  {
    afficherMenu('compteCoach', 'Mon bureau', true);
    afficherMenu('equipe', 'Mon équipe', true);
    afficherMenu('classementLigue', 'Classement', true);
    afficherMenu('calendrier', 'Calendrier', true);
    afficherMenu('forum', 'Conf. de presse', true);
  }

  echo '<p class="nav1ligne"><a href="souris.php?deconnexion=true">Déconnexion</a></p>';
  echo '</nav>';
}
?>

<div class="fond">
  <div id="contenu" class="colonnes">
