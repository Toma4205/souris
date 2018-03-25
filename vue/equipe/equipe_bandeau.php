<div class="detail_match_bandeau conteneurRow">
  <div class="detail_match_bandeau_journee_prec"
    title="<?php if ($avecJourneePrec){echo 'Afficher ma compo de la journée précédente';} ?>"
    <?php if ($avecJourneePrec){echo 'onclick="javascript:submitForm(\'journeePrec\');"';} ?>>
    <?php if ($avecJourneePrec){echo '<';} ?>
  </div>
  <div class="detail_match_bandeau_col_g">
    <div class="detail_match_bandeau_equipe">
      <?php if ($calLigue->id() != null) { ?>
      <div><?php echo $calLigue->nomEquipeDom(); ?></div>
      <div class="detail_match_bandeau_equipe_coach">Coach : <?php echo $equipeDom->nomCoach(); ?></div>
      <div class="detail_match_bandeau_equipe_logo">
        <img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeDom->codeStyleCoach()]; ?>" alt="Logo équipe dom." width="80px" height="80px"/>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="detail_match_bandeau_col_c">
    <div class="detail_match_bandeau_journee">Journée <?php echo $numJournee; ?></div>
    <?php if ($calLigue->id() != null) { ?>
    <div class="detail_match_bandeau_ville">Lieu : <?php echo $equipeDom->ville(); ?></div>
    <div class="detail_match_bandeau_stade">Stade "<?php echo $equipeDom->stade(); ?>"</div>
    <?php } ?>
    <div class="detail_match_bandeau_date">
      <?php
        $dateDebut = date_create($calReel->dateHeureDebut());
        echo 'Début de la journée ' . $calReel->numJournee() . ' de L1' .
          '<br/><span class="heureProchaineJournee">' . date_format($dateDebut, 'd/m/Y H:i:s') . '</span>';
      ?>
    </div>
  </div>
  <div class="detail_match_bandeau_col_d">
    <div class="detail_match_bandeau_equipe">
      <?php if ($calLigue->id() != null) { ?>
      <div><?php echo $calLigue->nomEquipeExt(); ?></div>
      <div class="detail_match_bandeau_equipe_coach">Coach : <?php echo $equipeExt->nomCoach(); ?></div>
      <div class="detail_match_bandeau_equipe_logo">
        <img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeExt->codeStyleCoach()]; ?>" alt="Logo équipe ext." width="80px" height="80px"/>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="detail_match_bandeau_journee_suiv"
    title="<?php if ($avecJourneeSuiv){echo 'Afficher ma compo de la journée suivante';} ?>"
    <?php if ($avecJourneeSuiv){echo 'onclick="javascript:submitForm(\'journeeSuiv\');"';} ?>>
    <?php if ($avecJourneeSuiv){echo '>';} ?>
  </div>
</div>
