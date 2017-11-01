<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<div class="colonnes">
  <div class="colonne">
    <fieldset>
      <legend>Mes actions en attente</legend>
        A venir...
    </fieldset>
    <fieldset>
      <legend>Actualités</legend>
      <p>
        A venir...
      </p>
    </fieldset>
  </div>
  <div class="colonne">
    <fieldset>
      <legend>Mes ligues</legend>
      <?php
      if (sizeof($ligues) > 0)
      {
      ?>
      <table class="tableBase">
        <thead>
          <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Equipe</th>
            <th>Classement</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach($ligues as $value)
          {
            echo '<tr><td>' . $value->id() . '</td>';
            echo '<td>' . $value->nom() . '</td>';
            echo '<td>A venir...</td>';
            echo '<td>A venir...</td>';
            if ($value->etat() == EtatLigue::CREATION)
            {
              if ($value->createur())
              {
                echo '<td><input type="submit" value="Poursuivre la création" name="continuerCreaLigue[' . $value->id() . ']" /></td>';
              }
              else
              {
                if (null != $value->dateValidation())
                {
                  echo '<td>En attente de validation du coach créateur...</td>';
                }
                else
                {
                  echo '<td>';
                  echo '<input type="submit" value="Accepter invitation" name="accepterInvitation[' . $value->id() . ']" /> ';
                  echo ' <input type="submit" value="Refuser invitation" name="refuserInvitation[' . $value->id() . ']" />';
                  echo '</td>';
                }
              }
            }
            elseif ($value->etat() == EtatLigue::MERCATO) {
              echo '<td><input type="submit" value="Construire mon équipe" name="continuerCreaLigue[' . $value->id() . ']" /></td>';
            }
            elseif ($value->etat() == EtatLigue::EN_COURS) {
              echo '<td><input type="submit" value="Rejoindre" name="rejoindre[' . $value->id() . ']" /></td>';
            }
            elseif ($value->etat() == EtatLigue::TERMINEE) {
              echo '<td>A venir... (T)</td>';
              //echo '<td><input type="submit" value="Masquer" name="masquer[' . $value->id() . ']" /></td>';
            }
            echo '</tr>';
          }
          echo '</tbody></table>';
        }
        else
        {
          echo '<br/>';
          echo 'Aucune ligue. Faut se mettre au boulot jeune padawan !';
        }
          ?>
    </fieldset>
  </div>
</div>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
