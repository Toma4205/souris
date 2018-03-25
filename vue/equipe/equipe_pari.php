<?php
  function afficherOption($num, $numSelect)
  {
    if ($num == $numSelect) {
      echo '<option value="' . $num . '" selected="selected">' . $num . '</option>';
    } else {
      echo '<option value="' . $num . '">' . $num . '</option>';
    }
  }

  function afficherSelectScore($nomSelect, $numSelect)
  {
    echo '<select name="' . $nomSelect . '">';
    for ($i = 0; $i <= 10; $i++) {
      afficherOption($i, $numSelect);
    }
    echo '</select>';
  }

  if ($avecJourneeSuiv == TRUE) {
?>
<section class="section_pari conteneurColumn">
    <div class="detail_compo_titre">Pari truqué<!--<img src="web/img/bonusmalus/PNG_pari.png" title="Pari truqué" alt="" width="40px" height="40px"/>-->
    </div>
    <div class="message_ingo">Gain d'un bonus en cas de score exact trouvé</div>
    <div class="conteneurRow width_100pc">
      <div class="width_10pc"><img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeDom->codeStyleCoach()]; ?>" alt="Logo équipe dom." width="40px" height="40px"/></div>
      <div class="width_30pc text_align_right margin_auto_vertical"><?php echo $calLigue->nomEquipeDom(); ?></div>
      <div class="width_8pc margin_auto_vertical"><?php afficherSelectScore('pariDom', $compoEquipe->pariDom()); ?></div>
      <div class="width_4pc margin_auto_vertical">-</div>
      <div class="width_8pc margin_auto_vertical"><?php afficherSelectScore('pariExt', $compoEquipe->pariExt()); ?></div>
      <div class="width_30pc text_align_left margin_auto_vertical"><?php echo $calLigue->nomEquipeExt(); ?></div>
      <div class="width_10pc"><img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeExt->codeStyleCoach()]; ?>" alt="Logo équipe ext." width="40px" height="40px"/></div>
    </div>
</section>
<?php } ?>
