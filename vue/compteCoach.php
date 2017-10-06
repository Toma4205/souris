<?php
// entete
require_once("vue/commun/entete.php");
?>

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
              <th>Date Ajout</th>
              <th>Date Refus</th>
            </tr>
          </thead>
          <tbody>
      <?php
        foreach($amis as $value)
        {
            echo '<tr><td>' . $value->coachAmi()->id() . '</td>';
            echo '<td>' . $value->coachAmi()->nom() . '</td>';
            echo '<td>' . $value->coachAmi()->codePostal() . '</td>';
            echo '<td>' . $value->dateAjout() . '</td>';
            echo '<td>' . $value->dateRefus() . '</td></tr>';
        }
        echo '</tbody></table>';
      }
      else
      {
        echo '<br/>';
        echo 'Aucun coach ami.';
      }

      echo '<br/>';

      if ($nbDemandeAjout > 0)
      {
        echo '<p>Vous avez ' . $nbDemandeAjout . ' demande(s) d\'ajout. Consultez la rubrique \'Gérer mes amis\'.</p>';
      }
      else
      {
        echo '<p>Aucune demande d\'ajout. Soyez plus sympathique avec vos congénères.</p>';
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
require_once("vue/commun/pied_de_page.php");
?>
