<?php

if (isset($_POST['majCompte']) && isset($_POST['nom']) && !empty($_POST['nom']))
{
  $manager = new CoachManager($bdd);
  $maj = true;

  if ($_POST['nom'] != $coach->nom())
  {
    if ($manager->existeByNom($_POST['nom']))
    {
      $message = 'Le nom choisi est déjà pris.';
      $maj = false;
    }
  }

  if ($maj)
  {
    $coach->setNom($_POST['nom']);
    $coach->setMail($_POST['mail']);
    $coach->setCode_postal($_POST['codePostal']);

    $manager->majCoach($coach);

    $_SESSION[ConstantesSession::COACH] = $coach;
  }
}
elseif (isset($_POST['majCompte']))
{
  $message = 'Le nom est obligatoire !';
}

include_once('vue/gestionCompteCoach.php');
?>
