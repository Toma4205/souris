<?php
// entete
$vueJs = 'classement.js';
require_once("vue/commun/enteteflex.php");
?>
<header class="menuClassement">
  <p id="choixClassement" class="bold" onclick="javascript:afficherSection(this, 'sectionClassement');">Classement</p>
  <p id="choixButeurs" onclick="javascript:afficherSection(this, 'sectionButeurs');">Buteurs</p>
  <p id="choixJoueurs" onclick="javascript:afficherSection(this, 'sectionJoueurs');">Joueurs</p>
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
</section>
<section id="sectionButeurs" class="cache">
  <div>
    <?php
    if (isset($buteurs) && count($buteurs) > 0)
    {
    ?>
    <table class="tableBase">
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
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
