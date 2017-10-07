<?php
// entete
require_once("vue/commun/entete.php");
?>

<form action="" method="post">
  <fieldset>
    <legend>Liste confrères</legend>
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
            <th>Supprimer</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach($confreres as $value)
          {
            echo '<tr><td>' . $value->coachConfrere()->id() . '</td>';
            echo '<td>' . $value->coachConfrere()->nom() . '</td>';
            echo '<td>' . $value->coachConfrere()->codePostal() . '</td>';
            echo '<td>' . $value->dateDebut() . '</td>';
            echo '<td><input type="submit" value="Supprimer" name="supprimer[' . $value->coachConfrere()->id() . ']" /></td></tr>';
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
    <legend>Ajouter un confrère</legend>
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
        if (isset($coachsRech) &&sizeof($coachsRech) > 0)
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
        foreach($coachsRech as $value)
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
</form>

<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
