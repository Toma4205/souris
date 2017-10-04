<?php

include_once('modele/connexionSQL.php');

if (!isset($_GET['section']) OR $_GET['section'] == 'index')
{
    include_once('controleur/ctr_accueil.php');
}
elseif ($_GET['section'] == 'compteCoach')
{
    include_once('controleur/ctr_compteCoach.php');
}
elseif ($_GET['section'] == 'gestionAmis')
{
    include_once('controleur/ctr_gestionAmis.php');
}
