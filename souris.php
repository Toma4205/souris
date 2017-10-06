<?php
require_once('controleur/autoLoad.php');
require_once('modele/connexionSQL.php');
require_once('controleur/constantesSession.php');

session_start();

// Récupération de la connexion
$bdd = ConnexionBDD::getInstance();

if (!isset($_GET['section']) OR $_GET['section'] == 'index')
{
    require_once('controleur/accueil/ctr_accueil.php');
}
elseif (isset($_SESSION[ConstantesSession::COACH]))
{
    $coach = $_SESSION[ConstantesSession::COACH];

    if ($_GET['section'] == 'compteCoach')
    {
        require_once('controleur/compteCoach/ctr_compteCoach.php');
    }
    elseif ($_GET['section'] == 'gestionConfreres')
    {
        require_once('controleur/gestionConfrere/ctr_gestionConfreres.php');
    }
}
else
{
    header('Location: souris.php');
    exit();
}
