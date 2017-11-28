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
if (isset($_POST['changerTactique']))
{
  $compoEquipe->setCode_tactique($_POST['choixTactique']);
  $compoEquipe->setCode_bonus_malus($_POST['choixBonus']);
  $tabCompo = $_POST;
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
      $compoEquipeManager->creerJoueurCompoEquipe($compoEquipe->id(), $numero, $joueur, 0, null);
    }
  }

  $tabCompo = $_POST;
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
    echo $compoEquipe->id();
    $joueursCompo = $compoEquipeManager->findJoueurCompoByCompo($compoEquipe->id());
    if (isset($joueursCompo))
    {
      foreach($joueursCompo as $joueur)
      {
        echo $joueur->numero() . "-" . $joueur->idJoueurReel() . '<br/>';
        $tabCompo[$joueur->numero()] = $joueur->idJoueurReel();
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
