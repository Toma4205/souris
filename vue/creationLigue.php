<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
  <!-- ***************************************
  //   ***** DEBUT PARTIE CREATION LIGUE *****
  //   *************************************** -->
<fieldset>
    <legend>Paramètres de ligue</legend>
    <p>Nom *<br/>
      <input type="text" class="width_200px" name="nom" size="40" value=<?php
        echo '"';
        if(isset($creaLigue))
        {
          echo htmlspecialchars($creaLigue->nom());
        }
        echo '"', (isset($creaLigue) && null != $creaLigue->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Pack Bonus/Malus <br/>
      <select name="bonusMalus" <?php
            if (isset($creaLigue) && null != $creaLigue->id())
            {
              echo ' disabled>';
            }
            else
            {
              echo '>';
            }

            $arrayBonusMalus = [];
            $arrayBonusMalus[ConstantesAppli::BONUS_MALUS_AUCUN] = 'Aucun';
            $arrayBonusMalus[ConstantesAppli::BONUS_MALUS_CLASSIQUE] = 'Classique';
            $arrayBonusMalus[ConstantesAppli::BONUS_MALUS_FOLIE] = 'Folie';
            foreach ($arrayBonusMalus as $cle => $value)
            {
              if((isset($creaLigue) && $creaLigue->bonusMalus() == $cle)
                || (!isset($creaLigue) && $cle == ConstantesAppli::BONUS_MALUS_AUCUN))
              {
                  echo '<option value="' . $cle . '" selected="selected">' . $value . '</option>';
              }
              else
              {
                  echo '<option value="' . $cle . '">' . $value . '</option>';
              }
            }
         ?>
      </select>
    </p>
    <p>Mode expert <br/>
      <input type="checkbox" name="modeExpert" <?php
          if (isset($creaLigue) && $creaLigue->modeExpert() == 1)
          {
            echo 'checked';
          }
          echo ' ', (isset($creaLigue) && null != $creaLigue->id() ? ' disabled' : ' enabled');?>/>
    </p>
    <p>Mode mercato <br/>
      <select name="modeMercato" <?php
            if (isset($creaLigue) && null != $creaLigue->id())
            {
              echo ' disabled>';
            }
            else
            {
              echo '>';
            }

            $arrayModeMercato = [];
            $arrayModeMercato[ConstantesAppli::MERCATO_ENCHERE] = 'Enchères';
            $arrayModeMercato[ConstantesAppli::MERCATO_DRAFT] = 'Draft';
            foreach ($arrayModeMercato as $cle => $value)
            {
              if((isset($creaLigue) && $creaLigue->modeMercato() == $cle)
                || (!isset($creaLigue) && $cle == ConstantesAppli::MERCATO_ENCHERE))
              {
                  echo '<option value="' . $cle . '" selected="selected">' . $value . '</option>';
              }
              else
              {
                  echo '<option value="' . $cle . '">' . $value . '</option>';
              }
            }
         ?>
      </select>
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
        echo '<p class="italic">Le détail des paramètres est expliqué dans le réglement.</p>';
        echo '<input type="submit" value="Créer" name="creationLigue" />';
      }
    ?>
</fieldset>
<!-- *********************************************
//   ***** DEBUT PARTIE GESTION PARTICIPANTS *****
//   ********************************************* -->
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
        echo '<br/>';
        echo '<input type="submit" value="Valider les participants" name="validationFinale" />';
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
?>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
