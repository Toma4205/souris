<?php
// entete
$vueJs = 'calendrier.js';
require_once("vue/commun/entete.php");
?>
<div class="sousTitre"><h3>Calendrier</h3></div>
<?php
  if (isset($calendriers))
  {
?>
<div>
  <select name="journees" onchange="javascript:afficherDivJournee(this)">
    <?php
        $index = 1;
        foreach ($calendriers as $cle => $value)
        {
          if($index == 1)
          {
              echo '<option value="divJournee' . $index . '" selected="selected">Journée ' . $index . '</option>';
          }
          else
          {
              echo '<option value="divJournee' . $index . '">Journée ' . $index . '</option>';
          }
          $index++;
        }
     ?>
  </select>
</div>
<?php
    $index = 1;
    foreach ($calendriers as $cle => $value)
    {
?>
<div id="divJournee<?php echo $index; ?>" class="colonnes <?php if ($index != 1) echo 'cache'; ?>" style="height:50px;">
  <div class="colonne" style="width:47%;vertical-align:middle;">
    <div style="float:right;"><?php echo $value->nomEquipeDom(); ?></div>
  </div>
  <div class="colonne" style="width:6%;vertical-align:middle;">
    <?php
        if ($value->scoreDom() != null)
        {
          echo '<div class="centre" style="text-align:center;">' . $value->scoreDom() . '-' . $value->scoreExt() . '</div>';
        }
        else
        {
          echo '<div class="centre" style="text-align:center;">-</div>';
        }
    ?>
  </div>
  <div class="colonne" style="width:47%;vertical-align:middle;">
    <div style="float:left;"><?php echo $value->nomEquipeExt(); ?></div>
  </div>
</div>
<?php
      $index++;
    } // Fin foreach
  }
  else {
    $message = 'Calendrier indisponible ! Veuillez nous contacter en indiquant le nom de votre ligue.';
  }

// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
