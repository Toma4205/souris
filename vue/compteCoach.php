<?php
// entete
$vueJs = 'compteCoach.js';
$vueCss = 'compteCoach.css';
require_once("vue/commun/enteteflex.php");
?>
<section id="sectionMesActions">
    <header>Mes actions en attente</header>
    <?php
    $nbAction = 0;
    if (sizeof($ligues) > 0)
    {
      echo '<div>';
      echo '<ul>';
      foreach($ligues as $value)
      {
        if ($value->etat() == EtatLigue::MERCATO)
        {
          $nbAction++;
          echo '<li class="detail_action_actu conteneurRow">';
          echo '<div><span class="float_left image_action_actu"><img src="web/img/action_mercato_compte_coach.png" alt="" width="50px" height="50px"/></span>';
          echo 'Ton équipe n\'est pas terminée pour la ligue <b>' . $value->nom() . '</b> !';
          echo '</div><div class="margin_auto"></div><div class="conteneurRowDroite margin_auto_vertical">';
          echo '<input type="submit" class="font_size_point_8rem" value="Je prends Ney et Fekir" name="continuerCreaLigue[' . $value->id() . ']" />';
          echo '</div></li>';
        }
        elseif ($value->etat() == EtatLigue::CREATION)
        {
          if ($value->createur())
          {
            $nbAction++;
            echo '<li class="detail_action_actu conteneurRow">';
            echo '<div><span class="float_left image_action_actu"><img src="web/img/action_creation_compte_coach.png" alt="" width="50px" height="50px"/></span>';
            echo 'Ta ligue <b>' . $value->nom() . '</b> est toujours en cours de création !';
            echo '</div><div class="margin_auto"></div><div class="conteneurRowDroite margin_auto_vertical">';
            echo '<input type="submit" class="font_size_point_8rem" value="J\'y vais" name="continuerCreaLigue[' . $value->id() . ']" />';
            echo '<input type="submit" class="font_size_point_8rem" value="J\'abandonne" name="suppCreaLigue[' . $value->id() . ']" onclick="return confirmerSuppCreaLigue(\'' . $value->nom() . '\');" />';
            echo '</div></li>';
          }
          else if (null == $value->dateValidation())
          {
            $nbAction++;
            echo '<li class="detail_action_actu">';
            echo '<span class="float_left image_action_actu"><img src="web/img/action_invit_compte_coach.png" alt="" width="50px" height="50px"/></span>';
            echo '<b>' . $value->nomCoachCreateur() . '</b> t\'invite dans sa ligue <b>' . $value->nom() . '.</b>';
            if ($value->libellePari() != null) {
              echo '<br/> (enjeu : <b>' . $value->libellePari() . '</b>)';
            }
            echo '<span class="float_right">';
            echo '<input type="submit" class="font_size_point_8rem" value="Je me lance" name="accepterInvitation[' . $value->id() . ']" /> ';
            echo ' <input type="submit" class="font_size_point_8rem" value="J\'ai piscine" name="refuserInvitation[' . $value->id() . ']" />';
            echo '</span>';
            echo '</li>';
          }
        }
      }
      echo '</ul>';
      echo '</div>';
    }
    if ($nbAction == 0) {
      echo '<div>Ton bureau est à jour.</div>';
    }
    ?>
</section>
<section>
    <header>Actualités</header>
    <div>
    <?php

    $nbActu = 0;
    if (sizeof($ligues) > 0 || sizeof($actus) > 0)
    {
      echo '<ul>';
      foreach($ligues as $value)
      {
        if ($value->etat() == EtatLigue::CREATION)
        {
          if ($value->createur() == FALSE && null != $value->dateValidation())
          {
            $nbActu++;
            echo '<li class="detail_action_actu">';
            echo '<span class="float_left image_action_actu"><img src="web/img/actu_compte_coach.png" alt="" width="50px" height="50px"/></span>';
            echo '<b>' . $value->nomCoachCreateur() . '</b> doit valider ta participation à la ligue <b>"' . $value->nom() . '"</b>.';
            echo '</li>';
          }
        }
      }
      foreach($actus as $value)
      {
        $nbActu++;
        echo '<li class="detail_action_actu conteneurRow" id="actu_'.$value->id().'">';
        echo '<div class="conteneurRowGauche image_action_actu margin_0"><img src="web/img/actu_compte_coach.png" alt="" width="50px" height="50px"/></div>';
        echo '<div class="conteneurRowGauche text_align_left margin_0">'.$value->libelle().'</div>';
        echo '<div class="margin_auto"></div>';
        echo '<div class="conteneurRowDroite margin_auto_vertical">';
        echo '<img src="web/img/croix.jpg" alt="" width="20px" height="20px" onclick="supprimerActualite(\''.$value->id().'\')"/>';
        echo '</div>';
        echo '</li>';
      }
      echo '</ul>';
    }

    echo '<div id="message_aucune_actu" class="';
    if ($nbActu > 0) {
      echo 'cache';
    }
    echo '">Aucune actualité pour le moment. <b>#viedeouf</b></div>';

    echo '<div id="count_actu" class="cache">'.$nbActu.'</div>';
    ?>
    </div>
</section>
<section id="sectionMesLigues">
      <header>Mes ligues</header>
      <?php
      if (sizeof($ligues) > 0)
      {
      ?>
      <table class="tableBase">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Classement</th>
            <?php if ($numJourneeEnCours != null){echo '<th>Score en cours</th>';} ?>
            <th>Etat</th>
            <th>Action</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php
          echo '<input type="hidden" name="masquer"/>';
          echo '<input type="hidden" name="scoreLigue"/>';
          foreach($ligues as $value)
          {
            if ($value->etat() == EtatLigue::EN_COURS || $value->etat() == EtatLigue::TERMINEE)
            {
              echo '<tr><td>' . $value->nom() . '</td>';
              if ($value->classement() != null)
              {
                echo '<td>' . $value->classement() . '</td>';
              }
              else
              {
                echo '<td>Aucun</td>';
              }

              if ($numJourneeEnCours != null) {
                if ($value->scoreDom() != null) {
                  $classeVictDefaire = 'score_direct';
                  if (($value->dom() == TRUE && $value->scoreDom() > $value->scoreExt())
                    || ($value->dom() == FALSE && $value->scoreDom() < $value->scoreExt())) {
                    $classeVictDefaire = 'score_direct_victoire';
                  } else if (($value->dom() == TRUE && $value->scoreDom() < $value->scoreExt())
                    || ($value->dom() == FALSE && $value->scoreDom() > $value->scoreExt())) {
                    $classeVictDefaire = 'score_direct_defaite';
                  }
                  echo '<td class="' . $classeVictDefaire . '" onclick="allerVersScoreLigue(\'' . $value->id() . '\')"><b>' . $value->scoreDom() . ' - ' . $value->scoreExt() . '</b></td>';
                } else {
                  echo '<td></td>';
                }
              }

              if ($value->etat() == EtatLigue::EN_COURS) {
                echo '<td>En cours</td>';
              } else {
                echo '<td>Terminé</td>';
              }

              echo '<td><input type="submit" value="Rejoindre" name="rejoindre[' . $value->id() . ']" /></td>';
              echo '<td>';
              if ($value->etat() == EtatLigue::TERMINEE) {
                echo '<img src="./web/img/croix.jpg" alt="Masquer" title="Masquer cette ligue"
                  width="15px" height="15px" onclick="javascript:masquerLigue(\'' . $value->id() . '\');" />';
              }
              echo '</td></tr>';
            }
          }
          echo '</tbody></table>';
        }
        else
        {
          echo '<br/>';
          echo 'Aucune ligue. Faut se mettre au boulot jeune padawan !';
        }
          ?>
</section>

<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
