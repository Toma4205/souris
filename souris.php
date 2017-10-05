<?php
require('controleur/autoLoad.php');
require_once('modele/connexionSQL.php');
require_once('controleur/constantesSession.php');

if (!isset($_GET['section']) OR $_GET['section'] == 'index')
{
    require_once('controleur/accueil/ctr_accueil.php');
}
elseif ($_GET['section'] == 'compteCoach')
{
    require_once('controleur/compteCoach/ctr_compteCoach.php');
}
elseif ($_GET['section'] == 'gestionAmis')
{
    require_once('controleur/gestionAmi/ctr_gestionAmis.php');
}
