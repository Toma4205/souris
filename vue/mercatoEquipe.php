<?php
// entete
$vueJs = 'mercato.js';
$vueCss = 'mercatoEquipe.css';
require_once("vue/commun/enteteflex.php");

function afficherEquipeEnAttenteMercato($equipes, $lib)
{
  echo '<div class="equipe_attente_mercato">';
  echo '<div class="equipe_attente_mercato_lib">' . $lib . '</div>';
  echo '<ul>';

  foreach ($equipes as $e)
  {
    echo '<li class="detail_equipe_attente_mercato">';
    if ($e->nom() != null) {
      echo '<b>' . $e->nomCoach() . '</b> doit valider ses enchères.';
    }
    else {
      echo '<b>' . $e->nomCoach() . '</b> doit encore créer son équipe.';
    }
    echo '</li>';
  }

  echo '</ul>';
  echo '</div>';
}

if (!isset($_SESSION[ConstantesSession::EQUIPE_CREA])) {
    echo '<p>Merci de créer votre équipe afin de pouvoir effectuer votre mercato.</p>';
} elseif ($equipe->finMercato() == TRUE) {
    afficherEquipeEnAttenteMercato($equipesEnAttente, 'Mercato fermé. En attente des autres coachs :');
    echo '<section class="conteneurRow"><div>';
    require_once("vue/commun/tableAchatJoueur.php");
    echo '</div></section>';
} elseif ($tourValide == TRUE) {
    afficherEquipeEnAttenteMercato($equipesEnAttente, 'Tour mercato validé. En attente des autres coachs :');
    echo '<section class="conteneurRow"><div>';
    require_once("vue/commun/tableAchatJoueur.php");
    echo '</div></section>';
} else {
?>
  <section class="avecBordureInf">
  <p><span class="libBudgetRestant">Budget restant (M€) : </span><span class="budgetRestant"><output id="budgetRestant" name="budgetRestant"><?php echo $equipe->budgetRestant(); ?></output></span>
        <input type="submit" id="validationMercato" value="Valider mes offres" name="validationMercato" />
        <?php
          if ($tourMercato > 1)
          {
            echo '<input type="submit" id="clotureMercato" value="Fermer mon mercato"
              name="clotureMercato" onclick="return confirmerFermerMercato();"/>';
          }
         ?>
  </p>
  </section>
<?php
  require_once("vue/commun/tableMercato.php");
}

// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
