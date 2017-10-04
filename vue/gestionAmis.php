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
