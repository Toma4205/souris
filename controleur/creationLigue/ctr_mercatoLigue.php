<?php

$ligueManager = new LigueManager($bdd);

$creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];
// Pour rafraichir les données
$_SESSION[ConstantesSession::LIGUE_CREA] = $ligueManager->findLigueById($creaLigue->id());
$creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];

if ($creaLigue->tourMercato() > 1)
{
  $joueurEquipeManager = new JoueurEquipeManager($bdd);
  $joueursEquipe = $joueurEquipeManager->findJoueurTourMercatoTermine($creaLigue->id(), $creaLigue->tourMercato());

  // Tri de la liste
  $joueursEquipeTries = [];
  foreach($joueursEquipe as $cle => $joueur)
  {
    if ($joueur->dateValidation() != null)
    {
      $joueursEquipeTries[] = $joueur;
      unset($joueursEquipe[$cle]);

      foreach($joueursEquipe as $cle2 => $joueur2)
      {
        if ($joueur2->id() == $joueur->id() && $joueur2->nomEquipe() != $joueur->nomEquipe())
        {
          $joueursEquipeTries[] = $joueur2;
        }
      }
    }
  }
}
include_once('vue/mercatoLigue.php');
?>
