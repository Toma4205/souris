<?php
// entete
$vueJs = 'mercato.js';
require_once("vue/commun/enteteflex.php");
?>
<section>
    <?php
    if (!isset($_SESSION[ConstantesSession::EQUIPE_CREA])) {
      echo '<p>Merci de créer votre équipe afin de pouvoir effectuer votre mercato.</p>';
    } elseif ($equipe->finMercato() == TRUE) {
      echo '<p>Mercato fermé.</p>';
      require_once("vue/commun/tableAchatJoueur.php");
    } elseif ($tourValide == TRUE) {
      echo '<p>Tour mercato validé. En attente des autres coachs...</p>';
      require_once("vue/commun/tableAchatJoueur.php");
    } else {
    ?>
    <p><span class="libBudgetRestant">Budget restant (M€) : </span><span class="budgetRestant"><output id="budgetRestant" name="budgetRestant"><?php echo $budgetRestant; ?></output></span>
        <input type="submit" id="validationMercato" value="Valider mes offres" name="validationMercato" />
        <?php
          if ($tourMercato > 1)
          {
            echo '<input type="submit" id="clotureMercato" value="Fermer mon mercato" name="clotureMercato" />';
          }
         ?>
    </p>
    <br/>
    <?php
    require_once("vue/commun/tableMercato.php");
    }
    ?>
</section>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
