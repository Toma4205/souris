<?php
// entete
require_once("vue/commun/entete.php");
?>

<form action="" method="post">
  <?php
  if (sizeof($demandesAjout) > 0)
  {
  ?>
  <fieldset>
    <legend>Liste des demandes d'ajout</legend>
      <table class="tableBase">
        <thead>
          <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Code postal</th>
            <th>Date demande</th>
            <th>Accepter</th>
            <th>Refuser</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach($demandesAjout as $demande)
          {
            echo '<tr><td>' . $demande->coachAmi()->id() . '</td>';
            echo '<td>' . $demande->coachAmi()->nom() . '</td>';
            echo '<td>' . $demande->coachAmi()->codePostal() . '</td>';
            echo '<td>' . $demande->dateDemande() . '</td>';
            echo '<td><input type="submit" value="Accepter" name="accepter[' . $demande->coachAmi()->id() . ']" /></td>';
            echo '<td><input type="submit" value="Refuser" name="refuser[' . $demande->coachAmi()->id() . ']" /></td></tr>';
          }
          echo '</tbody></table>';
          ?>
  </fieldset>
  <?php
  }
  if (sizeof($amisDemandesEnCours) > 0)
  {
  ?>
  <fieldset>
    <legend>Liste de mes demandes en cours</legend>
      <table class="tableBase">
        <thead>
          <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Code postal</th>
            <th>Date demande</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach($amisDemandesEnCours as $demande)
          {
            echo '<tr><td>' . $demande->coachAmi()->id() . '</td>';
            echo '<td>' . $demande->coachAmi()->nom() . '</td>';
            echo '<td>' . $demande->coachAmi()->codePostal() . '</td>';
            echo '<td>' . $demande->dateDemande() . '</td></tr>';
          }
          echo '</tbody></table>';
          ?>
  </fieldset>
  <?php
  }
  ?>
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
            <th>Depuis</th>
            <th>Supprimer</th>
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
            echo '<td><input type="submit" value="Supprimer" name="supprimer[' . $value->coachAmi()->id() . ']" /></td></tr>';
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
