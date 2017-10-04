<?php
/* On enregistre notre autoload.
function chargerClasse($classname)
{
  require $classname.'.php';
}

spl_autoload_register('chargerClasse');*/

require 'modele/coach/coach.php';
require 'modele/coach/coachManager.php';

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_SESSION['coach']))
{
  if (isset($_POST['creerLigue'])) {
    header('Location: creationLigue.php');
  }
  elseif (isset($_POST['gererAmis'])) {
    header('Location: gestionAmis.php');
  }
  $coach = $_SESSION['coach'];
}
else {
  header('Location: accueil.php');
  exit();
}

$bdd = new PDO('mysql:host=localhost;dbname=souris;charset=utf8', 'souris', 'souris',
  array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$manager = new CoachManager($bdd);

$amis = $manager->findCoachAmiById($coach->id());
$_SESSION['listeAmis'] = $amis;

// entete
include("vue/commun/entete.php");
?>
    <p>Nombre de coachs : <?= $manager->count() ?></p>

    <fieldset>
      <legend>Mes informations</legend>
      <p>
        Nom         : <?= htmlspecialchars($coach->nom()) ?><br />
        Mail        : <?= $coach->mail() ?><br />
        Code Postal : <?= $coach->codePostal() ?><br />
      </p>
    </fieldset>
    <fieldset>
      <legend>Liste amis</legend>
      <?php
        if (sizeof($amis) > 0)
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
        foreach($amis as $value)
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
        <legend>Gestion</legend>
        <form action="" method="post">
          <!--<input type="submit" value="Créer une ligue" name="creerLigue" />-->
          <input type="submit" value="Gérer mes amis" name="gererAmis" />
        </form>
    </fieldset>

<?php

// Le pied de page
include("vue/commun/pied_de_page.php");

?>
