<div id="divRemplacant">
  <?php

    function afficherContenuSelectRempl($numPosition, $gb, $def, $mil, $att, $tabCompo)
    {
      $contenu = '<p><span class="spanChoixJoueur">' . $numPosition . '. ';
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

    function ajouterOptionRempl($numPosition, $preLib, $joueurs, $tabCompo)
    {
      $contenu = '';
      foreach ($joueurs as $joueur)
      {
        if (isset($tabCompo[$numPosition]) && $tabCompo[$numPosition] == $joueur->id()) {
          $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $preLib . ') ' . $joueur->nom() . ' - ' . $joueur->libelleEquipe() . '</option>';
        } elseif (in_array($joueur->id(), $tabCompo)) {
          $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $preLib . ') ' . $joueur->nom() . ' - ' . $joueur->libelleEquipe() . '</option>';
        } else {
          $contenu .= '<option value="' . $joueur->id() . '">' . $preLib . ') ' . $joueur->nom() . ' - ' . $joueur->libelleEquipe() . '</option>';
        }
      }

      return $contenu;
    }

    $numPosition = 12;
    if (isset($gb)) {
      echo '<div class="detail_effectif">';
      echo '<div class="detail_effectif_titre">Remplaçants</div>';
      echo '<div>';
      for ($numPosition = 12; $numPosition <= 18; $numPosition++)
      {
          afficherContenuSelectRempl($numPosition, $gb, $def, $mil, $att, $tabCompo);
      }
      echo '</div>';
      echo '</div>';
    }
  ?>
</div>
