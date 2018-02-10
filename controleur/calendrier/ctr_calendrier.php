<?php

$equipeManager = new EquipeManager($bdd);
$calLigueManager = new CalendrierLigueManager($bdd);
$compoEquipeManager = new CompoEquipeManager($bdd);

$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $ligue->id());
$calendriers = $calLigueManager->findCalendrierByLigue($ligue->id());
$indexJournee = 1;
if (isset($_POST["journees"]))
{
  $indexJournee = substr($_POST["journees"], 10);
}
else {
  foreach ($calendriers as $cle => $value)
  {
    if ($value->scoreDom() !== null && $value->numJournee() >= $indexJournee)
    {
      $indexJournee = $value->numJournee();
      if ($value->idEquipeDom() == $equipe->id())
      {
        $_POST["id_match"] = $value->id();
      }
      else if ($value->idEquipeExt() == $equipe->id())
      {
        $_POST["id_match"] = $value->id();
      }
    }
  }
}

foreach ($calendriers as $cle => $value)
{
    if ($_POST["id_match"] == $value->id())
    {
      $match = $value;
      $equipeDom = $equipeManager->findEquipeById($value->idEquipeDom());
      $equipeExt = $equipeManager->findEquipeById($value->idEquipeExt());
      $compoDom = $compoEquipeManager->findCompoByEquipeEtCalLigue($value->idEquipeDom(), $value->id());
      $compoExt = $compoEquipeManager->findCompoByEquipeEtCalLigue($value->idEquipeExt(), $value->id());

      $log = 'match=' . $value->id() . ', equipeDom=' . $value->idEquipeDom() . ', equipeExt=' . $value->idEquipeExt();
      if ($compoDom != null) {
        $log = $log . ', compoDom=' . $compoDom->id();
        $joueursDom = $compoEquipeManager->findJoueurCompoByCompo($compoDom->id());
      }
      if ($compoExt != null) {
        $log = $log . ', compExt=' . $compoExt->id();
        $joueursExt = $compoEquipeManager->findJoueurCompoByCompo($compoExt->id());
      }
      echo $log;
      break;
    }
}

include_once('vue/calendrier.php');
?>
