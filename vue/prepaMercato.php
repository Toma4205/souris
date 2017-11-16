<?php
// entete
$vueJs = 'mercato.js';
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<div class="sousTitre"><h3>Mercato</h3></div>
  <p><span class="libBudgetRestant">Budget restant (M€) : </span><span class="budgetRestant"><output id="budgetRestant" name="budgetRestant"><?php echo $budgetRestant; ?></output></span>
      <input type="submit" id="validationMercato" value="Valider mes offres" name="validationMercato" />
      <input type="submit" id="reinitialisation" value="Réinitialiser" name="reinitialisation" />
  </p>
  <br/>
  <?php require_once("vue/commun/tableMercato.php");?>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
