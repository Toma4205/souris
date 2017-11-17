<?php

$calLigueManager = new CalendrierLigueManager($bdd);

$calendriers = $calLigueManager->findCalendrierByLigue($ligue->id());

include_once('vue/calendrier.php');
?>
