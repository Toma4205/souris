<?php

$joueurReelManager = new JoueurReelManager($bdd);

$creaLigue = $_SESSION[ConstantesSession::LIGUE_CREA];

if (isset($_POST['validationMercato']))
{
  // TODO MPL enregistrer joueurs achetés
}

include_once('vue/mercatoEquipe.php');
?>
