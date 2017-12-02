<?php
// entete
$vueJs = 'equipe.js';
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

function afficherContenuSelectRentrant($numRempl, $gb, $def, $mil, $att, $tabRempl, $tabRentrant)
{
  $contenu = '<select name="rentrant_' . $numRempl . '" class="selectChoixJoueur" onchange="javascript:onSelectionRentrant(\''. $numRempl . '\');">';
  if (isset($tabRentrant[$numRempl]) && $tabRentrant[$numRempl] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  $contenu .= ajouterOptionRentrant($numRempl, $gb, $tabRempl, $tabRentrant);
  $contenu .= ajouterOptionRentrant($numRempl, $def, $tabRempl, $tabRentrant);
  $contenu .= ajouterOptionRentrant($numRempl, $mil, $tabRempl, $tabRentrant);
  $contenu .= ajouterOptionRentrant($numRempl, $att, $tabRempl, $tabRentrant);
  $contenu .= '</select>';

  echo $contenu;
}

function afficherContenuSelectSortant($numRempl, $gb, $def, $mil, $att, $tabTitu, $tabRrentrant, $tabSortant)
{
  $contenu = '<select name="sortant_' . $numRempl . '" class="selectChoixJoueur" onchange="javascript:onSelectionSortant(\''. $numRempl . '\');">';
  if (isset($tabSortant[$numRempl]) && $tabSortant[$numRempl] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  $contenu .= ajouterOptionSortant($numRempl, $gb, $tabTitu, $tabRrentrant, $tabSortant);
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

if (isset($calReel))
{
?>
<section>
  <div class="conteneurRow enteteJournee">
    <p class="width_25pc">
      <?php
        echo $calLigue->nomEquipeDom();
       ?>
    </p>
    <p class="width_50pc">
      <?php
        echo '<span class="journeeEquipe">Journée ' . $calLigue->numJournee() . '</span>';
        echo '<br/>';
        echo 'Stade : ' . $equipe->stade();
      ?>
    </p>
    <p class="width_25pc">
      <?php
        echo $calLigue->nomEquipeExt();
       ?>
    </p>
  </div>
  <div class="conteneurRow enteteEquipe">
    <p class="width_50pc">
      <?php
        $dateDebut = date_create($calReel->dateHeureDebut());
        echo 'Début de la ' . $calReel->numJournee() . 'e journée de L1' .
          '<br/><span class="heureProchaineJournee">' . date_format($dateDebut, 'd/m/Y H:i:s') . '</span>';
      ?>
    </p>
  </div>
  <div class="conteneurRow">
    <div class="conteneurColumn">
      <p>Choix tactique</p>
      <?php
        if (isset($nomenclTactique))
        {
          echo '<select name="choixTactique" class="selectChoixTactiqueBonus" onchange="javascript:submitForm();">';

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
            foreach ($nomenclTactique as $cle => $value)
            {
              $tactique = $value->nbDef() . '-' . $value->nbMil() . '-' . $value->nbAtt();
              if($compoEquipe->codeTactique() == $value->code())
              {
                $tactiqueSelect = $tactique;
                echo '<option value="' . $value->code() . '" selected="selected">' . $tactique . '</option>';
              }
            }

            $tactiquePrecedente = '';
            foreach ($nomenclTactique as $cle => $value)
            {
              $tactique = $value->nbDef() . '-' . $value->nbMil() . '-' . $value->nbAtt();
              if ($tactique != $tactiquePrecedente && $tactique != $tactiqueSelect)
              {
                $tactiquePrecedente = $tactique;
                echo '<option value="' . $value->code() . '">' . $tactique . '</option>';
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
      <select name="choixCapitaine">
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
      <select name="choixBonus" class="selectChoixTactiqueBonus">
        <option value="-1">A venir...</option>
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
          afficherContenuSelectRentrant($numRemplacement, $gb, $def, $mil, $att, $tabRempl, $tabRentrant);
          echo '</div>';
          echo '<p class="signeRemplacement"> > </p>';
          echo '<div>';
          afficherContenuSelectSortant($numRemplacement, $gb, $def, $mil, $att, $tabTitu, $tabRentrant, $tabSortant);

          $value = '';
          if (isset($tabNote[$numRemplacement])) {
            $value = 'value="' . $tabNote[$numRemplacement] . '" ';
          }
          echo '<input class="width_20px margin_left_5px" type="text" name="note_' . $numRemplacement . '"
            onchange="javascript:verifierNote(' . $numRemplacement . ');" size="3" ' . $value . '/>';
          echo '</div>';
          echo '</div>';
        }
      }
    ?>
  </section>
  <div>
    <input type="submit" value="Valider la compo" name="enregistrer" class="marginBottom width_200px" />
  </div>
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
