<?php

function traiterFinTourMercato(LigueManager $ligueManager, JoueurEquipeManager $joueurEquipeManager,
  $idLigue, $tourMercato)
{
  $joueurEquipeManager->affecterJoueurAEquipe($idLigue, $tourMercato);
  $_SESSION[ConstantesSession::LIGUE_CREA] = $ligueManager->findLigueById($idLigue);

  // Redirection du visiteur vers la page d'accueil
  header('Location: index.php?section=mercatoLigue');
}

$ligueManager = new LigueManager($bdd);
$equipeManager = new EquipeManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);

$creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];
// Pour rafraichir les données
$_SESSION[ConstantesSession::LIGUE_CREA] = $ligueManager->findLigueById($creaLigue->id());
$creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];
$tourMercato = $creaLigue->tourMercato();

// Permet de vérifier si le mercato peut se dérouler
$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $creaLigue->id());

// TODO MPL Que faire si un coach n'a plus assez d'argent pour créer son équipe ??

// Validation du tour du Mercato
if (isset($_POST['validationMercato']))
{
  foreach ($_POST as $cle => $prixAchat)
  {
    if (substr($cle, 0, 5) === "name_")
    {
      $idJoueur = substr($cle, 5);
      $joueurEquipeManager->creerJoueurEquipe($creaLigue->id(), $equipe->id(), $idJoueur, $prixAchat, $tourMercato);
    }
  }

  if ($joueurEquipeManager->isTourMercatoTermine($creaLigue->id(), $tourMercato))
  {
    traiterFinTourMercato($ligueManager, $joueurEquipeManager, $creaLigue->id(), $tourMercato);
  }
}
elseif (isset($_POST['clotureMercato']))
{
  $equipeManager->fermerMercato($equipe->id());
  if ($equipeManager->isTousMercatoFerme($creaLigue->id()))
  {
    // Création calendrier
    $tabIdEquipe = $equipeManager->findIdEquipeByLigue($creaLigue->id());

    $calReelManager = new CalendrierReelManager($bdd);
    $calReel = $calReelManager->findProchaineJournee();

    // TODO Voir comment gérer ce cas
    if ($calReel->numJournee() != null && ($calReel->numJournee() + (sizeof($tabIdEquipe) - 1) * 2))
    {
      $calLigueManager = new CalendrierLigueManager($bdd);
      $calLigueManager->calculerCalendrier($creaLigue->id(), $tabIdEquipe, $calReel->numJournee());

      // Maj état ligue MERCATO => EN_COURS
      $ligueManager->mettreAJourEtatLigue(EtatLigue::EN_COURS, $creaLigue->id());

      $_SESSION[ConstantesSession::LIGUE] = $ligueManager->findLigueById($creaLigue->id());
      // Redirection du visiteur vers la page d'accueil
      header('Location: index.php?section=equipe');
    }
    else {
      echo 'TODO : plus assez de journées pour compléter le calendrier de la ligue !!';
    }
  }
  elseif ($joueurEquipeManager->isTourMercatoTermine($creaLigue->id(), $tourMercato))
  {
    traiterFinTourMercato($ligueManager, $joueurEquipeManager, $creaLigue->id(), $tourMercato);
  }
  else
  {
    $equipe->setFin_mercato(TRUE);
  }
}

if (isset($equipe))
{
  $tourValide = $joueurEquipeManager->isTourMercatoValide($equipe->id(), $tourMercato);

  if ($equipe->finMercato() == TRUE)
  {
    $tourValide = TRUE;
    $joueursAchetes = $joueurEquipeManager->findByEquipe($equipe->id());
  }
  elseif ($tourValide == FALSE)
  {
    $joueurReelManager = new JoueurReelManager($bdd);
    $nomenclEquipeManager = new NomenclatureManager($bdd);

    $equipes = $nomenclEquipeManager->findNomenclatureEquipe();
    $joueursAchetes = $joueurEquipeManager->findByEquipe($equipe->id());

    if ($tourMercato == 1)
    {
      $joueurPrepaMercatoManager = new JoueurPrepaMercatoManager($bdd);
      $joueursPrepaMercato = $joueurPrepaMercatoManager->findByCoach($coach->id());

      $joueursReels = $joueurReelManager->findAll();
    }
    else {
      $joueursReels = $joueurReelManager->findJoueurRestantByLigue($creaLigue->id(), $tourMercato);
    }
  } else {
    $joueursAchetes = $joueurEquipeManager->findMercatoEnCoursByEquipe($equipe->id(), $tourMercato);
  }

  if ($tourValide) {
    $equipesEnAttente = $equipeManager->findEquipeEnAttenteMercato($creaLigue->id(), $tourMercato);
  }
}

include_once('vue/mercatoEquipe.php');
?>
