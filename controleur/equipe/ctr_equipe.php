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
$capitaine = -1;
if (isset($_POST['changerTactique']))
{
  $compoEquipe->setCode_tactique($_POST['choixTactique']);
  $compoEquipe->setCode_bonus_malus($_POST['choixBonus']);
  $tabCompo = $_POST;
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

  foreach($_POST as $numero => $joueur)
  {
    if (is_numeric($numero) && $joueur != -1)
    {
      $isCapitaine = 0;
      if ($_POST["choixCapitaine"] == $numero)
      {
        $isCapitaine = 1;
      }
      $compoEquipeManager->creerJoueurCompoEquipe($compoEquipe->id(), $numero, $joueur, $isCapitaine, null);
    }
  }
  $tabCompo = $_POST;
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
