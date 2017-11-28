<?php
// entete
$vueJs = 'equipe.js';
require_once("vue/commun/enteteflex.php");

function afficherContenuSelect($libSelect, $nameSelect, $joueurs, $classeCss, $tabCompo)
{
  $contenu = '<p><span class="spanChoixJoueur">' . $libSelect;
  $contenu .= '</span><select name="' . $nameSelect . '" class="' . $classeCss . '" onchange="javascript:onChoixJoueur(\''. $nameSelect . '\',\'' . $classeCss . '\');">';
  if (isset($tabCompo[$nameSelect]) && $tabCompo[$nameSelect] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  foreach ($joueurs as $joueur)
  {
    if (isset($tabCompo[$nameSelect]) && $tabCompo[$nameSelect] == $joueur->id()) {
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    } elseif (in_array($joueur->id(), $tabCompo)) {
      $contenu .= '<option class="cache" value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    } else {
      $contenu .= '<option value="' . $joueur->id() . '">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
    }
  }
  $contenu .= '</select></p>';

  echo $contenu;
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
      <p>Bonus/Malus</p>
      <select name="choixBonus" class="selectChoixTactiqueBonus">
        <option value="-1">A venir...</option>
      </select>
    </div>
  </div>
  <div id="rowCompoEquipe" class="conteneurRow">
    <div>
      <img src="./web/img/terrain_442.jpg" alt="Tactique" width="300px" height="400px" />
      <div>
        <input type="submit" value="Valider la compo" name="enregistrer" />
      </div>
    </div>
    <div id="contenuCompoEquipe" class="conteneurColumnGauche">
      <div id="divTitulaire">
        <p id="titulaire">Titulaires</p>
      <?php
        if ($ligue->modeExpert() != TRUE)
        {
          if (isset($gb))
          {
            echo '<div>';
            afficherContenuSelect('1. GB ', 1, $gb, 'selectChoixJoueurGB', $tabCompo);
            echo '</div>';
          }

          $numPosition = 2;
          if (isset($def))
          {
            echo '<div>';
            for ($index = 1; $index <= $choixTactique->nbDef(); $index++)
            {
              afficherContenuSelect($numPosition . '. DEF ', $numPosition, $def, 'selectChoixJoueurDEF', $tabCompo);
              $numPosition++;
            }
            echo '</div>';
          }
          if (isset($mil))
          {
            echo '<div>';
            for ($index = 1; $index <= $choixTactique->nbMil(); $index++)
            {
              afficherContenuSelect($numPosition . '. MIL ', $numPosition, $mil, 'selectChoixJoueurMIL', $tabCompo);
              $numPosition++;
            }
            echo '</div>';
          }
          if (isset($att))
          {
            echo '<div>';
            for ($index = 1; $index <= $choixTactique->nbAtt(); $index++)
            {
              afficherContenuSelect($numPosition . '. ATT ', $numPosition, $att, 'selectChoixJoueurATT', $tabCompo);
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
      <div>
        <p id="remplacant">Remplaçants</p>
        <div>A venir...</div>
      </div>
      <div>
        <p id="capitaine">Capitaine</p>
        <div>A venir...</div>
      </div>
    </div>
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
