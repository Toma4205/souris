<?php
function afficherContenuSelectRentrant($numRempl, $def, $mil, $att, $tabRempl, $tabRentrant)
{
  $contenu = '<select name="rentrant_' . $numRempl . '" class="selectChoixRempl" onchange="javascript:onSelectionRentrant(\''. $numRempl . '\');">';
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
  $contenu = '<select name="sortant_' . $numRempl . '" class="selectChoixRempl" onchange="javascript:onSelectionSortant(\''. $numRempl . '\');">';
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

function ajouterOptionRentrant($numPosition, $joueurs, $tabRempl, $tabRentrant)
{
  $contenu = '';
  foreach ($joueurs as $joueur)
  {
    $cleRempl = array_search($joueur->id(), $tabRempl);
    $cleRentrant = array_search($joueur->id(), $tabRentrant);
    if (isset($tabRentrant[$numPosition]) && $tabRentrant[$numPosition] == $joueur->id()) {
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . '</option>';
    } elseif ($cleRempl > 0 && $cleRentrant == 0) {
      $contenu .= '<option value="' . $joueur->id() . '">' . $joueur->nom() . '</option>';
    } else {
      $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $joueur->nom() . '</option>';
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
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . '</option>';
    } elseif ($avecRentrant > 0 && $cleTitu > 0 && $cleSortant == 0) {
      $contenu .= '<option value="' . $joueur->id() . '">' . $joueur->nom() . '</option>';
    } else {
      $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $joueur->nom() . '</option>';
    }
  }

  return $contenu;
}
?>

<section id="divRemplacement" class="section_remplacement conteneurColumn">
    <div id="remplacement" class="detail_effectif_titre text_align_left">Remplacements <span class="detail_effectif_rempl_lib normal">(Joueur de gauche remplace Joueur de droite si note < X)</span></div>

<?php
    $tabTitu = [];
    $tabRempl = [];
    $tabGB = [];
    foreach ($gb as $joueur) {
        $tabGB[] = $joueur->id();
    }

    foreach ($tabCompo as $numero => $joueur) {
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
  echo '<div class="signeRemplacement"><img src="web/img/rempl.png" alt=" > " width="20px" height="20px" /></div>';
  echo '<div>';
  afficherContenuSelectSortant($numRemplacement, $def, $mil, $att, $tabTitu, $tabRentrant, $tabSortant);

  $value = '';
  if (isset($tabNote[$numRemplacement])) {
      $value = 'value="' . $tabNote[$numRemplacement] . '" ';
  }
  echo '<input class="width_25px margin_left_5px" type="text" name="note_' . $numRemplacement . '" onchange="javascript:verifierNote(' . $numRemplacement . ');" maxlength="3" ' . $value . '/>';
  echo '</div>';
  echo '</div>';
  }
}
?>

</section>
