<?php

$coachManager = new CoachManager($bdd);
$ligueManager = new LigueManager($bdd);
$equipeManager = new EquipeManager($bdd);
$joueurReelManager = new JoueurReelManager($bdd);

// ***************************************
// ***** DEBUT PARTIE CREATION LIGUE *****
// ***************************************

if (isset($_POST['creationLigue']))
{
  $creaLigue = new Ligue(['nom' => $_POST['nom'],
                      'nb_equipe' => $_POST['nbEquipe'],
                      'libelle_pari' => $_POST['libellePari']]);

  if(isset($_POST['modeExpert']))
  {
    $creaLigue->setMode_expert(1);
  }
  else {
    $creaLigue->setMode_expert(0);
  }

  if (isset($_POST['nom']) && !empty($_POST['nom']))
  {
    $idLigue = $ligueManager->creerLigue($coach->id(), $creaLigue);
    $creaLigue->setId($idLigue);

    $_SESSION[ConstantesSession::LIGUE_CREA] = $creaLigue;
  }
  else
  {
    $message = 'Le nom de la ligue est obligatoire.';
  }
}
elseif (isset($_SESSION[ConstantesSession::LIGUE_CREA]))
{
  $creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];
}

// *********************************************
// ***** DEBUT PARTIE GESTION PARTICIPANTS *****
// *********************************************

// Invitation d'un confrère dans la liste
if (isset($_POST['inviter']))
{
  foreach($_POST['inviter'] as $cle => $value)
  {
    $ligueManager->inviterCoachDansLigue($cle, $creaLigue->id());
  }
}
// Validation finale des coachs ayant accepté l'invitation
elseif (isset($_POST['validationFinale']))
{
  $nbCoachAttendu = $creaLigue->nbEquipe() - 1;
  if (isset($_POST['coachInvite']))
  {
    if (count($_POST['coachInvite']) == $nbCoachAttendu)
    {
      $ligueManager->validerParticipants($creaLigue->id(), $_POST['coachInvite']);
      $creaLigue->setEtat(EtatLigue::MERCATO);
      $_SESSION[ConstantesSession::LIGUE_CREA] = $creaLigue;
    }
    else
    {
      $message = 'Vous devez sélectionner ' . $nbCoachAttendu . ' coach(s).';
    }
  }
  else
  {
    $message = 'Vous devez sélectionner ' . $nbCoachAttendu . ' coach(s).';
  }
}

// Si la ligue est en cours de création, on cherche les confrères et les coachs déjà invités
if (isset($creaLigue) && $creaLigue->etat() == EtatLigue::CREATION)
{
  $confreres = $_SESSION[ConstantesSession::LISTE_CONFRERES];
  $coachsInvites = $coachManager->findCoachsInvitesByIdLigue($creaLigue->id());
}

// ****************************************
// ***** DEBUT PARTIE CREATION EQUIPE *****
// ****************************************

if (isset($_POST['creationEquipe']))
{
  $equipe = new Equipe(['nom' => $_POST['nomEquipe'],
                      'ville' => $_POST['villeEquipe'],
                      'stade' => $_POST['stadeEquipe']]);

  if (isset($_POST['nomEquipe']) && !empty($_POST['nomEquipe'])
      && isset($_POST['villeEquipe']) && !empty($_POST['villeEquipe'])
      && isset($_POST['stadeEquipe']) && !empty($_POST['stadeEquipe']))
  {
    $equipeManager->creerEquipe($equipe, $coach->id(), $creaLigue->id());
  }
  else
  {
      $message = 'Le nom, la ville et le stade doivent être renseignés.';
  }
}

// ********************************
// ***** DEBUT PARTIE MERCATO *****
// ********************************

// Si la ligue est validée, on recherche l'équipe et les joueurs achetés
if (isset($creaLigue) && $creaLigue->etat() == EtatLigue::MERCATO)
{
  $equipe = $equipeManager->findEquipeByCoachEtLigue($coach->id(), $creaLigue->id());
  // TODO MPL rechercher les joueur_fictif
  $joueursReelsGB = $joueurReelManager->findByPosition(ConstantesAppli::GARDIEN);
}

if (isset($_POST['validationMercato']))
{
  // TODO MPL enregistrer joueurs achetés
}

include_once('vue/creationLigue.php');
?>
