<?php

$equipeManager = new EquipeManager($bdd);
$calReelManager = new CalendrierReelManager($bdd);
$calLigueManager = new CalendrierLigueManager($bdd);
$nomenclManager = new NomenclatureManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);
$compoEquipeManager = new CompoEquipeManager($bdd);

$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $ligue->id());
$calLigue = $calLigueManager->findProchaineJourneeByEquipe($equipe->id());

$compoEquipe = $compoEquipe = new CompoEquipe([]);
$tabCompo = [];
$tabRentrant = [];
$tabSortant = [];
$tabNote = [];
$capitaine = -1;
if (isset($_POST['changerTactique']))
{
  $compoEquipe->setCode_tactique($_POST['choixTactique']);
  $compoEquipe->setCode_bonus_malus($_POST['choixBonus']);
  foreach ($_POST as $name => $valeur)
  {
    if (substr($name, 0, 8) == 'rentrant') {
      $tabRentrant[explode('_', $name)[1]] = $valeur;
    } elseif (substr($name, 0, 7) == 'sortant') {
      $tabSortant[explode('_', $name)[1]] = $valeur;
    } elseif (substr($name, 0, 4) == 'note') {
      $tabNote[explode('_', $name)[1]] = $valeur;
    } elseif (is_numeric($name)) {
      $tabCompo[$name] = $valeur;
    }
  }
  $capitaine = $_POST["choixCapitaine"];
}
elseif (isset($_POST['enregistrer']))
{
  $compoEquipe->setCode_tactique($_POST['choixTactique']);
  if ($_POST['choixBonus'] != -1)
  {
    $compoEquipe->setCode_bonus_malus($_POST['choixBonus']);
  }
  $compoEquipeManager->creerOuMajCompoEquipe($compoEquipe, $equipe->id(), $calLigue->id());

  $compoEquipe = $compoEquipeManager->findCompoByEquipeEtCalLigue($equipe->id(), $calLigue->id());
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
          $avecRempl = TRUE;
          $compoEquipeManager->creerJoueurCompoEquipeAvecRempl($compoEquipe->id(), $name, $joueur,
            $isCapitaine, null, $numRempl, $tabRentrant[$numRempl], $tabNote[$numRempl]);
        }
      }

      if ($avecRempl == FALSE) {
        $compoEquipeManager->creerJoueurCompoEquipe($compoEquipe->id(), $name, $joueur, $isCapitaine, null);
      }
    }
  }
  $capitaine = $_POST["choixCapitaine"];
}
else
{
  $compoEquipe = $compoEquipeManager->findCompoByEquipeEtCalLigue($equipe->id(), $calLigue->id());
  if ($compoEquipe == null)
  {
    $compoEquipe = new CompoEquipe(['code_tactique' => ConstantesAppli::TACTIQUE_DEFAUT]);
  }
  else
  {
    $joueursCompo = $compoEquipeManager->findJoueurCompoByCompo($compoEquipe->id());
    if (isset($joueursCompo))
    {
      foreach($joueursCompo as $joueur)
      {
        $tabCompo[$joueur->numero()] = $joueur->idJoueurReel();
        if ($joueur->capitaine() == 1)
        {
          $capitaine = $joueur->numero();
        }
        if ($joueur->noteMinRemplacement() != null) {
          $tabRentrant[$joueur->numeroRemplacement()] = $joueur->idJoueurReelRemplacant();
          $tabSortant[$joueur->numeroRemplacement()] = $joueur->idJoueurReel();
          $tabNote[$joueur->numeroRemplacement()] = $joueur->noteMinRemplacement();
        }
      }
    }
  }
}

$calReel = $calReelManager->findProchaineJournee();
$nomenclTactique = $nomenclManager->findNomenclatureTactiqueSelonMode($ligue->modeExpert());
$joueurs = $joueurEquipeManager->findByEquipe($equipe->id());
if (isset($joueurs))
{
  $gb = [];
  $def = [];
  $mil = [];
  $att = [];

  foreach($joueurs as $joueur)
  {
    if ($joueur->position() == ConstantesAppli::GARDIEN)
    {
      $gb[] = $joueur;
    } elseif ($joueur->position() == ConstantesAppli::DEFENSEUR)
    {
      $def[] = $joueur;
    } elseif ($joueur->position() == ConstantesAppli::MILIEU)
    {
      $mil[] = $joueur;
    } elseif ($joueur->position() == ConstantesAppli::ATTAQUANT)
    {
      $att[] = $joueur;
    }
  }
}

$choixTactique = $nomenclManager->findNomenclatureTactiqueByCode($compoEquipe->codeTactique());

include_once('vue/equipe.php');
?>
