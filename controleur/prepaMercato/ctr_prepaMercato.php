<?php

$joueurReelManager = new JoueurReelManager($bdd);
$joueurPrepaMercatoManager = new JoueurPrepaMercatoManager($bdd);

if (isset($_POST['reinitialisation']))
{
  $joueurPrepaMercatoManager->purgerMercatoCoach($coach->id());

  $message = 'Votre mercato a bien été réinitialisé.';
}
elseif (isset($_POST['validationMercato']))
{
  $joueurPrepaMercatoManager->purgerMercatoCoach($coach->id());
  foreach ($_POST as $cle => $prixAchat)
  {
    if (substr($cle, 0, 5) === "name_")
    {
      $idJoueur = substr($cle, 5);
      $joueurPrepaMercatoManager->creerJoueurPrepaMercato($coach->id(), $idJoueur, $prixAchat);
    }
  }

  $message = 'Votre mercato a bien été sauvegardé.';
}

$budgetRestant = ConstantesAppli::BUDGET_INIT;

// Rech joueurs enregistrés
$joueursPrepaMercato = $joueurPrepaMercatoManager->findByCoach($coach->id());
// Rech joueurs réels
$joueursReelsGB = $joueurReelManager->findByPosition(ConstantesAppli::GARDIEN);
$joueursReelsDEF = $joueurReelManager->findByPosition(ConstantesAppli::DEFENSEUR);
$joueursReelsMIL = $joueurReelManager->findByPosition(ConstantesAppli::MILIEU);
$joueursReelsATT = $joueurReelManager->findByPosition(ConstantesAppli::ATTAQUANT);

include_once('vue/prepaMercato.php');
?>
