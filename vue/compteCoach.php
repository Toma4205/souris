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
      <legend>Liste confreres</legend>
      <?php
        if (sizeof($confreres) > 0)
        {
      ?>
        <table class="tableBase">
          <thead>
            <tr>
              <th>Id</th>
              <th>Nom</th>
              <th>Code postal</th>
              <th>Depuis</th>
            </tr>
          </thead>
          <tbody>
      <?php
        foreach($confreres as $value)
        {
            echo '<tr><td>' . $value->coachConfrere()->id() . '</td>';
            echo '<td>' . $value->coachConfrere()->nom() . '</td>';
            echo '<td>' . $value->coachConfrere()->codePostal() . '</td>';
            echo '<td>' . $value->dateDebut() . '</td></tr>';
        }
        echo '</tbody></table>';
      }
      else
      {
        echo '<br/>';
        echo 'Aucun confrere.';
      }
      ?>
    </fieldset>
    <fieldset>
        <legend>Gestion</legend>
        <form action="" method="post">
          <!--<input type="submit" value="Créer une ligue" name="creerLigue" />-->
          <input type="submit" value="Gérer mes confreres" name="gererConfreres" />
        </form>
    </fieldset>

<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
