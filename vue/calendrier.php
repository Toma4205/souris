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
<!-- TODO MPL Centrer select -->
<div>
  <select name="journees" onchange="javascript:afficherDivJournee(this)">
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
</div>
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
<div id="divJournee<?php echo $numJournee; ?>" class="colonnes <?php if ($numJournee != 1) echo 'cache'; ?>" style="height:50px;">
<?php
      }
      else
      {
        $changementJournee = false;
      }
 ?>
 <div>
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
    } // Fin foreach
    echo '</div>';
  }
  else {
    $message = 'Calendrier indisponible ! Veuillez nous contacter en indiquant le nom de votre ligue.';
  }

// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
