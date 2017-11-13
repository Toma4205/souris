<?php

$joueurReelManager = new JoueurReelManager($bdd);
$joueurPrepaMercatoManager = new JoueurPrepaMercatoManager($bdd);
$nomenclEquipeManager = new NomenclatureManager($bdd);

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
$joueursReels = $joueurReelManager->findAll();

// TODO MPL voir s'il est possible de stocker qqpart cette nomenclature
$equipes = $nomenclEquipeManager->findNomenclatureEquipe();
// Utilisé dans la vue tableAchatJoueur.php
$tourValide = FALSE;

include_once('vue/prepaMercato.php');
?>
