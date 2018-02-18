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
          echo '<tr><td>' . $index . ' . ' . $value->nom() . '</td>';
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
          <th>€ / B.</th>
          <th>Match</th>
          <th>B. / Match</th>
          <th>Tour mercato</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $index = 1;
        foreach($buteurs as $value)
        {
          echo '<tr><td>' . $index . ' . ' . $value->nom() . ' ' . $value->prenom() . '</td>';
          echo '<td>' . $value->nomEquipe() . '</td>';
          echo '<td>' . $value->totalBut() . ' (' . $value->nbButReel() . '+' . $value->nbButVirtuel() . ')</td>';
          echo '<td>' . $value->prixAchat() . '</td>';
          echo '<td>' . round(($value->prixAchat() / $value->totalBut()), 2) . '</td>';
          echo '<td>' . $value->nbMatch() . '</td>';
          echo '<td>' . round(($value->totalBut() / $value->nbMatch()), 2) . '</td>';
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
    <p>A venir Joueurs ...</p>
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
            echo '<li class="detail_effectif_joueur">' . $value->nom() . ' ' . $value->prenom() . ' (' . $value->libelleEquipe() . ')';
            echo '<span class="float_right detail_effectif_joueur_tour_m">' . $value->tourMercato() . '</span>';
            echo '<span class="float_right detail_effectif_joueur_prix">' . $value->prixAchat() . '</span>';
            echo '</li>';
          }
        }

        echo '</ul>';
        echo '</div>';
        echo '</div>';
    }

    foreach ($equipes as $cle => $value)
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
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
