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
          if($index == 1)
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
    class="calendrier_matchs colonnes <?php if ($numJournee != 1) echo 'cache'; ?>">
<?php
      }
      else
      {
        $changementJournee = false;
      }
 ?>
  <div class="colonnes border_bottom_single <?php if ($changementJournee) echo 'border_top_single'; ?>">
    <div class="colonne width_47pc vertical_align_middle">
      <div class="float_right"><?php echo $value->nomEquipeDom(); ?></div>
    </div>
    <div class="colonne width_6pc vertical_align_middle">
    <?php
        if ($value->scoreDom() != null)
        {
          echo '<div style="text-align:center;">' . $value->scoreDom() . ' - ' . $value->scoreExt() . '</div>';
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
    echo '</section>';
}
else {
    $message = 'Calendrier indisponible ! Veuillez nous contacter en indiquant le nom de votre ligue.';
}

// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
