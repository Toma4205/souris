<?php
// entete
$vueJs = 'mercato.js';
require_once("vue/commun/enteteflex.php");
?>
<section class="avecBordureInf">
  <p><span class="libBudgetRestant">Budget restant (M€) : </span><span class="budgetRestant"><output id="budgetRestant" name="budgetRestant"><?php echo $budgetRestant; ?></output></span>
      <input type="submit" id="validationMercato" value="Valider mes offres" name="validationMercato" />
      <input type="submit" id="reinitialisation" value="Réinitialiser" name="reinitialisation" />
  </p>
</section>
<?php
require_once("vue/commun/tableMercato.php");

// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
