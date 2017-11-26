<?php
// entete
$vueJs = 'equipe.js';
require_once("vue/commun/enteteflex.php");

function afficherContenuSelect($libSelect, $nameSelect, $joueurs, $tabCompo)
{
  $contenu = '<p>' . $libSelect;
  $contenu .= '<select name="' . $nameSelect . '">';
  if (isset($tabCompo[$nameSelect]) && $tabCompo[$nameSelect] == -1) {
    $contenu .= '<option value="-1" selected="selected">...</option>';
  } else {
    $contenu .= '<option value="-1">...</option>';
  }

  foreach ($joueurs as $joueur)
  {
    if (isset($tabCompo[$nameSelect]) && $tabCompo[$nameSelect] == $joueur->id()) {
      $contenu .= '<option value="' . $joueur->id() . '" selected="selected">' . $joueur->nom() . ' ' . $joueur->prenom() . '</option>';
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
  <div id="divTactiqueCache" class="cache">
    <?php
      echo '<input id="cache_mode_expert" value="' . $ligue->modeExpert() . '" />';
      foreach ($nomenclTactique as $tactique)
      {
        echo '<input id="cache_classique_' . $tactique->code() . '" value="' .
          $tactique->nbDef() . ',' . $tactique->nbMil() . ',' . $tactique->nbAtt() . '"/>';
        // TODO MPL continuer cache expert
        echo '<input id="cache_expert_' . $tactique->code() . '" value="' . $tactique->nbDc() . '"/>';
      }
     ?>
  </div>
  <div class="conteneurRow enteteJournee">
    <p class="width_25pc">
      <?php
        echo $calLigue->nomEquipeDom();
       ?>
    </p>
    <p class="width_50pc journeeEquipe">
      <?php
        echo 'Journée ' . $calLigue->numJournee();
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
  <div class="conteneurColumn">
    <p>
      Choix tactique
    </p>
    <?php
        if (isset($nomenclTactique))
        {
          echo '<select name="choixTactique" class="selectChoixTactique" onchange="javascript:submitForm();">';

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
  <div id="rowCompoEquipe" class="conteneurRow">
    <div>
      <img src="./web/img/terrain_442.jpg" alt="Tactique" width="300px" height="400px" />
    </div>
    <div id="contenuCompoEquipe" class="conteneurColumnGauche">
      <?php
        if ($ligue->modeExpert() != TRUE)
        {
          if (isset($gb))
          {
            afficherContenuSelect('1. GB ', 'choixGB', $gb, $tabCompo);
          }

          $numPosition = 2;
          if (isset($def))
          {
            for ($index = 1; $index <= $choixTactique->nbDef(); $index++)
            {
              afficherContenuSelect($numPosition . '. DEF ', 'choixDEF' . $index, $def, $tabCompo);
              $numPosition++;
            }
          }
          if (isset($mil))
          {
            for ($index = 1; $index <= $choixTactique->nbMil(); $index++)
            {
              afficherContenuSelect($numPosition . '. MIL ', 'choixMIL' . $index, $mil, $tabCompo);
              $numPosition++;
            }
          }
          if (isset($att))
          {
            for ($index = 1; $index <= $choixTactique->nbAtt(); $index++)
            {
              afficherContenuSelect($numPosition . '. ATT ', 'choixATT' . $index, $att, $tabCompo);
              $numPosition++;
            }
          }
        }
        else
        {
          echo '<p>Mode expert à venir...</p>';
        }
      ?>
    </div>
  </div>
  <input type="submit" value="Valider la compo" name="enregistrer" class="marginBottom" />
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
