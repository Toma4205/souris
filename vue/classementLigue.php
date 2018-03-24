<?php
// entete
$vueJs = 'classement.js';
$vueCss = 'classement.css';

require_once("vue/commun/enteteflex.php");
?>
<header class="menuClassement">
  <p id="choixClassement" class="bold" onclick="javascript:afficherSection(this, 'sectionClassement');">Classement</p>
  <p id="choixButeurs" onclick="javascript:afficherSection(this, 'sectionButeurs');">Buteurs</p>
  <p id="choixJoueurs" onclick="javascript:afficherSection(this, 'sectionJoueurs');">Joueurs</p>
  <p id="choixEffectif" onclick="javascript:afficherSection(this, 'sectionEffectifs');">Effectifs</p>
  <p id="choixTrophee" onclick="javascript:afficherSection(this, 'sectionTrophees');">Trophées</p>
</header>
<section id="sectionClassement">
  <div>
    <table class="tableBase">
      <thead>
        <tr>
          <th>Equipe</th>
          <th>Pts</th>
          <th>J.</th>
          <th>G.</th>
          <th>N.</th>
          <th>P.</th>
          <th>B.p.</th>
          <th>B.c.</th>
          <th>+/-</th>
          <th>Bonus</th>
          <th>Malus</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $index = 1;
        foreach($equipes as $value)
        {
          echo '<tr><td>' . $index . ' . ' . $value->nom();
          if ($index == sizeof($equipes)) {
            echo '<img src="web/img/souris.png" class="margin_left_5px" title="La souris actuelle"
              alt="" width="15px" height="15px"/>';
          }
          echo '</td>';
          echo '<td>' . ((3 * $value->nbVictoire()) + $value->nbNul()) . '</td>';
          echo '<td>' . $value->nbMatch() . '</td>';
          echo '<td>' . $value->nbVictoire() . '</td>';
          echo '<td>' . $value->nbNul() . '</td>';
          echo '<td>' . $value->nbDefaite() . '</td>';
          echo '<td>' . $value->nbButPour() . '</td>';
          echo '<td>' . $value->nbButContre() . '</td>';
          echo '<td>' . ($value->nbButPour() - $value->nbButContre()) . '</td>';
          echo '<td>' . $value->nbBonus() . '</td>';
          echo '<td>' . $value->nbMalus() . '</td></tr>';

          $index++;
        }
      ?>
      </tbody>
    </table>
  </div>
  <?php
    if ($ligue->libellePari() != null) {
      echo '<div class="classement_pari_ligue"><span class="classement_pari_ligue_lib">Pari de la ligue</span> : ' . $ligue->libellePari() . '</div>';
    }
   ?>
</section>
<section id="sectionButeurs" class="cache">
  <div>
    <?php
    if (isset($buteurs) && count($buteurs) > 0)
    {
    ?>
    <table class="tableBase tableButeur">
      <thead>
        <tr>
          <th>Joueur</th>
          <th>Equipe</th>
          <th>Total (réel + fictif)</th>
          <th>Prix</th>
          <th>Match</th>
          <th>B. / Match</th>
          <th>€ / B.</th>
          <th>Tour mercato</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $index = 1;
        foreach($buteurs as $value)
        {
          echo '<tr><td>' . $index . ' . ' . $value->nom() . '</td>';
          echo '<td>' . $value->nomEquipe() . '</td>';
          echo '<td>' . $value->totalBut() . ' (' . $value->nbButReel() . '+' . $value->nbButVirtuel() . ')</td>';
          echo '<td>' . $value->prixAchat() . '</td>';
          echo '<td>' . $value->nbMatch() . '</td>';
          echo '<td>' . round(($value->totalBut() / $value->nbMatch()), 2) . '</td>';
          echo '<td>' . round(($value->prixAchat() / $value->totalBut()), 2) . '</td>';
          echo '<td>' . $value->tourMercato() . '</td></tr>';

          $index++;
        }
      ?>
      </tbody>
    </table>
    <?php
    } else {
      echo '<p>Aucun buteur pour le moment.</p>';
    }
    ?>
  </div>
</section>
<section id="sectionJoueurs" class="cache">
  <div>
    <div class="bloc_choix_stats_joueurs">
      <select name="statsEquipe" class="choix_stats_equipe" onchange="javascript:afficherDivStatsEquipe(this)">
      <?php
          foreach ($equipes as $cle => $value) {
            if($value->id() == $equipe->id())
            {
                echo '<option value="statsEquipe' . $value->id() . '" selected="selected">' . $value->nom() . '</option>';
            }
            else
            {
                echo '<option value="statsEquipe' . $value->id() . '">' . $value->nom() . '</option>';
            }
          }
       ?>
     </select>
   </div>
   <?php

    function afficherStatsJoueurs($joueurs)
    {
      $top = array(1 => new JoueurEquipe(["moy_note" => 0]),
                  2 => new JoueurEquipe(["moy_note" => 0]),
                  3 => new JoueurEquipe(["moy_note" => 0]));
      foreach ($joueurs as $cle => $joueur)
      {
        if ($top[1]->moyNote() <= $joueur->moyNote()) {
          $top[3] = $top[2];
          $top[2] = $top[1];
          $top[1] = $joueur;
        } elseif ($top[2]->moyNote() <= $joueur->moyNote()) {
          $top[3] = $top[2];
          $top[2] = $joueur;
        }
        elseif ($top[3]->moyNote() <= $joueur->moyNote()) {
          $top[3] = $joueur;
        }
      }

      echo '<div class="detail_effectif">';
      echo '<div class="detail_effectif_titre">Top 3 - Equipe';
      echo '<span class="float_right detail_effectif_joueur_prix detail_effectif_joueur_caract_titre">Prix Achat</span>';
      echo '<span class="float_right detail_effectif_joueur_nb_match detail_effectif_joueur_caract_titre">Nb match</span>';
      echo '<span class="float_right detail_effectif_joueur_moy detail_effectif_joueur_caract_titre">Note moy.</span>';
      echo '</div>';
      echo '<div>';
      echo '<ul>';

      $nb = 0;
      foreach ($top as $cle => $joueur)
      {
        if ($joueur->moyNote() > 0) {
          $nb++;
          echo '<li class="detail_effectif_joueur"><b>' . $joueur->nom() . '</b> (' . $joueur->libelleEquipe() . ')';
          echo '<span class="float_right detail_effectif_joueur_prix">' . $joueur->prixAchat() . '</span>';
          echo '<span class="float_right detail_effectif_joueur_nb_match">' . $joueur->nbMatch() . '</span>';
          echo '<span class="float_right detail_effectif_joueur_moy">' . $joueur->moyNote() . '</span>';
          echo '</li>';
        } else {
          break;
        }
      }

      echo '</ul>';
      echo '</div>';
      if ($nb == 0) {
        echo '<p>Aucune note pour le moment.</p>';
      }
      echo '</div>';
    }

    foreach ($equipes as $cle => $value)
    {
      echo '<div id="statsEquipe' . $value->id(). '"';
      if ($value->id() != $equipe->id())
      {
        echo ' class="cache"';
      }
      echo '>';
      $joueurs = $tabEffectif[$value->id()];
      afficherStatsJoueurs($tabEffectif[$value->id()]);
      echo '</div>';
    }
    ?>
  </div>
  <div class="bloc_moy_ligue">
    <?php
      function afficherBlocTop($top, $libPoste, $equipes)
      {
        echo '<div class="detail_effectif">';
        echo '<div class="detail_effectif_titre">Top ' . $libPoste . ' - Ligue';
        echo '<span class="float_right detail_effectif_joueur_prix detail_effectif_joueur_caract_titre">Prix Achat</span>';
        echo '<span class="float_right detail_effectif_joueur_nb_match detail_effectif_joueur_caract_titre">Nb match</span>';
        echo '<span class="float_right detail_effectif_joueur_moy detail_effectif_joueur_caract_titre">Note moy.</span>';
        echo '</div>';
        echo '<div>';
        echo '<ul>';

        $nb = 0;
        foreach ($top as $cle => $joueur)
        {
          if ($joueur->moyNote() > 0) {
            $nb++;
            echo '<li class="detail_effectif_joueur"><b>' . $joueur->nom() . '</b> (' . $equipes[$joueur->idEquipe()]->nom() . ')';
            echo '<span class="float_right detail_effectif_joueur_prix">' . $joueur->prixAchat() . '</span>';
            echo '<span class="float_right detail_effectif_joueur_nb_match">' . $joueur->nbMatch() . '</span>';
            echo '<span class="float_right detail_effectif_joueur_moy">' . $joueur->moyNote() . '</span>';
            echo '</li>';
          } else {
            break;
          }
        }

        echo '</ul>';
        echo '</div>';
        if ($nb == 0) {
          echo '<p>Aucune note pour le moment.</p>';
        }
        echo '</div>';
      }

      afficherBlocTop($topGB, ConstantesAppli::GARDIEN_IHM, $equipes);
      afficherBlocTop($topDEF, ConstantesAppli::DEFENSEUR_IHM, $equipes);
      afficherBlocTop($topMIL, ConstantesAppli::MILIEU_IHM, $equipes);
      afficherBlocTop($topATT, ConstantesAppli::ATTAQUANT_IHM, $equipes);
    ?>
  </div>
  <div class="bloc_equipe_type">
    <p>A venir bloc équipe Type / Pipe ...</p>
  </div>
</section>
<section id="sectionEffectifs" class="cache">
  <div class="bloc_choix_effectif">
    <select name="effectifs" class="choix_effectif" onchange="javascript:afficherDivEffectif(this)">
      <?php
          foreach ($equipes as $cle => $value) {
            if($value->id() == $equipe->id())
            {
                echo '<option value="effectif' . $value->id() . '" selected="selected">' . $value->nom() . '</option>';
            }
            else
            {
                echo '<option value="effectif' . $value->id() . '">' . $value->nom() . '</option>';
            }
          }
       ?>
    </select>
  </div>
  <?php

    function afficherBlocJoueur($joueurs, $poste, $libPoste)
    {
        echo '<div class="detail_effectif">';
        echo '<div class="detail_effectif_titre">' . $libPoste;
        echo '<span class="float_right detail_effectif_joueur_tour_m detail_effectif_joueur_caract_titre">Tour mercato</span>';
        echo '<span class="float_right detail_effectif_joueur_prix detail_effectif_joueur_caract_titre">Prix Achat</span>';
        echo '</div>';
        echo '<div>';
        echo '<ul>';

        foreach ($joueurs as $cle => $value)
        {
          if ($value->position() == $poste) {
            echo '<li class="detail_effectif_joueur"><b>' . $value->nom() . '</b> (' . $value->libelleEquipe() . ')';
            echo '<span class="float_right detail_effectif_joueur_tour_m">' . $value->tourMercato() . '</span>';
            echo '<span class="float_right detail_effectif_joueur_prix">' . $value->prixAchat() . '</span>';
            echo '</li>';
          }
        }

        echo '</ul>';
        echo '</div>';
        echo '</div>';
    }

    foreach ($equipes as $value)
    {
      echo '<div id="effectif' . $value->id(). '"';
      if ($value->id() != $equipe->id())
      {
        echo ' class="cache"';
      }
      echo '>';
      $joueurs = $tabEffectif[$value->id()];
      afficherBlocJoueur($joueurs, ConstantesAppli::GARDIEN, ConstantesAppli::GARDIEN_IHM);
      afficherBlocJoueur($joueurs, ConstantesAppli::DEFENSEUR, ConstantesAppli::DEFENSEUR_IHM);
      afficherBlocJoueur($joueurs, ConstantesAppli::MILIEU, ConstantesAppli::MILIEU_IHM);
      afficherBlocJoueur($joueurs, ConstantesAppli::ATTAQUANT, ConstantesAppli::ATTAQUANT_IHM);
      echo '</div>';
    }
  ?>
</section>
<section id="sectionTrophees" class="cache">
  <?php

  function afficherBlocTrophee($idEquipe, $nomEquipe, $caracs)
  {
    echo '<div id="trophees' . $idEquipe . '" class="detail_trophee_equipe margin_auto">';
    echo '<div class="detail_trophee_equipe_lib">' . $nomEquipe . '</div>';
    foreach($caracs as $carac) {
        if ($carac->idEquipe() == $idEquipe) {
          $libLong = str_replace("%J",'<b>'.$carac->nom().'</b>',$carac->libelleCaricature());
          $libLong = str_replace("%T",'<b>'.$carac->total().'</b>',$libLong);

          echo '<div class="detail_trophee_equipe_trophee conteneurRow">';
          echo '<div class="width_130px conteneurColumn margin_0">';
          echo '<div class="detail_trophee_equipe_trophee_lib_court">' . $carac->libelleCourtCaricature() . '</div>';
          echo '<img src="web/img/caricature/' . $carac->code() . '.png" class="margin_auto"
            title="' . $carac->libelleCourtCaricature() . '" alt="" width="40px" height="40px"/>';
          echo '</div>';
          echo '<div class="detail_trophee_equipe_trophee_lib_long margin_auto">' . $libLong . '</div>';
          echo '</div>';
        }
    }
    echo '</div>';
  }

  if (isset($caracsEquipe) && sizeof($caracsEquipe) > 0) {
    echo '<div>';
    foreach($equipes as $value) {
      afficherBlocTrophee($value->id(), $value->nom(), $caracsEquipe);
    }
    echo '</div>';
  } else {
    echo '<div><br/>Ne soyez pas si hâtif.<br/>Les trophées seront distribués une fois la ligue terminée.</div>';
  }

  ?>
</section>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
