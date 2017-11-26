<?php

$equipeManager = new EquipeManager($bdd);
$calReelManager = new CalendrierReelManager($bdd);
$calLigueManager = new CalendrierLigueManager($bdd);
$nomenclManager = new NomenclatureManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);
$compoEquipeManager = new CompoEquipeManager($bdd);

$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $ligue->id());
$calReel = $calReelManager->findProchaineJournee();
$calLigue = $calLigueManager->findProchaineJourneeByEquipe($equipe->id());
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

//foreach($_POST as $key => $val) echo '$_POST["'.$key.'"]='.$val.'<br />';

$compoEquipe = $compoEquipeManager->findCompoByEquipeEtCalLigue($equipe->id(), $calLigue->id());
if ($compoEquipe == null)
{
  $compoEquipe = new CompoEquipe(['code_tactique' => ConstantesAppli::TACTIQUE_DEFAUT]);
}

$tabCompo = [];
if (isset($_POST['changerTactique']))
{
  $compoEquipe->setCode_tactique($_POST['choixTactique']);
  $tabCompo = $_POST;
}
elseif (isset($_POST['enregistrer']))
{
  $compoEquipe->setCode_tactique($_POST['choixTactique']);
  $compoEquipe->setCode_bonus_malus(null);
  $compoEquipeManager->creerOuMajCompoEquipe($compoEquipe, $equipe->id(), $calLigue->id());
}

$choixTactique = $nomenclManager->findNomenclatureTactiqueByCode($compoEquipe->codeTactique());

include_once('vue/equipe.php');
?>
