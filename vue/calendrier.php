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
    foreach ($calendriers as $cle => $value)
    {
      if ($numJournee < $value->numJournee())
      {
        // Changement de journée
        $numJournee = $value->numJournee();
        if ($numJournee > 1)
        {
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

  // Afficahge des buteurs dans le detail_match_bandeau
  function afficherButeur($joueurs, $joueursAdv, $codeBonusAdv, $nomJoueur, $statut)
  {
    if ($statut == ConstantesAppli::STATUT_CAL_TERMINE)
    {
      if ($joueurs != null) {
        foreach ($joueurs as $cle => $value)
        {
          if ($value->numeroDefinitif() != null && ($value->nbButReel() > 0 || $value->nbButVirtuel() > 0))
          {
            $total = $value->nbButReel() + $value->nbButVirtuel();
            echo '<li';
            if ($value->nbButVirtuel() > 0) {
                echo ' class="buteur_virtuel"';
            }
            echo '>' . $value->nom();
            for ($index = 1; $index <= $total; $index++) {
              if ($index == 1 && $codeBonusAdv == ConstantesAppli::BONUS_MALUS_DIN_ARB  && $nomJoueur == $value->nom()) {
                echo '<img class="but" src="web/img/but_annule.png" alt="But" width="10px" height="10px"/>';
              } else {
                echo '<img class="but" src="web/img/but.png" alt="But" width="10px" height="10px"/>';
              }
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

  // Affichage des bonus/malus dans le detail_match
  function afficherBonusMalus($codeBonus, $libBonus, $nomJoueurEquipe, $nomJoueurAdv, $miTemps, $codeTactique)
  {
    echo '<div class="detail_match_equipe_bonus_malus">';
    echo '<div class="detail_match_equipe_titre">Bonus</div>';
    echo '<div class="detail_match_equipe_bonus_malus_liste conteneurRow">';

    $avecBonus = false;
    if ($codeBonus != null) {
      $avecBonus = true;
      echo '<div class="margin_auto conteneurRow">';
      echo '<div>';
      echo '<div class="detail_match_equipe_bonus_malus_lib">' . $libBonus . '</div>';
      if ($codeBonus == ConstantesAppli::BONUS_MALUS_FUMIGENE) {
        echo '<img src="web/img/bonusmalus/PNG_fumigenes.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_DIN_ARB) {
        echo '<img src="web/img/bonusmalus/PNG_diner.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_FAM_STA) {
        echo '<img src="web/img/bonusmalus/PNG_family.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BUS) {
        echo '<img src="web/img/bonusmalus/PNG_bus.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_MAU_CRA) {
        echo '<img src="web/img/bonusmalus/PNG_mauvaisCrampon.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BOUCHER) {
        echo '<img src="web/img/bonusmalus/PNG_butcher.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CHA_GB) {
        echo '<img src="web/img/bonusmalus/PNG_changementGardien.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_PAR_TRU) {
        echo '<img src="web/img/bonusmalus/PNG_pari.png" alt="bonus dom." width="40px" height="40px"/>';
      } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CON_ZZ) {
        echo '<img src="web/img/bonusmalus/PNG_zizou.png" alt="bonus dom." width="40px" height="40px"/>';
      }
      echo '</div>';
      echo '<div class="detail_match_equipe_bonus_malus_joueur">';
      echo '<div class="detail_match_equipe_bonus_malus_joueur_bloc">';
      if ($nomJoueurEquipe != null) {
        echo '<div>' . $nomJoueurEquipe;
        if ($miTemps != null) {
          echo ' (mi-temps : ' . $miTemps . ')';
        }
        echo '</div>';
      }
      if ($nomJoueurAdv != null) {
        echo '<div>Adv : ' . $nomJoueurAdv . '</div>';
      }
      echo '</div>';
      echo '</div>';
      echo '</div>';
    }
    if (substr($codeTactique, 0, 1) === "4") {
      $avecBonus = true;
      echo '<div class="margin_auto">4 def = +0.5</div>';
    } else if (substr($codeTactique, 0, 1) === "5") {
      $avecBonus = true;
      echo '<div class="margin_auto">5 def = +1</div>';
    }
    // TODO MPL Voir affichage sélectionneur

    if (!$avecBonus) {
      echo '<div>Aucun Bonus</div>';
    }

    echo '</div>';
    echo '</div>';
  }

  function afficherJoueur($joueur, $nom, $tabRemp)
  {
    echo '<li class="detail_match_equipe_joueur';
    if ($joueur->numeroDefinitif() == null) {
      echo ' pas_joue';
    }
    echo '"><b>' . $joueur->numero() . '</b> ' . $nom;
    if ($joueur->capitaine() == 1) {
      echo '<b> (C)</b>';
    }
    if ($joueur->note() != null) {
      echo '<span class="float_right detail_match_equipe_joueur_note_bonus_malus">';
      if ($joueur->noteBonus() != null && $joueur->noteBonus() != 0) {
        if (substr($joueur->noteBonus(), 0, 1) !== "-") {
          echo '+';
        }
        echo $joueur->noteBonus();
      } else {
        echo '.';
      }
      echo '</span>';
      echo '<span class="float_right detail_match_equipe_joueur_note bold">' . $joueur->note() . '</span>';
    } elseif ($tabRemp != null && !isset($tabRemp[$joueur->numero()])) {
      echo '<span class="float_right detail_match_equipe_joueur_tontonpat">';
      echo '<img src="web/img/tontonpat.png" alt="Tonton Pat\'" title="Tonton Pat\'" width="18px" height="18px"/>';
      echo '</span>';
    }

    echo '</li>';
  }

  // Affichage des titulaires dans le detail_match
  function afficherTitulaires($joueurs, $codeTactique)
  {
    echo '<div class="detail_match_equipe_titu">';
    echo '<div class="detail_match_equipe_titre">Titulaires (' . $codeTactique . ')';
    echo '<span class="float_right detail_match_equipe_joueur_note_bonus_malus_titre">Dont</span>';
    echo '<span class="float_right detail_match_equipe_joueur_note_titre">Note</span>';
    echo '</div>';
    echo '<div>';
    echo '<ul>';

    // On cherche les Remplaçants
    $tabRemp = [];
    foreach ($joueurs as $cle => $value)
    {
      if ($value->numero() > 11 && $value->numeroDefinitif() != null) {
        $tabRemp[$value->numeroDefinitif()] = $value->numero();
      }
    }

    foreach ($joueurs as $cle => $value)
    {
      if ($value->numero() > 11) {
        break;
      }
      afficherJoueur($value, $value->nom(), $tabRemp);
    }

    echo '</ul>';
    echo '</div>';
    echo '</div>';
  }

  // Affichage des remplaçants dans le detail_match
  function afficherRemplacants($joueurs)
  {
    echo '<div class="detail_match_equipe_rempl">';
    echo '<div class="detail_match_equipe_titre">Remplaçants';
    echo '<span class="float_right detail_match_equipe_joueur_note_bonus_malus_titre">Dont</span>';
    echo '<span class="float_right detail_match_equipe_joueur_note_titre">Note</span>';
    echo '</div>';
    echo '<div>';
    echo '<ul>';

    $tabRempl;
    foreach ($joueurs as $cle => $value)
    {
      if ($value->numero() > 11)
      {
        $nom = $value->nom();
        if (isset($tabRempl[$value->idJoueurReel()]))
        {
          $nom = $tabRempl[$value->idJoueurReel()];
        }
        afficherJoueur($value, $nom, null);
      }
      else if ($value->numeroRemplacement() != null)
      {
          $tabRempl[$value->idJoueurReelRemplacant()] = $value->nomRemplacant() . ' remplace ' . $value->nom() . ' si note < ' . $value->noteMinRemplacement();
      }
    }

    echo '</ul>';
    echo '</div>';
    echo '</div>';
  }

  function afficherMoyennes($joueurs, $code)
  {
    $nbDefInit = 0;
    $nbMilInit = 0;
    $nbAttInit = 0;
    $nbDef = 0;
    $nbMil = 0;
    $nbAtt = 0;
    $noteDef = 0;
    $noteMil = 0;
    $noteAtt = 0;
    // Par défaut 1 = jeune du club
    $noteGB = 1;

    foreach ($joueurs as $cle => $value)
    {
      if ($value->numero() <= 11)
      {
        if ($value->position() == ConstantesAppli::DEFENSEUR) {
          $nbDefInit += 1;
        } else if ($value->position() == ConstantesAppli::MILIEU) {
          $nbMilInit += 1;
        } else if ($value->position() == ConstantesAppli::ATTAQUANT) {
          $nbAttInit += 1;
        }
      }
      if ($value->numeroDefinitif() != null)
      {
        if ($value->position() == ConstantesAppli::DEFENSEUR) {
          $nbDef += 1;
          $noteDef += $value->note();
        } else if ($value->position() == ConstantesAppli::MILIEU) {
          $nbMil += 1;
          $noteMil += $value->note();
        } else if ($value->position() == ConstantesAppli::ATTAQUANT) {
          $nbAtt += 1;
          $noteAtt += $value->note();
        } else {
          $noteGB = $value->note();
        }
      }
    }

    // TODO MPL-TVE les moyennes sont déjà calculées pour les buts virtuels => peut être les mettre en BDD ??
    $moyDef = 0;
    if ($nbDef > 0) {
      $moyDef = ($noteDef / $nbDef) - ($nbDefInit - $nbDef);
    }

    $moyMil = 0;
    if ($nbMil > 0) {
      $moyMil = ($noteMil / $nbMil) - ($nbMilInit - $nbMil);
    }

    $moyAtt = 0;
    if ($nbAtt > 0) {
      $moyAtt = ($noteAtt / $nbAtt) - ($nbAttInit - $nbAtt);
    }

    $moyGen = (($moyDef * $nbDefInit) + ($moyMil * $nbMilInit) + ($moyAtt * $nbAttInit) + $noteGB) / 11;

    echo '<div class="detail_match_equipe_moyenne">';
    echo '<div class="detail_match_equipe_titre">Moyennes</div>';
    echo '<div>';
    echo '<ul>';

    echo '<li class="detail_match_equipe_moyenne_ligne">Défense';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyDef, 2) . '</span>';
    echo '</li>';
    echo '<li class="detail_match_equipe_moyenne_ligne">Milieu';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyMil, 2) . '</span>';
    echo '</li>';
    echo '<li class="detail_match_equipe_moyenne_ligne">Attaque';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyAtt, 2) . '</span>';
    echo '</li>';
    echo '<li class="detail_match_equipe_moyenne_ligne">Générale';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyGen, 2) . '</span>';
    echo '</li>';

    echo '</ul>';
    echo '</div>';
    echo '</div>';
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
  <div class="detail_match_equipe conteneurRow">
    <div class="detail_match_equipe_g">
      <?php if (isset($compoDom)){afficherBonusMalus($compoDom->codeBonusMalus(),
        $compoDom->libCourtBonusMalus(), $compoDom->nomJoueurReelEquipe(),
        $compoDom->nomJoueurReelAdverse(), $compoDom->miTemps(), $compoDom->codeTactique());} ?>
      <?php if (isset($joueursDom)){afficherTitulaires($joueursDom, $compoDom->codeTactique());} else {echo '<div>Aucune compo</div>';} ?>
      <?php if (isset($joueursDom)){afficherRemplacants($joueursDom);} ?>
      <?php if (isset($joueursDom) && $match->statut() == ConstantesAppli::STATUT_CAL_TERMINE){afficherMoyennes($joueursDom, $compoDom->codeTactique());} ?>
    </div>
    <div class="detail_match_equipe_d">
      <?php if (isset($compoExt)){afficherBonusMalus($compoExt->codeBonusMalus(),
        $compoExt->libCourtBonusMalus(), $compoExt->nomJoueurReelEquipe(),
        $compoExt->nomJoueurReelAdverse(), $compoExt->miTemps(), $compoExt->codeTactique());} ?>
      <?php if (isset($joueursExt)){afficherTitulaires($joueursExt, $compoExt->codeTactique());} else {echo '<div>Aucune compo</div>';} ?>
      <?php if (isset($joueursExt)){afficherRemplacants($joueursExt);} ?>
      <?php if (isset($joueursExt) && $match->statut() == ConstantesAppli::STATUT_CAL_TERMINE){afficherMoyennes($joueursExt, $compoExt->codeTactique());} ?>
    </div>
  </div>
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
