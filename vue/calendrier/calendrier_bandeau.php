<?php
// Afficahge des buteurs dans le detail_match_bandeau
function afficherButeur($joueurs, $joueursAdv, $codeBonusAdv, $nomJoueur, $statut)
{
  if ($statut >= ConstantesAppli::STATUT_CAL_TERMINE)
  {
    if ($joueurs != null) {
      foreach ($joueurs as $cle => $value)
      {
        if ($value->numeroDefinitif() != null && (
          ($value->nbButReel() > 0 || $value->nbButVirtuel() > 0)
          || ($codeBonusAdv == ConstantesAppli::BONUS_MALUS_DIN_ARB  && $nomJoueur == $value->nom())))
        {
          $total = $value->nbButReel() + $value->nbButVirtuel();
          echo '<li';
          if ($value->nbButVirtuel() > 0) {
              echo ' class="buteur_virtuel"';
          }
          echo '>' . $value->nom();
          for ($index = 1; $index <= $total; $index++) {
             echo '<img class="but" src="web/img/but.png" alt="But" width="10px" height="10px"/>';
          }

          if ($codeBonusAdv == ConstantesAppli::BONUS_MALUS_DIN_ARB  && $nomJoueur == $value->nom()) {
            echo '<img class="but" src="web/img/but_annule.png" alt="But" width="10px" height="10px"/>';
          }

          echo '</li>';
        }
      }
    }
    if ($joueursAdv != null) {
      foreach ($joueursAdv as $cle => $value)
      {
        if ($value->numeroDefinitif() != null && $value->nbCsc() > 0)
        {
          echo '<li>' . $value->nom() . ' (csc)';
          for ($index = 1; $index <= $value->nbCsc(); $index++) {
            echo '<img class="but" src="web/img/but.png" alt="But" width="10px" height="10px"/>';
          }
          echo '</li>';
        }
      }
    }
  } elseif ($statut == ConstantesAppli::STATUT_CAL_EN_COURS)
  {
    if ($joueurs != null) {
      foreach ($joueurs as $cle => $value)
      {
        if ($value->numero() < 12 && $value->nbButReel() > 0)
        {
          echo '<li>' . $value->nom();
          for ($index = 1; $index <= $value->nbButReel(); $index++) {
            echo '<img class="but" src="web/img/but.png" alt="But" width="10px" height="10px"/>';
          }
          echo '</li>';
        }
      }
    }
    if ($joueursAdv != null) {
      foreach ($joueursAdv as $cle => $value)
      {
        if ($value->numero() < 12 && $value->nbCsc() > 0)
        {
          echo '<li>' . $value->nom() . ' (csc)';
          for ($index = 1; $index <= $value->nbCsc(); $index++) {
            echo '<img class="but" src="web/img/but.png" alt="But" width="10px" height="10px"/>';
          }
          echo '</li>';
        }
      }
    }
  }
}
?>

<div class="detail_match_bandeau conteneurRow">
  <div class="detail_match_bandeau_col_g">
    <div class="detail_match_bandeau_equipe">
      <div><?php echo $match->nomEquipeDom(); ?></div>
      <div class="detail_match_bandeau_equipe_coach">Coach : <?php echo $equipeDom->nomCoach(); ?></div>
      <div class="detail_match_bandeau_equipe_logo">
        <img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeDom->codeStyleCoach()]; ?>" alt="Logo équipe dom." width="80px" height="80px"/>
      </div>
      <div class="detail_match_bandeau_equipe_pari">Pari truqué : <?php if (isset($compoDom)) {echo $compoDom->pariDom() . ' - ' . $compoDom->pariExt();} ?></div>
      <div class="detail_match_bandeau_equipe_buteur">
        <ul>
          <?php
          if (isset($joueursDom)){
            $codeBonus = null;
            $nomJoueurAdv = null;
            if (isset($compoExt)) {
              $codeBonus = $compoExt->codeBonusMalus();
              $nomJoueurAdv = $compoExt->nomJoueurReelAdverse();
            }
            if (isset($joueursExt)) {
              afficherButeur($joueursDom, $joueursExt, $codeBonus, $nomJoueurAdv, $match->statut());
            } else {
              afficherButeur($joueursDom, null, $codeBonus, $nomJoueurAdv, $match->statut());
            }
          } elseif (isset($joueursExt)){
            afficherButeur(null, $joueursExt, null, null, $match->statut());
          }
          ?>
        </ul>
      </div>
    </div>
  </div>
  <div class="detail_match_bandeau_col_c">
    <div class="detail_match_bandeau_etat">
    <?php
      if ($match->statut() == ConstantesAppli::STATUT_CAL_EN_COURS) {
        echo 'En cours';
      } else {
        echo 'Terminé';
      }
     ?>
    </div>
    <div class="detail_match_bandeau_score conteneurRow">
      <div><?php echo $match->scoreDom(); ?></div>
      <div class="detail_match_bandeau_score_tiret">-</div>
      <div><?php echo $match->scoreExt(); ?></div>
    </div>
    <div class="detail_match_bandeau_date">Journée <?php echo $match->numJourneeCalReel(); ?> de Ligue 1</div>
    <div class="detail_match_bandeau_ville">Lieu : <?php echo $equipeDom->ville(); ?></div>
    <div class="detail_match_bandeau_stade">Stade "<?php echo $equipeDom->stade(); ?>"</div>
    <div class="detail_match_bandeau_selectionneur"><?php if ($match->selectionneur() == 1){
      echo '<img src="web/img/bonusmalus/PNG_selection.png" alt="Sélectionneur" width="80px" height="80px"
        title="La Dech\' est dans les tribunes (+0.5 aux français)."/>';} ?></div>
  </div>
  <div class="detail_match_bandeau_col_d">
    <div class="detail_match_bandeau_equipe">
      <div><?php echo $match->nomEquipeExt(); ?></div>
      <div class="detail_match_bandeau_equipe_coach">Coach : <?php echo $equipeExt->nomCoach(); ?></div>
      <div class="detail_match_bandeau_equipe_logo">
        <img src="web/img/coach/<?php echo $tabNomenclStyleCoach[$equipeExt->codeStyleCoach()]; ?>" alt="Logo équipe ext." width="80px" height="80px"/>
      </div>
      <div class="detail_match_bandeau_equipe_pari">Pari truqué : <?php if (isset($compoExt)) {echo $compoExt->pariDom() . ' - ' . $compoExt->pariExt();} ?></div>
      <div class="detail_match_bandeau_equipe_buteur">
        <ul>
          <?php
          if (isset($joueursExt))
          {
            $codeBonus = null;
            $nomJoueurAdv = null;
            if (isset($compoDom)) {
              $codeBonus = $compoDom->codeBonusMalus();
              $nomJoueurAdv = $compoDom->nomJoueurReelAdverse();
            }
            if (isset($joueursDom)) {
              afficherButeur($joueursExt, $joueursDom, $codeBonus, $nomJoueurAdv, $match->statut());
            } else {
              afficherButeur($joueursExt, null, $codeBonus, $nomJoueurAdv, $match->statut());
            }
          }
          elseif (isset($joueursDom)){
           afficherButeur(null, $joueursDom, null, null, $match->statut());
          }
          ?>
        </ul>
      </div>
    </div>
  </div>
</div>
