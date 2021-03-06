<?php

$manager = new CoachManager($bdd);

if (isset($_POST['majCompte']) && isset($_POST['nom']) && !empty($_POST['nom']))
{
    $coach->setNom($_POST['nom']);
    $coach->setMail($_POST['mail']);
    $coach->setCode_postal($_POST['codePostal']);

    if(isset($_POST['affLigueMasquee']))
    {
      $coach->setAff_ligue_masquee(1);
    }
    else {
      $coach->setAff_ligue_masquee(0);
    }

    $manager->majCoach($coach);

    $_SESSION[ConstantesSession::COACH] = $coach;
}
elseif (isset($_POST['majCompte']))
{
  $message = 'Le nom est obligatoire !';
}
elseif (isset($_POST['majMotDePasse']))
{
  if (isset($_POST['motDePasseActuel']) && !empty($_POST['motDePasseActuel'])
    && isset($_POST['motDePasseCrea']) && !empty($_POST['motDePasseCrea'])
    && isset($_POST['confirmMotDePasseCrea']) && !empty($_POST['confirmMotDePasseCrea']))
  {
    if ($_POST['motDePasseCrea'] == $_POST['confirmMotDePasseCrea'])
    {
      $coachAMaj = new Coach(['nom' => $coach->nom(),
                          'mot_de_passe' => $_POST['motDePasseActuel']]);

      $coachAMaj = $manager->findByNomMotDePasse($coachAMaj);
      if (null == $coachAMaj->id())
      {
        $messageMdp = 'Mot de passe actuel invalide !';
      }
      else {
        $manager->majMdpCoach($coach->id(), $_POST['motDePasseCrea']);
      }
    }
    else {
      $messageMdp = 'Les nouveaux mots de passe sont différents !';
    }
  }
  else {
    $messageMdp = 'Les 3 champs sont obligatoires !';
  }
}

include_once('vue/gestionCompteCoach.php');
?>
