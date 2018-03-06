<?php
// entete
$vueCss = 'calendrier.css';
$vueJs = 'calendrier.js';
require_once("vue/commun/enteteflex.php");

if (isset($calendriers))
{
?>
<section class="calendrier_journee">
  <select name="journees" class="choix_journee" onchange="javascript:afficherDivJournee(this)">
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
    $tabIdEquipeParJournee = [];
    foreach ($calendriers as $cle => $value)
    {
      if ($numJournee < $value->numJournee())
      {
        // Changement de journée

        $numJournee = $value->numJournee();
        if ($numJournee > 1)
        {
          // Recherche équipe exempt
          if (sizeof($equipes) % 2 == 1) {
            foreach ($equipes as $idEqu => $equ) {
              if (!in_array($idEqu, $tabIdEquipeParJournee)) {
                echo '<div class="detail_journee_cal_match conteneurRow">';
                echo 'Exempt : ' . $equ->nom();
                echo '</div>';
                break;
              }
            }
          }
          $tabIdEquipeParJournee = [];

          // Si ce n'est pas la première journée, on ferme le div de la journée précédente
          echo '</div>';
        }
?>
  <div id="divJournee<?php echo $numJournee; ?>"
    class="detail_journee_cal <?php if ($numJournee != $indexJournee) echo 'cache'; ?>">
<?php
      }
 ?>
<div class="detail_journee_cal_match conteneurRow">
  <div class="detail_journee_cal_col_g">
    <img class="float_left" src="web/img/coach/<?php echo $tabNomenclStyleCoach[$value->codeStyleCoachDom()]; ?>" alt="Logo équipe dom." width="30px" height="30px"/>
    <div class="float_right"><?php echo $value->nomEquipeDom(); ?></div>
  </div>
  <div class="detail_journee_cal_col_c">
    <?php
        $tabIdEquipeParJournee[] = $value->idEquipeDom();
        $tabIdEquipeParJournee[] = $value->idEquipeExt();
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
  <div class="detail_journee_cal_col_d">
    <img class="float_right" src="web/img/coach/<?php echo $tabNomenclStyleCoach[$value->codeStyleCoachExt()]; ?>" alt="Logo équipe ext." width="30px" height="30px"/>
    <div class="float_left"><?php echo $value->nomEquipeExt(); ?></div>
  </div>
</div>
<?php
} // Fin foreach calendriers
// Fermeture du div de la dernière journée
echo '</div>';
?>
</section>
<section id="detailMatch" class="detail_match">
<?php
if (isset($match)) {
?>

  <!-- ********************* -->
  <!-- AFFICHAGE DU BANDEAU -->
  <!-- ********************* -->

  <?php include_once('calendrier/calendrier_bandeau.php') ?>

  <!-- ********************* -->
  <!-- AFFICHAGE DU TERRAIN -->
  <!-- ********************* -->

  <div class="detail_match_terrain_bloc cache">
    <div class="detail_match_terrain_conteneur_img">
      <div class="detail_match_terrain_dom">
        <!-- GB -->
        <div class="detail_match_terrain_ligne">
          <div class="detail_match_terrain_position_centre">
            <img class="detail_match_terrain_maillot" src="web/img/maillot/shirt_mon.png" />
            <div>Subasic</div>
          </div>
        </div>
        <!-- DEF -->
        <div class="detail_match_terrain_ligne">
        </div>
        <!-- MIL -->
        <div class="detail_match_terrain_ligne">
        </div>
        <!-- ATT -->
        <div class="detail_match_terrain_ligne">
        </div>
      </div>
      <div class="detail_match_terrain_ext">
      </div>
    </div>
  </div>

  <!-- ********************* -->
  <!-- AFFICHAGE DES EQUIPES -->
  <!-- ********************* -->

  <?php include_once('calendrier/calendrier_detail_equipe.php') ?>

<?php
} // Fin du if (isset($match))
?>
</section>
<?php
} // Fin du if (isset($calendriers))
else
{
    $message = 'Calendrier indisponible ! Veuillez nous contacter en indiquant le nom de votre ligue.';
}

// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
