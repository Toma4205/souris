<div class="conteneurRow detail_compo">
  <div class="conteneurColumn margin_top_0">
    <div class="detail_compo_titre">Choix tactique</div>
    <div class="message_ingo">(Si 4 DEF, +0.5 pour chaque /<br/> Si 5 DEF, +1 pour chaque)</div>
    <?php
      if (isset($nomenclTactique))
      {
        echo '<select name="choixTactique" class="selectChoixTactique" onchange="javascript:submitForm(\'changerTactique\');">';

        if ($ligue->modeExpert() == TRUE)
        {
          foreach ($nomenclTactique as $cle => $value)
          {
            if($compoEquipe->codeTactique() == $value->code())
            {
                echo '<option value="' . $value->code() . '" selected="selected">' . $value->code() . '</option>';
            }
            else
            {
                echo '<option value="' . $value->code() . '">' . $value->code() . '</option>';
            }
          }
        }
        else
        {
          $tactiqueSelect = '';
          $optionSelect = '';
          foreach ($nomenclTactique as $cle => $value)
          {
            if($compoEquipe->codeTactique() == $value->code())
            {
              $tactiqueSelect = $value->nbDef() . '-' . $value->nbMil() . '-' . $value->nbAtt();
              $optionSelect = '<option value="' . $value->code() . '" selected="selected">' . $tactiqueSelect . '</option>';
            }
          }

          $tactiquePrecedente = '';
          foreach ($nomenclTactique as $cle => $value)
          {
            $tactique = $value->nbDef() . '-' . $value->nbMil() . '-' . $value->nbAtt();
            if ($tactique != $tactiquePrecedente)
            {
              $tactiquePrecedente = $tactique;
              if ($tactique != $tactiqueSelect)
              {
                echo '<option value="' . $tactique . '">' . $tactique . '</option>';
              }
              else
              {
                echo $optionSelect;
              }
            }
          }
        }

        echo '</select>';
      }
      else
      {
        echo '<p>Aucune nomenclature ! Veuillez contacter l\'assistance.';
      }
      ?>
  </div>
  <div class="conteneurColumn margin_top_0">
    <div class="detail_compo_titre">Capitaine</div>
    <div class="message_ingo">(+0.5 si équipe réelle gagne /<br/> -1 si équipe réelle perd)</div>
    <select name="choixCapitaine" class="selectChoixCapitaine">
        <option value="-1">...</option>
        <?php
          for ($i = 1; $i <= 11; $i++)
          {
            if ($capitaine == $i) {
              echo '<option value="' . $i . '" selected="selected">N° ' . $i . '</option>';
            } else {
              echo '<option value="' . $i . '">N° ' . $i . '</option>';
            }
          }
         ?>
    </select>
  </div>
  <div class="conteneurColumn margin_top_0">
    <div class="detail_compo_titre">Bonus/Malus<span id="imgSuppBonus" class="detail_compo_bonus_malus_supp cache"
      onclick="javascript:suppSelectBonusMalus();" title="Supprimer bonus sélectionné">
      <img src="web/img/croix.jpg" alt="X" width="10px" height="10px"/></span>
    </div>
    <input type="hidden" id="choixBonus" name="choixBonus" value="<?php echo $compoEquipe->codeBonusMalus(); ?>" />
    <?php
      if (isset($bonusMalus) && sizeof($bonusMalus) > 0)
      {
        $tabBonus = [];
        foreach ($bonusMalus as $value) {
          if (!array_key_exists($value->code(), $tabBonus)) {
            $tabBonus[$value->code()] = 0;
          }
          $tabBonus[$value->code()] = $tabBonus[$value->code()] + 1;
        }

        echo '<div class="conteneurColumn detail_compo_bonus_malus_div">';

        $tabAff = [];
        $index = 0;
        foreach ($bonusMalus as $value) {

          $codeBonus = $value->code();

          if (!array_key_exists($codeBonus, $tabAff)) {
            $libBonus = $value->libelleCourt();
            $libLongBonus = $value->libelle();

            if ($index == 0) {
              echo '<div class="conteneurRow detail_compo_bonus_malus_row">';
            } else if ($index % 4 == 0) {
              echo '</div>';
              echo '<div class="conteneurRow detail_compo_bonus_malus_row">';
            }
            echo '<div id="' . $codeBonus . '" class="detail_compo_bonus_malus_bloc"
              onclick="javascript:selectBonusMalus(\'' . $codeBonus . '\')">';
            echo '<div class="detail_compo_bonus_malus_lib">' . $libBonus . ' (' . $tabBonus[$codeBonus] . ')' . '</div>';
            if ($codeBonus == ConstantesAppli::BONUS_MALUS_FUMIGENE) {
              echo '<img src="web/img/bonusmalus/PNG_fumigenes.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_DIN_ARB) {
              echo '<img src="web/img/bonusmalus/PNG_diner.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_FAM_STA) {
              echo '<img src="web/img/bonusmalus/PNG_family.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BUS) {
              echo '<img src="web/img/bonusmalus/PNG_bus.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_MAU_CRA) {
              echo '<img src="web/img/bonusmalus/PNG_mauvaisCrampon.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BOUCHER) {
              echo '<img src="web/img/bonusmalus/PNG_butcher.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CHA_GB) {
              echo '<img src="web/img/bonusmalus/PNG_changementGardien.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CON_ZZ) {
              echo '<img src="web/img/bonusmalus/PNG_zizou.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
            }
            echo '</div>';
            $index++;

            $tabAff[$codeBonus] = TRUE;
          }
        }
        echo '</div>'; // Fermeture div dernière row

        echo '</div>';
      } else {
        echo '<div class="margin_top_1rem font_size_point_8rem">Plus de bonus/malus disponible.</div>';
      }
     ?>
    <select name="choixJoueurBonus" class="selectChoixJoueurBonus cache">
        <option value="-1">...</option>
        <?php
          for ($i = 1; $i <= 11; $i++)
          {
            if ($joueurBonus == $i) {
              echo '<option value="' . $i . '" selected="selected">N° ' . $i . '</option>';
            } else {
              echo '<option value="' . $i . '">N° ' . $i . '</option>';
            }
          }
         ?>
    </select>
    <select name="choixJoueurAdvBonus" class="selectChoixJoueur cache">
        <option value="-1">...</option>
        <?php
          foreach ($joueursAdvBonus as $joueur)
          {
            if ($joueurAdvBonus == $joueur->id()) {
              echo '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . '</option>';
            } else {
              echo '<option value="' . $joueur->id() . '">' . $joueur->nom() . '</option>';
            }
          }
         ?>
    </select>
    <select name="choixMiTempsBonus" class="selectChoixMiTempsBonus cache">
        <option value="-1">...</option>
        <?php
          for ($i = 1; $i <= 2; $i++)
          {
            if ($miTempsBonus == $i) {
              echo '<option value="' . $i . '" selected="selected">Mi-Temps ' . $i . '</option>';
            } else {
              echo '<option value="' . $i . '">Mi-Temps ' . $i . '</option>';
            }
          }
         ?>
    </select>
  </div>
</div>
