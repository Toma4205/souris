<?php

$equipeManager = new EquipeManager($bdd);
$calReelManager = new CalendrierReelManager($bdd);
$calLigueManager = new CalendrierLigueManager($bdd);
$nomenclManager = new NomenclatureManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);
$compoEquipeManager = new CompoEquipeManager($bdd);
$bonusManager = new BonusMalusManager($bdd);

$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $ligue->id());

// Récupération des calendriers réels et fictifs
if (isset($_POST['journeePrec']))
{
  $calReel = $calReelManager->findJourneeByNumero($_POST['numJourneeCalReel'] - 1);
}
elseif (isset($_POST['journeeSuiv']))
{
  $calReel = $calReelManager->findJourneeByNumero($_POST['numJourneeCalReel'] + 1);
}
else if (isset($_POST['numJourneeCalReel']))
{
  $calReel = $calReelManager->findJourneeByNumero($_POST['numJourneeCalReel']);
}
else
{
  $calReel = $calReelManager->findProchaineJournee();
}

$calLigue = new CalendrierLigue([]);
if ($calReel->numJournee() != null)
{
  $calLigue = $calLigueManager->findProchaineJourneeByCalReel($equipe->id(), $calReel->numJournee());
}

$compoEquipe = new CompoEquipe([]);
$tabCompo = [];
$tabRentrant = [];
$tabSortant = [];
$tabNote = [];
$capitaine = -1;
$joueurBonus = -1;
if (isset($_POST['changerTactique']))
{
  $compoEquipe->setCode_tactique($_POST['choixTactique']);
  $compoEquipe->setCode_bonus_malus($_POST['choixBonus']);
  if (isset($_POST['pariDom'])) {
    $compoEquipe->setPari_dom($_POST['pariDom']);
  }
  if (isset($_POST['pariExt'])) {
    $compoEquipe->setPari_ext($_POST['pariExt']);
  }

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
  $joueurBonus = $_POST["choixJoueurBonus"];
  $joueurAdvBonus = $_POST["choixJoueurAdvBonus"];
}
elseif ($calLigue->id() != null)
{
  $compoEquipe = $compoEquipeManager->findCompoByEquipeEtCalLigue($equipe->id(), $calLigue->id());
  if ($compoEquipe == null)
  {
    $compoEquipe = new CompoEquipe(['code_tactique' => ConstantesAppli::TACTIQUE_DEFAUT]);
  }
  else
  {
    $bonus = $bonusManager->findBonusMalusByEquipeEtCalLigue($equipe->id(), $calLigue->id());
    if ($bonus != null) {
      $joueurBonus = $bonus->idJoueurReelEquipe();
      $joueurAdvBonus = $bonus->idJoueurReelAdverse();
    }

    $joueursCompo = $compoEquipeManager->findJoueurCompoByCompo($compoEquipe->id());
    if (isset($joueursCompo))
    {
      foreach($joueursCompo as $joueur)
      {
        if ($joueur->idJoueurReel() == $joueurBonus) {
          $joueurBonus = $joueur->numero();
        }

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

$avecJourneePrec = false;
$avecJourneeSuiv = false;
$numJournee = '';
if ($calLigue->id() != null)
{
  if ($calLigue->numJournee() > $calLigueManager->findProchaineJourneeByLigue($ligue->id())) {
    $avecJourneePrec = true;
  }
  if ($calLigueManager->findJourneeMaxByLigue($ligue->id()) > $calLigue->numJournee()) {
      $avecJourneeSuiv = true;
  }

  // TODO MPL Mettre en cache application
  $nomenclStyleCoach = $nomenclManager->findNomenclatureStyleCoach();
  $tabNomenclStyleCoach;
  foreach ($nomenclStyleCoach as $key => $value) {
    $tabNomenclStyleCoach[$value->code()] = $value->nomImage();
  }

  $equipeDom = $equipeManager->findEquipeById($calLigue->idEquipeDom());
  $equipeExt = $equipeManager->findEquipeById($calLigue->idEquipeExt());
  $bonusMalus = $bonusManager->findBonusMalusByEquipe($equipe->id(), $calLigue->id());
  $nomenclTactique = $nomenclManager->findNomenclatureTactiqueSelonMode($ligue->modeExpert());
  $joueurs = $joueurEquipeManager->findByEquipe($equipe->id());

  if ($calLigue->nomEquipeDom() != $equipe->nom()) {
    $joueursAdvBonus = $joueurEquipeManager->findByEquipe($calLigue->idEquipeDom());
  }
  else {
    $joueursAdvBonus = $joueurEquipeManager->findByEquipe($calLigue->idEquipeExt());
  }

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
  $matchsCalReel = $calReelManager->findMatchsByJournee($calReel->numJournee());
  $numJournee = $calLigue->numJournee();
}
elseif (isset($calReel) && $ligue->etat() == EtatLigue::EN_COURS)
{
  $numJournee = $calLigueManager->findNumJourneeByLigueCalReel($ligue->id(), $calReel->numJournee());
  if ($numJournee > $calLigueManager->findProchaineJourneeByLigue($ligue->id())) {
    $avecJourneePrec = true;
  }
  if ($calLigueManager->findJourneeMaxByLigue($ligue->id()) > $numJournee) {
      $avecJourneeSuiv = true;
  }
}

echo 'A supp : idCoach=' . $coach->id() . ', idLigue=' . $ligue->id() .
  ', idEquipe=' . $equipe->id() . ', bonus=' . $compoEquipe->codeBonusMalus() . ', calReel=' . $calReel->numJournee() .
  ', calLigue=' . $calLigue->id() . ', journeePrec=' . $avecJourneePrec .
  ', journeeSuiv=' . $avecJourneeSuiv;

include_once('vue/equipe.php');
?>
