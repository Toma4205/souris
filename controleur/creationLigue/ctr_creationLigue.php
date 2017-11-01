<?php

$ligueManager = new LigueManager($bdd);
$coachManager = new CoachManager($bdd);

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

if (isset($_POST['creationLigue']))
{
  $equipe = new Equipe(['nom' => $_POST['nomEquipe'],
                      'ville' => $_POST['villeEquipe'],
                      'stade' => $_POST['stadeEquipe']]);
}

include_once('vue/creationLigue.php');
?>
