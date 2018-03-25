<?php
// Affichage des bonus/malus dans le detail_match
function afficherBonusMalus($codeBonus, $libBonus, $libLongBonus, $nomJoueurEquipe, $nomJoueurAdv, $miTemps, $codeTactique)
{
  echo '<div class="detail_match_equipe_bonus_malus">';
  echo '<div class="detail_match_equipe_titre">Bonus</div>';
  echo '<div class="detail_match_equipe_bonus_malus_liste conteneurRow">';

  $avecBonus = false;
  if ($codeBonus != null) {
    $avecBonus = true;
    echo '<div class="margin_auto conteneurRow">';
    echo '<div class="conteneurColumn">';
    echo '<div class="detail_match_equipe_bonus_malus_lib">' . $libBonus . '</div>';
    if ($codeBonus == ConstantesAppli::BONUS_MALUS_FUMIGENE) {
      echo '<img src="web/img/bonusmalus/PNG_fumigenes.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
    } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_DIN_ARB) {
      echo '<img src="web/img/bonusmalus/PNG_diner.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
    } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_FAM_STA) {
      echo '<img src="web/img/bonusmalus/PNG_family.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
    } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BUS) {
      echo '<img src="web/img/bonusmalus/PNG_bus.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
    } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_MAU_CRA) {
      echo '<img src="web/img/bonusmalus/PNG_mauvaisCrampon.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
    } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_BOUCHER) {
      echo '<img src="web/img/bonusmalus/PNG_butcher.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
    } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CHA_GB) {
      echo '<img src="web/img/bonusmalus/PNG_changementGardien.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
    } else if ($codeBonus == ConstantesAppli::BONUS_MALUS_CON_ZZ) {
      echo '<img src="web/img/bonusmalus/PNG_zizou.png" title="'.$libLongBonus.'" alt="bonus" width="40px" height="40px"/>';
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
    echo '<li name="' . $joueur->idJoueurReel() . '" class="detail_match_equipe_joueur';
    if ($joueur->numeroDefinitif() == null) {
      echo ' pas_joue';
    }
    echo '"><b>' . $joueur->numero() . '</b> ' . $nom;
    if ($joueur->capitaine() == 1) {
      echo '<img src="web/img/brassard_capitaine.png" class="image_brassard" alt="Chef Capt\'aine" title="Chef Capt\'aine" width="18px" height="18px"/>';
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
      if ($joueur->numero() == 1) {
        echo '<img src="web/img/jeuneclub.png" alt="Jeune du club" title="Jeune du club" width="18px" height="18px"/>';
      } else {
        echo '<img src="web/img/tontonpat.png" alt="Tonton Pat\'" title="Tonton Pat\'" width="18px" height="18px"/>';
      }
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
          $tabRempl[$value->idJoueurReelRemplacant()] = $value->nomRemplacant() . ' <img src="web/img/rempl.png" alt=" > " width="12px" height="12px" /> ' . $value->nom() . ' si note < ' . $value->noteMinRemplacement();
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
    $nbGb = 0;
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
    }
    foreach ($joueurs as $cle => $value)
    {
      if ($value->numeroDefinitif() != null)
      {
        if ($value->numeroDefinitif() == 1) {
          $nbGb += 1;
          $noteGB = $value->note();
        } else if ($value->numeroDefinitif() <= ($nbDefInit + 1)) {
          $nbDef += 1;
          $noteDef += $value->note();
        } else if ($value->numeroDefinitif() <= ($nbDefInit + $nbMilInit + + 1)) {
          $nbMil += 1;
          $noteMil += $value->note();
        } else {
          $nbAtt += 1;
          $noteAtt += $value->note();
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
    echo '<div class="detail_match_equipe_titre">Moyennes';
    echo '<span class="float_right detail_match_equipe_moyenne_malus_titre" title="-1 par Tonton Pat\'">Dont *</span>';
    echo '<span class="float_right detail_match_equipe_moyenne_titre">Moy.</span>';
    echo '</div>';
    echo '<div>';
    echo '<ul>';

    echo '<li class="detail_match_equipe_moyenne_ligne">Gardien';
    echo '<span class="float_right detail_match_equipe_moyenne_malus">';
    echo '.';
    echo '</span>';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . $noteGB . '</span>';
    echo '</li>';
    echo '<li class="detail_match_equipe_moyenne_ligne">Défense';
    echo '<span class="float_right detail_match_equipe_moyenne_malus">';
    if (($nbDefInit - $nbDef) > 0) {
      echo '-' . ($nbDefInit - $nbDef);
    } else {
      echo '.';
    }
    echo '</span>';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyDef, 2) . '</span>';
    echo '</li>';
    echo '<li class="detail_match_equipe_moyenne_ligne">Milieu';
    echo '<span class="float_right detail_match_equipe_moyenne_malus">';
    if (($nbMilInit - $nbMil) > 0) {
      echo '-' . ($nbMilInit - $nbMil);
    } else {
      echo '.';
    }
    echo '</span>';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyMil, 2) . '</span>';
    echo '</li>';
    echo '<li class="detail_match_equipe_moyenne_ligne">Attaque';
    echo '<span class="float_right detail_match_equipe_moyenne_malus">';
    if (($nbAttInit - $nbAtt) > 0) {
      echo '-' . ($nbAttInit - $nbAtt);
    } else {
      echo '.';
    }
    echo '</span>';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyAtt, 2) . '</span>';
    echo '</li>';
    echo '<li class="detail_match_equipe_moyenne_ligne">Générale';
    echo '<span class="float_right detail_match_equipe_moyenne_malus">.</span>';
    echo '<span class="float_right detail_match_equipe_moyenne_ligne_valeur bold">' . number_format($moyGen, 2) . '</span>';
    echo '</li>';

    echo '</ul>';
    echo '</div>';
    echo '</div>';
  }
?>

<div class="detail_match_equipe conteneurRow">
  <div class="detail_match_equipe_g">
    <?php if (isset($compoDom)){afficherBonusMalus($compoDom->codeBonusMalus(),
      $compoDom->libCourtBonusMalus(), $compoDom->libLongBonusMalus(), $compoDom->nomJoueurReelEquipe(),
      $compoDom->nomJoueurReelAdverse(), $compoDom->miTemps(), $compoDom->codeTactique());} ?>
    <?php if (isset($joueursDom)){afficherTitulaires($joueursDom, $compoDom->codeTactique());} else {echo '<div>Aucune compo</div>';} ?>
    <?php if (isset($joueursDom)){afficherRemplacants($joueursDom);} ?>
    <?php if (isset($joueursDom) && $match->statut() >= ConstantesAppli::STATUT_CAL_TERMINE){afficherMoyennes($joueursDom, $compoDom->codeTactique());} ?>
  </div>
  <div class="detail_match_equipe_d">
    <?php if (isset($compoExt)){afficherBonusMalus($compoExt->codeBonusMalus(),
      $compoExt->libCourtBonusMalus(), $compoExt->libLongBonusMalus(), $compoExt->nomJoueurReelEquipe(),
      $compoExt->nomJoueurReelAdverse(), $compoExt->miTemps(), $compoExt->codeTactique());} ?>
    <?php if (isset($joueursExt)){afficherTitulaires($joueursExt, $compoExt->codeTactique());} else {echo '<div>Aucune compo</div>';} ?>
    <?php if (isset($joueursExt)){afficherRemplacants($joueursExt);} ?>
    <?php if (isset($joueursExt) && $match->statut() >= ConstantesAppli::STATUT_CAL_TERMINE){afficherMoyennes($joueursExt, $compoExt->codeTactique());} ?>
  </div>
</div>
