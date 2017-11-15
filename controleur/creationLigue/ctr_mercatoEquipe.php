<?php

$ligueManager = new LigueManager($bdd);
$equipeManager = new EquipeManager($bdd);
$joueurEquipeManager = new JoueurEquipeManager($bdd);

$creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];
// Pour rafraichir les données
$_SESSION[ConstantesSession::LIGUE_CREA] = $ligueManager->findLigueById($creaLigue->id());
$creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];

// Permet de vérifier si le mercato peut se dérouler
$equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $creaLigue->id());

// TODO MPL Que faire si un coach n'a plus assez d'argent pour créer son équipe ??

// Validation du tour du Mercato
if (isset($_POST['validationMercato']))
{
  $tourMercato = $ligueManager->findTourMercato($creaLigue->id());

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
    $joueurEquipeManager->affecterJoueurAEquipe($creaLigue->id(), $tourMercato);
    $_SESSION[ConstantesSession::LIGUE_CREA] = $ligueManager->findLigueById($creaLigue->id());

    // Redirection du visiteur vers la page d'accueil
    header('Location: souris.php?section=mercatoLigue');
  }
}
elseif (isset($_POST['clotureMercato']))
{
  // TODO MPL fermerMercato + afficher effectif complet
}

if ($equipe)
{
  $tourMercato = $ligueManager->findTourMercato($creaLigue->id());
  $tourValide = $joueurEquipeManager->isTourMercatoValide($equipe->id(), $tourMercato);

  if ($tourValide == FALSE)
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
}

include_once('vue/mercatoEquipe.php');
?>
