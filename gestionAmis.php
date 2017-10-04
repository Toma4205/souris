<?php
/* On enregistre notre autoload.
function chargerClasse($classname)
{
  require $classname.'.php';
}

spl_autoload_register('chargerClasse');*/

require 'modele/coach/coach.php';
require 'modele/coach/coachManager.php';
require 'modele/ami/amiManager.class.php';

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_SESSION['coach']))
{
  if (isset($_POST['retour']))
  {
    header('Location: compteCoach.php');
  }
  $coach = $_SESSION['coach'];
}
else {
  header('Location: accueil.php');
  exit();
}

$bdd = new PDO('mysql:host=localhost;dbname=souris;charset=utf8', 'souris', 'souris',
  array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$coachManager = new CoachManager($bdd);
$amiManager = new AmiManager($bdd);

if (isset($_POST['rechercher']))
{
  if (isset($_POST['nomCoach']) && !empty($_POST['nomCoach']))
  {
    if (!isset($_SESSION['rechAmi']) || $_SESSION['rechAmi'] != $_POST['nomCoach'])
    {
      $coachs = $coachManager->findByNom($_POST['nomCoach'], $coach->id());
      $_SESSION['rechAmi'] = $_POST['nomCoach'];
      $_SESSION['listeRechAmi'] = $coachs;
    }
    else
    {
      $coachs = $_SESSION['listeRechAmi'];
    }
  }
  else
  {
    $message = 'La recherche ne doit pas être vide.';
  }
}
elseif (isset($_POST['ajouter']))
{
  $coachs = $_SESSION['listeRechAmi'];

  foreach($_POST['ajouter'] as $cle => $value)
  {
    $amiManager->creerAmi($coach, $cle);
  }

  $amis = $manager->findCoachAmiById($coach->id());
  $_SESSION['listeAmis'] = $amis;
  // TODO MPL supprimer coach ajouté de la liste
}
?>

<?php
    // entete
    include("vue/commun/entete.php");
?>

    <form action="" method="post">

    <fieldset>
      <legend>Liste amis</legend>
      <?php
        if (sizeof($_SESSION['listeAmis']) > 0)
        {
      ?>
        <table class="tableBase">
          <thead>
            <tr>
              <th>Id</th>
              <th>Nom</th>
              <th>Code postal</th>
            </tr>
          </thead>
          <tbody>
      <?php
        foreach($_SESSION['listeAmis'] as $value)
        {
            echo '<tr><td>' . $value->id() . '</td>';
            echo '<td>' . $value->nom() . '</td>';
            echo '<td>' . $value->codePostal() . '</td></tr>';
        }
        echo '</tbody></table>';
      }
      else
      {
        echo '<br/>';
        echo 'Aucun coach ami.';
      }
      ?>
    </fieldset>
    <fieldset>
        <legend>Ajouter un ami</legend>
          <p>Nom : <input type="text" size="40" name="nomCoach" value="<?php
              if(isset($_POST['nomCoach']))
              {
                echo htmlspecialchars($_POST['nomCoach']);
              }
            ?>" />
            <input type="submit" value="Rechercher" name="rechercher" />
          </p>
          <?php
              if(isset($_POST['nomCoach']))
              {
                if (isset($coachs) &&sizeof($coachs) > 0)
                {
          ?>
                  <table class="tableBase">
                    <thead>
                      <tr>
                        <th>Id</th>
                        <th>Nom</th>
                        <th>Code postal</th>
                        <th>Ajouter</th>
                      </tr>
                    </thead>
                    <tbody>
          <?php
                  foreach($coachs as $value)
                  {
                      echo '<tr><td>' . $value->id() . '</td>';
                      echo '<td>' . $value->nom() . '</td>';
                      echo '<td>' . $value->codePostal() . '</td>';
                      echo '<td><input type="submit" value="Ajouter" name="ajouter[' . $value->id() . ']" /></td></tr>';
                  }
                  echo '</tbody></table>';
                }
                else
                {
                  echo '<br/>';
                  echo 'Aucun coach contenant votre recherche.';
                }
              }
           ?>
         </fieldset>
      <fieldset>
        <input type="submit" value="Retour" name="retour" />
      </fieldset>
    </form>

<?php

// Le pied de page
include("vue/commun/pied_de_page.php");

?>
