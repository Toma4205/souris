<?php

$calLigueManager = new CalendrierLigueManager($bdd);
$compoEquipeManager = new CompoEquipeManager($bdd);

$calendriers = $calLigueManager->findCalendrierByLigue($ligue->id());
$indexJournee = 1;
if (isset($_POST["journees"]))
{
  $indexJournee = substr($_POST["journees"], 10);
}

if (isset($_POST["id_match"]))
{
  foreach ($calendriers as $cle => $value)
  {
    if ($_POST["id_match"] == $value->id())
    {
      $compoDom = $compoEquipeManager->findCompoByEquipeEtCalLigue($value->idEquipeDom(), $value->id());
      $compoExt = $compoEquipeManager->findCompoByEquipeEtCalLigue($value->idEquipeExt(), $value->id());
      echo 'IdMatch=' . $value->id() . ', IdCompoDom=' . $compoDom->id() . ', IdCompExt=' . $compoExt->id();
      if ($compoDom != null) {
        $joueursDom = $compoEquipeManager->findJoueurCompoByCompo($compoDom->id());
      }
      if ($compoExt != null) {
        $joueursExt = $compoEquipeManager->findJoueurCompoByCompo($compoExt->id());
      }
      break;
    }
  }
}

include_once('vue/calendrier.php');
?>
