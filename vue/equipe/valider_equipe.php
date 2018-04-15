<?php
require_once(__DIR__ . '/../../modele/connexionSQL.php');
require_once(__DIR__ . '/../../modele/compoequipe/compoEquipe.php');
require_once(__DIR__ . '/../../modele/compoequipe/compoEquipeManager.php');
require_once(__DIR__ . '/../../modele/bonusmalus/bonusMalus.php');
require_once(__DIR__ . '/../../modele/bonusmalus/bonusMalusManager.php');

try
{
    // Récupération de la connexion
    $bdd = ConnexionBDD::getInstance();

    $bonusManager = new BonusMalusManager($bdd);
    $compoEquipeManager = new CompoEquipeManager($bdd);

    $compoEquipe = new CompoEquipe([]);
    $tabCompo = [];
    $tabRentrant = [];
    $tabSortant = [];
    $tabNote = [];
    $capitaine = -1;
    $joueurBonus = -1;
    $idEquipe = $_POST['_0'];
    $idCalLigue = $_POST['_1'];

    $compoEquipe->setCode_tactique($_POST['choixTactique']);
    $compoEquipe->setPari_dom($_POST['pariDom']);
    $compoEquipe->setPari_ext($_POST['pariExt']);

    if ($_POST['choixBonus'] != '')
    {
      $compoEquipe->setCode_bonus_malus($_POST['choixBonus']);

      $bonus = new BonusMalus(['code' => $_POST['choixBonus']]);
      if ($_POST['choixJoueurBonus'] != -1 && $_POST[$_POST['choixJoueurBonus']] != -1) {
        $bonus->setId_joueur_reel_equipe($_POST[$_POST['choixJoueurBonus']] );
      }
      if ($_POST['choixJoueurAdvBonus'] != -1) {
        $bonus->setId_joueur_reel_adverse($_POST['choixJoueurAdvBonus']);
      }

      $bonusManager->creerOuMajBonusMalusCompoEquipe($bonus, $idEquipe, $idCalLigue);
    }
    else
    {
      // Réinit du bonus/malus sélectionné éventuellement avant
      $bonusManager->reinitBonusMalusCompoEquipe($idEquipe, $idCalLigue);
    }

    $compoEquipeManager->creerOuMajCompoEquipe($compoEquipe, $idEquipe, $idCalLigue);

    $compoEquipe = $compoEquipeManager->findCompoByEquipeEtCalLigue($idEquipe, $idCalLigue);
    $compoEquipeManager->purgerJoueurCompoEquipe($compoEquipe->id());

    // Valorisation des tab compo, rentrant, sortant et note
    foreach($_POST as $name => $joueur)
    {
      if (substr($name, 0, 8) == 'rentrant') {
        $tabRentrant[explode('_', $name)[1]] = $joueur;
      } elseif (substr($name, 0, 7) == 'sortant') {
        $tabSortant[explode('_', $name)[1]] = $joueur;
      } elseif (substr($name, 0, 4) == 'note') {
        $tabNote[explode('_', $name)[1]] = $joueur;
      } elseif (is_numeric($name)) {
        $tabCompo[$name] = $joueur;
      }
    }

    foreach($_POST as $name => $joueur)
    {
      if (is_numeric($name) && $joueur != -1)
      {
        $isCapitaine = 0;
        if ($_POST["choixCapitaine"] == $name)
        {
          $isCapitaine = 1;
        }

        $avecRempl = FALSE;
        foreach($tabSortant as $numRempl => $idJoueur) {
          if ($idJoueur == $joueur) {
            if (isset($tabRentrant[$numRempl]) && $tabRentrant[$numRempl] != -1
              && isset($tabNote[$numRempl]) && $tabNote[$numRempl] != '')
            {
              $avecRempl = TRUE;
              $compoEquipeManager->creerJoueurCompoEquipeAvecRempl($compoEquipe->id(), $name, $joueur,
                $isCapitaine, $numRempl, $tabRentrant[$numRempl], $tabNote[$numRempl]);
            }
          }
        }

        if ($avecRempl == FALSE) {
          $compoEquipeManager->creerJoueurCompoEquipe($compoEquipe->id(), $name, $joueur, $isCapitaine);
        }
      }
    }
    echo 1;
}
catch (Exception $e)
{
    die('Erreur : ' . $e->getMessage());
    echo 0;
    //echo $e->getMessage();
}

?>
