<?php
// entete
$vueJs = 'equipe.js';
$vueCss = 'equipe.css';
require_once("vue/commun/enteteflex.php");

if (isset($calReel) && $ligue->etat() == EtatLigue::EN_COURS)
{
?>
<section>
  <input type="hidden" name="numJourneeCalReel" value="<?php echo $calReel->numJournee(); ?>"/>

  <!-- ******************** -->
  <!-- AFFICHAGE DU BANDEAU -->
  <!-- ******************** -->

  <?php include_once('equipe/equipe_bandeau.php') ?>

  <?php
  if ($calLigue->id() != null)
  {
  ?>

  <?php include_once('equipe/equipe_infos_compo.php') ?>

  <div class="conteneurRow">
    <div class="conteneurColumnGauche width_40pc">

      <?php include_once('equipe/equipe_titu.php') ?>
      <?php include_once('equipe/equipe_remplacant.php') ?>

    </div>
    <div class="compo_equipe_col_droite width_60pc">

      <?php include_once('equipe/equipe_actu.php') ?>
      <?php include_once('equipe/equipe_calendrier.php') ?>
      <?php include_once('equipe/equipe_remplacement.php') ?>

    </div>
  </div>
  <div>
    <input type="submit" value="Valider la compo" name="enregistrer"
      onclick="return controlerBonus();" class="marginBottom width_200px" />
  </div>
  <div id="messageErreurBonus" class="cache">Jean-Michel à moitié... Ta saisie du bonus/malus est incomplète !</div>
  <?php
  } // Fin du IF $calLigue->id() != null
  else
  {
    echo '<p>Pas de match pour toi pour cette journée.</p>';
  }
  ?>
</section>
<?php
}
elseif ($ligue->etat() == EtatLigue::TERMINEE)
{
  echo '<p>Ligue terminée !</p>';
}
else
{
  echo '<p>Plus de match de championnat !</p>';
}

// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
