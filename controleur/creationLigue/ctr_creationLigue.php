<?php

$manager = new LigueManager($bdd);

if (isset($_POST['creation']) && isset($_POST['nom']) && !empty($_POST['nom']))
{
  $creaLigue = new Ligue($_POST);
  /*$creaLigue = new Ligue(['nom' => $_POST['nom'],
                      'nb_equipe' => $_POST['nbEquipe'],
                      'mode_expert' => $_POST['modeExpert'],
                      'libelle_pari' => $_POST['libellePari']]);*/
  $idLigue = $manager->creerLigue($coach->id(), $creaLigue);
  $creaLigue->setId($idLigue);
}
elseif (isset($_POST['creation']))
{
  $message = 'Le nom de la ligue est obligatoire.';
}

include_once('vue/creationLigue.php');
?>
