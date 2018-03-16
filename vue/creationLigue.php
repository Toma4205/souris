<?php
// entete
$vueJs = 'creationLigue.js';
$vueCss = 'creationLigue.css';

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
      <input type="text" class="width_200px" name="nom" maxlength="40" value=<?php
        echo '"';
        if(isset($creaLigue))
        {
          echo htmlspecialchars($creaLigue->nom());
        }
        echo '"', (isset($creaLigue) && null != $creaLigue->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Pack Bonus/Malus <br/>
      <select name="bonusMalus" onchange="javascript:onSelectionBonusMalus()" <?php
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
            $arrayBonusMalus[ConstantesAppli::BONUS_MALUS_PERSO] = 'Personnalisé';
            //$arrayBonusMalus[ConstantesAppli::BONUS_MALUS_FOLIE] = 'Folie';
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
    <div id="libBonusMalusPerso" class="italic font_size_point8rem cache">
      <p>La sélection se fera à la validation des participants.</p></div>
    <p>Mode expert <br/>
      <input type="checkbox" name="modeExpert" disabled/>
    <!--  <input type="checkbox" name="modeExpert" -->
      <?php if (isset($creaLigue) && $creaLigue->modeExpert() == 1){echo 'checked';}
          //echo ' ', (isset($creaLigue) && null != $creaLigue->id() ? ' disabled' : ' enabled');
      ?>
      <!--/>-->
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
            //$arrayModeMercato[ConstantesAppli::MERCATO_DRAFT] = 'Draft';
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
        echo '<p class="italic font_size_point8rem">Le détail des paramètres est expliqué dans le réglement.</p>';
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
    <table id="table_validation_coach" class="tableBase">
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
            echo '<td><input type="checkbox" name="coachInvite[' . $index .']" value="' . $value->id() . '"
              onclick="javascript:compterBonusASelect();" /></td></tr>';
          }
          else
          {
            echo '<td>En attente...</td></tr>';
          }

          $index++;
        }
        echo '</tbody></table>';
        echo '<br/>';
        if ($creaLigue->bonusMalus() != ConstantesAppli::BONUS_MALUS_PERSO) {
          echo '<div id="messageErreurValCoach" class="erreur cache"></div>';
          echo '<input id="boutonValCoach" type="submit" value="Valider les participants"
            name="validationFinale" onclick="return confirmerValCoach();" />';
        } else {
        ?>
</section>
<section id="sectionSelectionBonusMalus">
  <header>Sélection des bonus/malus</header>
  <div>
    <div class="selection_bonus_malus">Bonus/malus à sélectionner : <span id="nbBonusMalusASelect">0</span></div>
    <div class="conteneurRow selection_bonus_malus_type">
      <table id="table_selection_bonus_malus" class="tableBase">
        <thead>
          <tr>
            <th></th>
            <th>Nom</th>
            <th></th>
            <th>Nombre</th>
          </tr>
        </thead>
        <tbody>
          <?php
            if (isset($nomenclBonusMalus) && sizeof($nomenclBonusMalus) > 0) {

              function afficherSelectNbBonus($name)
              {
                echo '<td>';
                echo '<select name="nb_bonus_' . $name . '">';
                echo '<option value="0" selected="selected">0</option>';
                echo '<option value="1">1</option>';
                echo '<option value="2">2</option>';
                echo '<option value="3">3</option>';
                echo '<option value="4">4</option>';
                echo '<option value="5">5</option>';
                echo '</select>';
                echo '</td>';
              }

              foreach ($nomenclBonusMalus as $bonus) {
                $codeBonus = $bonus->code();
                $bonusTrouve = '';
                if ($codeBonus == ConstantesAppli::BONUS_MALUS_FUMIGENE) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_fumigenes.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_DIN_ARB) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_diner.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_FAM_STA) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_family.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BUS) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_bus.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_MAU_CRA) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_mauvaisCrampon.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BOUCHER) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_butcher.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CHA_GB) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_changementGardien.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_PAR_TRU) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_pari.png" alt="bonus" width="40px" height="40px"/>';
                } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CON_ZZ) {
                  $bonusTrouve = '<img src="web/img/bonusmalus/PNG_zizou.png" alt="bonus" width="40px" height="40px"/>';
                }

                if ($bonusTrouve != '') {
                  echo '<tr>';
                  echo '<td>' . $bonusTrouve . '</td>';
                  echo '<td>' . $bonus->libelleCourt() . '</td>';
                  echo '<td>' . $bonus->libelle() . '</td>';
                  afficherSelectNbBonus($codeBonus);
                  echo '</tr>';
                }
              }
            } else {
              echo 'Impossible d\'afficher les bonus/malus. Merci de nous contacter avec le nom de votre ligue.';
            }
          ?>
        </tbody>
      </table>
      </div>
    <br/>
    <div id="messageErreurValCoachEtBonus" class="erreur cache"></div>
    <input id="boutonValCoachEtBonus" type="submit" value="Valider les participants et bonus"
      name="validationFinaleAvecBonus" onclick="return controlerBonus();" />
  </div>
</section>
<?php
        } // Fin du IF ConstantesAppli::BONUS_MALUS_PERSO

        echo '<div class="validation_coach_lib">Attention : cette action lance la ligue pour de bon (et le mercato).</div>';

      } // Fin du IF sizeof($coachsInvites) > 0
    }
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
