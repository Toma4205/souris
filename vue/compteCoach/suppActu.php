<?php
require_once(__DIR__ . '/../../modele/connexionSQL.php');
require_once(__DIR__ . '/../../modele/actualitecoach/actualiteCoachManager.php');

if(isset($_POST['idActu'])) {
    try
    {
    		// Récupération de la connexion
    		$bdd = ConnexionBDD::getInstance();
        $manager = new ActualiteCoachManager($bdd);

        $manager->supprimerActu($_POST['idActu']);
        echo $_POST['idActu'];
    }
    catch (Exception $e)
    {
    		die('Erreur : ' . $e->getMessage());
        echo 0;
    		//echo $e->getMessage();
    }
} else {
    echo 0;
}

?>
