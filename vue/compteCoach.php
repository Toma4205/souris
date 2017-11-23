<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<div class="colonnes">
  <div class="colonne" style="width:50%;">
    <div class="sousTitre"><h3>Mes actions en attente</h3></div>
        <p>A venir...</p>
    <div class="sousTitre"><h3>Actualités</h3></div>
        <p>A venir...</p>
  </div>
  <div class="colonne" style="width:50%;">
      <div class="sousTitre"><h3>Mes ligues</h3></div>
      <?php
      if (sizeof($ligues) > 0)
      {
      ?>
      <table class="tableBase">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Classement</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach($ligues as $value)
          {
            echo '<tr><td>' . $value->nom() . '</td>';
            if ($value->classement() != null)
            {
              echo '<td>' . $value->classement() . '</td>';
            }
            else
            {
              echo '<td>Aucun</td>';
            }

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
                  echo '<input type="submit" value="Ok" name="accepterInvitation[' . $value->id() . ']" /> ';
                  echo ' <input type="submit" value="No, thanks" name="refuserInvitation[' . $value->id() . ']" />';
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
  </div>
</div>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
