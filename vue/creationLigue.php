<?php
// entete
require_once("vue/commun/enteteflex.php");
?>
  <!-- ***************************************
  //   ***** DEBUT PARTIE CREATION LIGUE *****
  //   *************************************** -->
<section class="conteneurRow<?php if (isset($creaLigue) && null != $creaLigue->id() && $creaLigue->etat() == EtatLigue::CREATION) { echo ' avecBordureInf';} ?>">
  <div class="formulaire">
    <header>Paramètres de ligue</header>
    <?php
      if (isset($messageLigue))
      {
        echo '<span class="erreur">' . $messageLigue . '</span>';
      }
     ?>
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
        echo '<input type="submit" value="Créer" name="creationLigue" class="marginBottom" />';
      }
    ?>
  </div>
</section>
<!-- *********************************************
//   ***** DEBUT PARTIE GESTION PARTICIPANTS *****
//   ********************************************* -->
<?php
  // Création ligue validée => envoi demande aux confrères
  if (isset($creaLigue) && null != $creaLigue->id() && $creaLigue->etat() == EtatLigue::CREATION)
  {
?>
<section<?php if (sizeof($coachsInvites) > 0) { echo ' class="avecBordureInf"';} ?>>
  <header>Inviter des confrères</header>
    <?php
    if (sizeof($confreres) > 0)
    {
      if (isset($messageInvit))
      {
        echo '<span class="erreur">' . $messageInvit . ' (TODO MPL Js)</span>';
      }
    ?>
    <table class="tableBase">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Inviter</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $index=1;
        foreach($confreres as $value)
        {
          echo '<tr><td>' . $value->coachConfrere()->nom() . '</td>';
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
              echo '<td><input type="checkbox" name="coachEnvoiInvit[' . $index .']" value="' . $value->coachConfrere()->id() . '" /></td></tr>';
            }
          }
          else
          {
            echo '<td><input type="checkbox" name="coachEnvoiInvit[' . $index .']" value="' . $value->coachConfrere()->id() . '" /></td></tr>';
          }
          $index++;
        }
        echo '</tbody></table>';
        echo '<br/>';
        echo '<input type="submit" value="Inviter des confrères" name="invitationConfrere" />';
      }
      else
      {
        echo '<br/>';
        echo 'Tu dois dans un premier temps ajouter des confrères (onglet Mes confrères) avant de pouvoir les inviter ! C\'est logique.';
      }

      if (sizeof($coachsInvites) > 0)
      {
        ?>
</section>
<section>
  <header>Confrères invités</header>
    <?php
      if (isset($messageValid))
      {
        echo '<span class="erreur">' . $messageValid . ' (TODO MPL Js)</span>';
      }
    ?>
    <table class="tableBase">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $nbOK = 0;
        $index=1;
        foreach($coachsInvites as $value)
        {
          echo '<tr><td>' . $value->nom() . '</td>';
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
        ?>
</section>
<?php
    }
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
