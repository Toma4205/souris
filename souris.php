<?php

require_once('modele/connexionSQL.php');
require_once('controleur/constantesSession.php');

if (!isset($_GET['section']) OR $_GET['section'] == 'index')
{
    require_once('controleur/ctr_accueil.php');
}
elseif ($_GET['section'] == 'compteCoach')
{
    require_once('controleur/ctr_compteCoach.php');
}
elseif ($_GET['section'] == 'gestionAmis')
{
    require_once('controleur/ctr_gestionAmis.php');
}
