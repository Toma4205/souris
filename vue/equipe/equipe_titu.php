<div id="divTitulaire">
<?php
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

  function ajouterOptionJoueur($numPosition, $joueurs, $tabCompo)
  {
    $contenu = '';
    foreach ($joueurs as $joueur)
    {
      $cleTabCompo = array_search($joueur->id(), $tabCompo);
      if (isset($tabCompo[$numPosition]) && $tabCompo[$numPosition] == $joueur->id()) {
        $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . ' - ' . $joueur->libelleEquipe() . '</option>';
      } elseif ($cleTabCompo > 0 && $cleTabCompo <= 11) {
        $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $joueur->nom() . ' - ' . $joueur->libelleEquipe() . '</option>';
      } else {
        $contenu .= '<option value="' . $joueur->id() . '">' . $joueur->nom() . ' - ' . $joueur->libelleEquipe() . '</option>';
      }
    }
  
    return $contenu;
  }

  if ($ligue->modeExpert() != TRUE)
  {
    if (isset($gb))
    {
      echo '<div class="detail_effectif">';
      echo '<div class="detail_effectif_titre">Gardien</div>';
      echo '<div>';
      afficherContenuSelect('1. ', 1, $gb, $tabCompo);
      echo '</div>';
      echo '</div>';
    }

    $numPosition = 2;
    if (isset($def))
    {
      echo '<div class="detail_effectif">';
      echo '<div class="detail_effectif_titre">Défenseurs</div>';
      echo '<div id="divTitulaireDEF">';
      for ($index = 1; $index <= $choixTactique->nbDef(); $index++)
      {
        afficherContenuSelect($numPosition . '. ', $numPosition, $def, $tabCompo);
        $numPosition++;
      }
      echo '</div>';
      echo '</div>';
    }
    if (isset($mil))
    {
      echo '<div class="detail_effectif">';
      echo '<div class="detail_effectif_titre">Milieux</div>';
      echo '<div id="divTitulaireMIL">';
      for ($index = 1; $index <= $choixTactique->nbMil(); $index++)
      {
        afficherContenuSelect($numPosition . '. ', $numPosition, $mil, $tabCompo);
        $numPosition++;
      }
      echo '</div>';
      echo '</div>';
    }
    if (isset($att))
    {
      echo '<div class="detail_effectif">';
      echo '<div class="detail_effectif_titre">Attaquants</div>';
      echo '<div id="divTitulaireATT">';
      for ($index = 1; $index <= $choixTactique->nbAtt(); $index++)
      {
        afficherContenuSelect($numPosition . '. ', $numPosition, $att, $tabCompo);
        $numPosition++;
      }
      echo '</div>';
      echo '</div>';
    }
  }
  else
  {
    echo '<div>Mode expert à venir...</div>';
  }
?>
</div>
