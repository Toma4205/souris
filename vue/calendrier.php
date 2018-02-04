<?php
// entete
$vueJs = 'calendrier.js';
require_once("vue/commun/enteteflex.php");

if (isset($calendriers))
{
?>
<section class="calendrier_journee">
  <select name="journees" class="font_size_20px" onchange="javascript:afficherDivJournee(this)">
    <?php
        $numJourneeMax = 2;
        foreach ($calendriers as $cle => $value)
        {
          if($value->numJournee() > $numJourneeMax)
          {
              $numJourneeMax = $value->numJournee();
          }
        }
        for ($index = 1; $index <= $numJourneeMax; $index++) {
          if($index == $indexJournee)
          {
              echo '<option value="divJournee' . $index . '" selected="selected">Journée ' . $index . '</option>';
          }
          else
          {
              echo '<option value="divJournee' . $index . '">Journée ' . $index . '</option>';
          }
        }
     ?>
  </select>
  <input type="hidden" id="idMatch" name="id_match" />
<?php
    $numJournee = 0;
    $changementJournee = false;
    foreach ($calendriers as $cle => $value)
    {
      if ($numJournee < $value->numJournee())
      {
        $numJournee = $value->numJournee();
        $changementJournee = true;

        if ($numJournee > 1)
        {
          echo '</div>';
        }
?>
  <div id="divJournee<?php echo $numJournee; ?>"
    class="calendrier_matchs colonnes <?php if ($numJournee != $indexJournee) echo 'cache'; ?>">
<?php
      }
      else
      {
        $changementJournee = false;
      }
 ?>
  <div class="colonnes">
    <div class="colonne width_47pc vertical_align_middle">
      <div class="float_right"><?php echo $value->nomEquipeDom(); ?></div>
    </div>
    <div class="colonne width_6pc vertical_align_middle">
    <?php
        if ($value->scoreDom() != null)
        {
          echo '<div class="score_match" onclick="javascript:stockerMatch(' . $value->id() . ');">'
          . $value->scoreDom() . ' - ' . $value->scoreExt() . '</div>';
        }
        else
        {
          echo '<div style="text-align:center;">-</div>';
        }
    ?>
    </div>
    <div class="colonne width_47pc vertical_align_middle">
      <div class="float_left"><?php echo $value->nomEquipeExt(); ?></div>
    </div>
  </div>
<?php
    } // Fin foreach
?>
</section>
<section id="detailMatch" class="detail_match">
<?php
  if (isset($_POST["id_match"]))
  {
    function afficherScore($nom, $score)
    {
      echo '<div style="width:50%">';
      echo '<div><p>' . $nom . '</p></div>';
      echo '<div><p>' . $score . '</p></div>';
    }
    function afficherEquipe($compo, $joueurs)
    {
      if ($compo != null) {
        echo '<div><p>' . $compo->codeTactique() . '</p></div>';
        if ($joueurs != null) {
          foreach ($joueurs as $cle => $value)
          {
            echo '<p class="joueurMatch">';
            echo '<b>' . $value->numero() . '</b> - ' . $value->nom() . ' ' . $value->prenom();
            echo '<span class="float_right">';
            echo '<input type="text" class="inputPrix" value="' . $value->note() . '" disabled/>';
            echo '</span></p>';
          }
        }
      } else {
        echo '<div><p>-</p></div>';
        echo '<div><p>Coach en vacances = forfait !</p></div>';
      }
    }

    foreach ($calendriers as $cle => $value)
    {
      if ($_POST["id_match"] == $value->id())
      {
        echo '<div class="detail_equipes conteneurRow">';

        // Equipe DOMICILE
        afficherScore($value->nomEquipeDom(), $value->scoreDom());
        if (isset($joueursDom))
        {
          afficherEquipe($compoDom, $joueursDom);
        }
        else
        {
          echo '<div><p>-</p></div>';
          echo '<div><p>Coach en vacances = forfait !</p></div>';
        }
        echo '</div>';

        // Equipe EXTERIEURE
        afficherScore($value->nomEquipeExt(), $value->scoreExt());
        if (isset($joueursExt))
        {
          afficherEquipe($value->nomEquipeExt(), $value->scoreExt(), $compoExt, $joueursExt);
        }
        else
        {
          echo '<div><p>-</p></div>';
          echo '<div><p>Coach en vacances = forfait !</p></div>';
        }
        echo '</div>';

        echo '</div>';
        break;
      }
    }
  }
?>
</section>
<?php
}
else {
    $message = 'Calendrier indisponible ! Veuillez nous contacter en indiquant le nom de votre ligue.';
}

// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
