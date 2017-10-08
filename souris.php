<?php
require_once('controleur/autoLoad.php');
require_once('modele/connexionSQL.php');
require_once('controleur/constantesSession.php');

session_start();

// Récupération de la connexion
$bdd = ConnexionBDD::getInstance();

// URL de base pour l'application
if (!isset($_GET['section']) OR $_GET['section'] == 'index')
{
    require_once('controleur/accueil/ctr_accueil.php');
}
// Si la SESSION possède un Coach, l'utilisateur est bien idientifié => navigation autorisée
elseif (isset($_SESSION[ConstantesSession::COACH]))
{
    $coach = $_SESSION[ConstantesSession::COACH];

    if ($_GET['section'] == 'compteCoach')
    {
        require_once('controleur/compteCoach/ctr_compteCoach.php');
    }
    elseif ($_GET['section'] == 'gestionCompteCoach')
    {
        require_once('controleur/gestionCompteCoach/ctr_gestionCompteCoach.php');
    }
    elseif ($_GET['section'] == 'gestionConfrere')
    {
        require_once('controleur/gestionConfrere/ctr_gestionConfrere.php');
    }
    elseif ($_GET['section'] == 'creationLigue')
    {
        require_once('controleur/creationLigue/ctr_creationLigue.php');
    }
    elseif ($_GET['section'] == 'prepaMercato')
    {
        require_once('controleur/prepaMercato/ctr_prepaMercato.php');
    }
}
// Sinon, on redirige l'utilisateur vers l'accueil
else
{
    header('Location: souris.php');
    exit();
}
