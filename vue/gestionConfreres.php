<?php
// entete
require_once("vue/commun/enteteflex.php");
?>
<div class="conteneurColumn width_90pc">
  <section id="sectionRechConfrere" class="avecBordureInf">
    <header><h3>Ajouter un confrère</h3></header>
    <?php
      if (isset($message))
      {
        echo '<span class="erreur">' . $message . '</span>';
      }
     ?>
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
            <th>Nom</th>
            <th>Code postal</th>
            <th>Ajouter</th>
          </tr>
        </thead>
        <tbody>
        <?php
        foreach($coachsRech as $value)
        {
          echo '<tr><td>' . $value->nom() . '</td>';
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
  </section>
  <section id="sectionMesConfrere">
    <header><h3>Liste confrères</h3></header>
      <?php
      if (sizeof($confreres) > 0)
      {
      ?>
      <table class="tableBase">
        <thead>
          <tr>
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
            $dateDebut = date_create($value->dateDebut());
            echo '<tr><td>' . $value->coachConfrere()->nom() . '</td>';
            echo '<td>' . $value->coachConfrere()->codePostal() . '</td>';
            echo '<td>' . date_format($dateDebut, 'd/m/Y') . '</td>';
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
  </section>
</div>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
