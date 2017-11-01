<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<fieldset>
    <legend>Paramètres de ligue</legend>
    <p>Nom <br/>
      <input type="text" class="width_200px" name="nom" size="40" value=<?php
        echo '"';
        if(isset($creaLigue))
        {
          echo htmlspecialchars($creaLigue->nom());
        }
        echo '"', (isset($creaLigue) && null != $creaLigue->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Nombre d'équipes <br/>
      <select name="nbEquipe" <?php
            if (isset($creaLigue) && null != $creaLigue->id())
            {
              echo ' disabled>';
            }
            else
            {
              echo '>';
            }
            $arrayNbEquipe = [2,3,4,5,6,7,8,9,10];
            foreach ($arrayNbEquipe as $value)
            {
              if((isset($creaLigue) && $creaLigue->nbEquipe() == $value)
                || (!isset($creaLigue) && $value == 2))
              {
                  echo '<option value="' . $value . '" selected="selected">' . $value . '</option>';
              }
              else
              {
                  echo '<option value="' . $value . '">' . $value . '</option>';
              }
            }
         ?>
      </select>
    </p>
    <p>Mode expert <span class="italic">(cf réglement)</span> <br/>
      <input type="checkbox" name="modeExpert" <?php
          if (isset($creaLigue) && $creaLigue->modeExpert() == 1)
          {
            echo 'checked';
          }
          echo ' ', (isset($creaLigue) && null != $creaLigue->id() ? ' disabled' : ' enabled');?>/>
    </p>
    <p>Un petit pari pour mettre du piquant ?<br/>
      <textarea name="libellePari" rows="5" cols="30" <?php
          echo '"', (isset($creaLigue) && null != $creaLigue->id() ? ' disabled' : ' enabled');?>><?php
           if(isset($creaLigue))
           {
             echo htmlspecialchars($creaLigue->libellePari());
           }?></textarea>
    </p>
    <?php
      // Création ligue non validée
      if (!isset($creaLigue) || null == $creaLigue->id())
      {
        echo '<input type="submit" value="Créer" name="creationLigue" />';
      }
    ?>
</fieldset>
<?php
  // Création ligue validée => envoi demande aux confrères
  if (isset($creaLigue) && null != $creaLigue->id() && $creaLigue->etat() == EtatLigue::CREATION)
  {
?>
<div class="colonnes">
  <div class="colonne">
    <fieldset>
    <legend>Inviter des confrères</legend>
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
          <th>Inviter</th>
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
          if (sizeof($coachsInvites) > 0)
          {
            $dejaInvite = FALSE;
            foreach($coachsInvites as $value2)
            {
              if ($value->coachConfrere()->id() == $value2->id())
              {
                $dejaInvite = TRUE;
              }
            }
            if ($dejaInvite == TRUE)
            {
              echo '<td>Invitation envoyée</td></tr>';
            }
            else
            {
              echo '<td><input type="submit" value="Inviter" name="inviter[' . $value->coachConfrere()->id() . ']" /></td></tr>';
            }
          }
          else
          {
            echo '<td><input type="submit" value="Inviter" name="inviter[' . $value->coachConfrere()->id() . ']" /></td></tr>';
          }
        }
        echo '</tbody></table>';
      }
      else
      {
        echo '<br/>';
        echo 'Tu dois dans un premier temps ajouter des confrères (onglet Mes confrères) avant de pouvoir les inviter ! C\'est logique.';
      }
        ?>
  </fieldset>
</div>
<div class="colonne">
  <fieldset>
    <legend>Confrères invités</legend>
    <?php
    if (sizeof($coachsInvites) > 0)
    {
    ?>
    <table class="tableBase">
      <thead>
        <tr>
          <th>Id</th>
          <th>Nom</th>
          <th>Code postal</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $nbOK = 0;
        $index=1;
        foreach($coachsInvites as $value)
        {
          echo '<tr><td>' . $value->id() . '</td>';
          echo '<td>' . $value->nom() . '</td>';
          echo '<td>' . $value->codePostal() . '</td>';
          if (null != $value->dateValidationLigue())
          {
            $nbOK++;
            echo '<td><input type="checkbox" name="coachInvite[' . $index .']" value="' . $value->id() . '" /></td></tr>';
          }
          else
          {
            echo '<td>En attente...</td></tr>';
          }

          $index++;
        }
        echo '</tbody></table>';

        // On enlève 1 car le coach créateur est forcément un participant
        $nbCoachManquant = $creaLigue->nbEquipe() - 1 - $nbOK;
        if ($nbCoachManquant < 1)
        {
          echo '<br/>';
          echo '<input type="submit" value="Valider les participants" name="validationFinale" />';
        }
        elseif ($nbCoachManquant == 1)
        {
          echo '<p class="italic">' . $nbCoachManquant . ' coach doit encore accepter l\'invitation.</p>';
        }
        else
        {
          echo '<p class="italic">' . $nbCoachManquant . ' coachs doivent encore accepter l\'invitation.</p>';
        }
      }
      else
      {
        echo '<br/>';
        echo 'Aucun coach invité pour le moment ! Ca va être compliqué de jouer.';
      }
        ?>
  </fieldset>
</div>
<?php
  }
  // Particpants validés => création équipe + mercato
  elseif (isset($creaLigue) && null != $creaLigue->id() && $creaLigue->etat() == EtatLigue::MERCATO)
  {
?>
<fieldset>
    <legend>Votre équipe</legend>
    <p>Nom <br/>
      <input type="text" class="width_200px" name="nomEquipe" size="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->nom());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Ville <br/>
      <input type="text" class="width_200px" name="villeEquipe" size="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->ville());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Stade <br/>
      <input type="text" class="width_200px" name="stadeEquipe" size="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->stade());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <?php
      // Création équipe non validée
      if (!isset($equipe) || null == $equipe->id())
      {
        echo '<input type="submit" value="Créer" name="creationEquipe" />';
      }
    ?>
</fieldset>
<?php
  }
?>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
