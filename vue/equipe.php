<?php
// entete
$vueJs = 'equipe.js';
$vueCss = 'equipe.css';
require_once("vue/commun/enteteflex.php");

function afficherContenuSelect($libSelect, $nameSelect, $joueurs, $tabCompo)
{
  $contenu = '<p><span class="spanChoixJoueur">' . $libSelect;
  $contenu .= '</span><select name="' . $nameSelect . '" class="selectChoixJoueur" onchange="javascript:onSelectionTitulaire(\''. $nameSelect . '\');">';
  if (isset($tabCompo[$nameSelect]) && $tabCompo[$nameSelect] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  $contenu .= ajouterOptionJoueur($nameSelect, $joueurs, $tabCompo);
  $contenu .= '</select></p>';

  echo $contenu;
}

function afficherContenuSelectRempl($numPosition, $gb, $def, $mil, $att, $tabCompo)
{
  $contenu = '<p><span class="spanChoixRempl">' . $numPosition . '. REMPL';
  $contenu .= '</span><select name="' . $numPosition . '" class="selectChoixJoueurREMPL" onchange="javascript:onSelectionRempl(\''. $numPosition . '\');">';
  if (isset($tabCompo[$numPosition]) && $tabCompo[$numPosition] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  $contenu .= ajouterOptionRempl($numPosition, 'GB ', $gb, $tabCompo);
  $contenu .= ajouterOptionRempl($numPosition, 'DEF', $def, $tabCompo);
  $contenu .= ajouterOptionRempl($numPosition, 'MIL', $mil, $tabCompo);
  $contenu .= ajouterOptionRempl($numPosition, 'ATT', $att, $tabCompo);
  $contenu .= '</select></p>';

  echo $contenu;
}

function afficherContenuSelectRentrant($numRempl, $def, $mil, $att, $tabRempl, $tabRentrant)
{
  $contenu = '<select name="rentrant_' . $numRempl . '" class="selectChoixJoueur" onchange="javascript:onSelectionRentrant(\''. $numRempl . '\');">';
  if (isset($tabRentrant[$numRempl]) && $tabRentrant[$numRempl] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  $contenu .= ajouterOptionRentrant($numRempl, $def, $tabRempl, $tabRentrant);
  $contenu .= ajouterOptionRentrant($numRempl, $mil, $tabRempl, $tabRentrant);
  $contenu .= ajouterOptionRentrant($numRempl, $att, $tabRempl, $tabRentrant);
  $contenu .= '</select>';

  echo $contenu;
}

function afficherContenuSelectSortant($numRempl, $def, $mil, $att, $tabTitu, $tabRrentrant, $tabSortant)
{
  $contenu = '<select name="sortant_' . $numRempl . '" class="selectChoixJoueur" onchange="javascript:onSelectionSortant(\''. $numRempl . '\');">';
  if (isset($tabSortant[$numRempl]) && $tabSortant[$numRempl] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  $contenu .= ajouterOptionSortant($numRempl, $def, $tabTitu, $tabRrentrant, $tabSortant);
  $contenu .= ajouterOptionSortant($numRempl, $mil, $tabTitu, $tabRrentrant, $tabSortant);
  $contenu .= ajouterOptionSortant($numRempl, $att, $tabTitu, $tabRrentrant, $tabSortant);
  $contenu .= '</select>';

  echo $contenu;
}

function ajouterOptionJoueur($numPosition, $joueurs, $tabCompo)
{
  $contenu = '';
  foreach ($joueurs as $joueur)
  {
    $cleTabCompo = array_search($joueur->id(), $tabCompo);
    if (isset($tabCompo[$numPosition]) && $tabCompo[$numPosition] == $joueur->id()) {
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . ' ' . $joueur->prenom() . ' - ' . $joueur->libelleEquipe() . '</option>';
    } elseif ($cleTabCompo > 0 && $cleTabCompo <= 11) {
      $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . ' - ' . $joueur->libelleEquipe() . '</option>';
    } else {
      $contenu .= '<option value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . ' - ' . $joueur->libelleEquipe() . '</option>';
    }
  }

  return $contenu;
}

function ajouterOptionRempl($numPosition, $preLib, $joueurs, $tabCompo)
{
  $contenu = '';
  foreach ($joueurs as $joueur)
  {
    if (isset($tabCompo[$numPosition]) && $tabCompo[$numPosition] == $joueur->id()) {
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $preLib . ') ' . $joueur->nom() . ' ' . $joueur->prenom() . ' - ' . $joueur->libelleEquipe() . '</option>';
    } elseif (in_array($joueur->id(), $tabCompo)) {
      $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $preLib . ') ' . $joueur->nom() . ' ' . $joueur->prenom() . ' - ' . $joueur->libelleEquipe() . '</option>';
    } else {
      $contenu .= '<option value="' . $joueur->id() . '">' . $preLib . ') ' . $joueur->nom() . ' ' . $joueur->prenom() . ' - ' . $joueur->libelleEquipe() . '</option>';
    }
  }

  return $contenu;
}

function ajouterOptionRentrant($numPosition, $joueurs, $tabRempl, $tabRentrant)
{
  $contenu = '';
  foreach ($joueurs as $joueur)
  {
    $cleRempl = array_search($joueur->id(), $tabRempl);
    $cleRentrant = array_search($joueur->id(), $tabRentrant);
    if (isset($tabRentrant[$numPosition]) && $tabRentrant[$numPosition] == $joueur->id()) {
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    } elseif ($cleRempl > 0 && $cleRentrant == 0) {
      $contenu .= '<option value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    } else {
      $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    }
  }

  return $contenu;
}

function ajouterOptionSortant($numPosition, $joueurs, $tabTitu, $tabRrentrant, $tabSortant)
{
  $contenu = '';

  // Détermine si le rentrant est dans le groupe de joueurs
  $avecRentrant = 0;
  if (isset($tabRrentrant[$numPosition]) && $tabRrentrant[$numPosition] != -1)
  {
    foreach ($joueurs as $joueur)
    {
      if ($tabRrentrant[$numPosition] == $joueur->id()) {
        $avecRentrant = 1;
        break;
      }
    }
  }

  foreach ($joueurs as $joueur)
  {
    // Détermine si joueur dans les titulaires
    $cleTitu = array_search($joueur->id(), $tabTitu);
    // Détermine si joueur dans les sortants
    $cleSortant = array_search($joueur->id(), $tabSortant);

    if (isset($tabSortant[$numPosition]) && $tabSortant[$numPosition] == $joueur->id()) {
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    } elseif ($avecRentrant > 0 && $cleTitu > 0 && $cleSortant == 0) {
      $contenu .= '<option value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    } else {
      $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    }
  }

  return $contenu;
}

if (isset($calReel) && $calLigue->id() != null)
{
?>
<section>
  <input type="hidden" name="numJourneeCalReel" value="<?php echo $calReel->numJournee(); ?>"/>
  <div class="detail_match_bandeau conteneurRow">
    <div class="detail_match_bandeau_journee_prec"
      title="<?php if ($avecJourneePrec){echo 'Afficher ma compo de la journée précédente';} ?>"
      <?php if ($avecJourneePrec){echo 'onclick="javascript:submitForm(\'journeePrec\');"';} ?>>
      <?php if ($avecJourneePrec){echo '<';} ?>
    </div>
    <div class="detail_match_bandeau_col_g">
      <div class="detail_match_bandeau_equipe">
        <div><?php echo $calLigue->nomEquipeDom(); ?></div>
        <div class="detail_match_bandeau_equipe_coach">Coach : <?php echo $equipeDom->nomCoach(); ?></div>
        <div class="detail_match_bandeau_equipe_logo">
          <img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeDom->codeStyleCoach()]; ?>" alt="Logo équipe dom." width="80px" height="80px"/>
        </div>
      </div>
    </div>
    <div class="detail_match_bandeau_col_c">
      <div class="detail_match_bandeau_journee">Journée <?php echo $calLigue->numJournee(); ?></div>
      <div class="detail_match_bandeau_ville">Lieu : <?php echo $equipeDom->ville(); ?></div>
      <div class="detail_match_bandeau_stade">Stade "<?php echo $equipeDom->stade(); ?>"</div>
      <div class="detail_match_bandeau_date">
        <?php
          $dateDebut = date_create($calReel->dateHeureDebut());
          echo 'Début de la journée ' . $calReel->numJournee() . ' de L1' .
            '<br/><span class="heureProchaineJournee">' . date_format($dateDebut, 'd/m/Y H:i:s') . '</span>';
        ?>
      </div>
    </div>
    <div class="detail_match_bandeau_col_d">
      <div class="detail_match_bandeau_equipe">
        <div><?php echo $calLigue->nomEquipeExt(); ?></div>
        <div class="detail_match_bandeau_equipe_coach">Coach : <?php echo $equipeExt->nomCoach(); ?></div>
        <div class="detail_match_bandeau_equipe_logo">
          <img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeExt->codeStyleCoach()]; ?>" alt="Logo équipe ext." width="80px" height="80px"/>
        </div>
      </div>
    </div>
    <div class="detail_match_bandeau_journee_suiv"
      title="<?php if ($avecJourneeSuiv){echo 'Afficher ma compo de la journée suivante';} ?>"
      <?php if ($avecJourneeSuiv){echo 'onclick="javascript:submitForm(\'journeeSuiv\');"';} ?>>
      <?php if ($avecJourneeSuiv){echo '>';} ?>
    </div>
  </div>
  <div class="conteneurRow">
    <div class="conteneurColumn">
      <p>Choix tactique</p>
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
    <div class="conteneurColumn">
      <p>Capitaine</p>
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
    <div class="conteneurColumn">
      <p>Bonus/Malus</p>
      <select name="choixBonus" class="selectChoixBonus" onchange="javascript:onSelectionBonusMalus('choixBonus')">
        <option value="-1">...</option>
        <?php
          if (isset($bonusMalus))
          {
            foreach($bonusMalus as $value)
            {
              if ($compoEquipe->codeBonusMalus() == $value->code()) {
                echo '<option value="' . $value->code() . '" selected="selected">' . $value->libelle() . '</option>';
              }
              else {
                echo '<option value="' . $value->code() . '">' . $value->libelle() . '</option>';
              }
            }
          }
         ?>
      </select>
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
                echo '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
              } else {
                echo '<option value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
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
  <div id="rowCompoEquipe" class="conteneurRow">
    <div id="contenuCompoEquipe" class="conteneurColumnGauche">
      <div id="divTitulaire">
        <p id="titulaire">Titulaires</p>
      <?php
        if ($ligue->modeExpert() != TRUE)
        {
          if (isset($gb))
          {
            echo '<div>';
            afficherContenuSelect('1. GB ', 1, $gb, $tabCompo);
            echo '</div>';
          }

          $numPosition = 2;
          if (isset($def))
          {
            echo '<div id="divTitulaireDEF">';
            for ($index = 1; $index <= $choixTactique->nbDef(); $index++)
            {
              afficherContenuSelect($numPosition . '. DEF ', $numPosition, $def, $tabCompo);
              $numPosition++;
            }
            echo '</div>';
          }
          if (isset($mil))
          {
            echo '<div id="divTitulaireMIL">';
            for ($index = 1; $index <= $choixTactique->nbMil(); $index++)
            {
              afficherContenuSelect($numPosition . '. MIL ', $numPosition, $mil, $tabCompo);
              $numPosition++;
            }
            echo '</div>';
          }
          if (isset($att))
          {
            echo '<div id="divTitulaireATT">';
            for ($index = 1; $index <= $choixTactique->nbAtt(); $index++)
            {
              afficherContenuSelect($numPosition . '. ATT ', $numPosition, $att, $tabCompo);
              $numPosition++;
            }
            echo '</div>';
          }
        }
        else
        {
          echo '<div>Mode expert à venir...</div>';
        }
      ?>
      </div>
    </div>
    <div id="divRemplacant">
      <p id="remplacant">Remplaçants</p>
      <?php
        $numPosition = 12;
        if (isset($gb))
        {
          echo '<div>';
          for ($numPosition = 12; $numPosition <= 18; $numPosition++)
          {
            afficherContenuSelectRempl($numPosition, $gb, $def, $mil, $att, $tabCompo);
          }
          echo '</div>';
        }
      ?>
    </div>
  </div>
  <section id="divRemplacement" class="conteneurColumn">
    <p id="remplacement">Remplacements <span class="italic normal">(si note strictement inférieure)</span></p>
    <?php
      $tabTitu = [];
      $tabRempl = [];
      $tabGB = [];
      foreach ($gb as $joueur)
      {
        $tabGB[] = $joueur->id();
      }

      foreach ($tabCompo as $numero => $joueur)
      {
        if ($numero > 1) {
          $cleGB = array_search($joueur, $tabGB);
          if ($numero <= 11) {
            $tabTitu[$numero] = $joueur;
          } elseif ($cleGB == 0) {
            $tabRempl[$numero] = $joueur;
          }
        }
      }

      if (isset($gb))
      {
        for ($numRemplacement = 1; $numRemplacement <= 5; $numRemplacement++)
        {
          echo '<div class="conteneurRow padding_bottom_15px">';

          echo '<div>';
          afficherContenuSelectRentrant($numRemplacement, $def, $mil, $att, $tabRempl, $tabRentrant);
          echo '</div>';
          echo '<p class="signeRemplacement"> > </p>';
          echo '<div>';
          afficherContenuSelectSortant($numRemplacement, $def, $mil, $att, $tabTitu, $tabRentrant, $tabSortant);

          $value = '';
          if (isset($tabNote[$numRemplacement])) {
            $value = 'value="' . $tabNote[$numRemplacement] . '" ';
          }
          echo '<input class="width_25px margin_left_5px" type="text" name="note_' . $numRemplacement . '"
            onchange="javascript:verifierNote(' . $numRemplacement . ');" maxlength="3" ' . $value . '/>';
          echo '</div>';
          echo '</div>';
        }
      }
    ?>
  </section>
  <div>
    <input type="submit" value="Valider la compo" name="enregistrer"
      onclick="return controlerBonus();" class="marginBottom width_200px" />
  </div>
  <div id="messageErreurBonus" class="cache">Jean-Michel à moitié... Ta saisie du bonus/malus est incomplète !</div>
</section>
<?php
}
else
{
  echo '<p>Plus de match de championnat !</p>';
}
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
