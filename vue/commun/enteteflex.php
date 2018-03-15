<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>ClassiCoach</title>
        <link rel="stylesheet" href="web/css/commun.css" type="text/css">
        <link rel="stylesheet" href="web/css/taille.css" type="text/css">
        <link rel="stylesheet" href="web/css/jquery.dataTables.min.css" type="text/css">
        <?php
          if (isset($vueCss))
          {
            echo '<link rel="stylesheet" href="web/css/' . $vueCss . '" type="text/css">';
          }
         ?>
        <link rel="icon" type="image/png" href="web/img/classicoach_minimalist_logo.png" />
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
              <img src="./web/img/classicoach_petit.png" alt="ClassiCoach" width="100px" height="inherit" />
            </div>
            <p class="bandeau_appli_titre">
              <?php
              // Si ligue en cours
              if (isset($ligue))
              {
                echo '<p class="lienBandeau"><a href="index.php?section=compteCoach">Bureau</a>   -   <span class="nomLigueBandeau">' . $ligue->nom() . '</span></p>';
              }
              elseif (isset($creaLigue) && $creaLigue->etat() == EtatLigue::MERCATO)
              {
                echo '<p class="lienBandeau"><a href="index.php?section=compteCoach">Bureau</a>   -   <span class="nomLigueBandeau">' . $creaLigue->nom() . '</span></p>';
              } else {
                echo 'T\'es un homme ou une souris ?';
              }
              ?>
            </p>
            <?php
            // Si authentification
            if (isset($coach))
            {
              echo '<p class="deconnexion width_110px"><a href="index.php?deconnexion=true">Déconnexion</a></p>';
            }
            ?>
        </header>

<?php
// Si authentification
if (isset($coach))
{
  // Affichage des menus
  function afficherMenu($section, $libMenu)
  {
    echo '<p';
    if($_GET['section'] == $section) {
      echo ' class="menuEnCours"';
    }
    echo '>';
    if($_GET['section'] != $section) echo '<a href="index.php?section=' . $section . '">';
    echo $libMenu;
    if($_GET['section'] != $section) echo '</a>';
    echo '</p>';
  }

  echo '<nav>';

  if (isset($creaLigue) && $creaLigue->etat() == EtatLigue::MERCATO)
  {
    afficherMenu('compteCoach', 'Mon bureau');
    afficherMenu('creationLigue', 'Ligue/Equipe');
    afficherMenu('mercatoEquipe', 'Mon mercato');
    afficherMenu('mercatoLigue', 'Mercato ligue');
  }
  elseif (isset($coach) && !isset($ligue))
  {
    afficherMenu('compteCoach', 'Mon bureau');
    afficherMenu('gestionConfrere', 'Mes confrères');
    afficherMenu('creationLigue', 'Créer une ligue');
    afficherMenu('prepaMercato', 'Préparer mon mercato');
    afficherMenu('gestionCompteCoach', 'Mon compte');
  }
  elseif (isset($ligue))
  {
    afficherMenu('compteCoach', 'Mon bureau');
    afficherMenu('equipe', 'Mon équipe');
    afficherMenu('classementLigue', 'Classement');
    afficherMenu('calendrier', 'Calendrier');
    afficherMenu('forum', 'Conf. de presse');
  }

  echo '</nav>';
}
?>

<div class="fond">
  <form id="formPrincipal" action="" method="post">
    <div id="contenu">
      <div class="conteneurColumn width_90pc">
