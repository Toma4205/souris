<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<div class="sousTitre"><h3>Mes informations</h3></div>
  <p>Nom <br/>
    <input type="text" class="width_200px" name="nom" size="40" value="<?php
      echo $coach->nom();
    ?>" /></p>
  <p>Mail <br/>
    <input type="text" class="width_200px" name="mail" size="50" value="<?php
      echo $coach->mail();
    ?>" /></p>
  <p>Code Postal <br/>
    <input type="text" name="codePostal" size="5" value="<?php
      echo $coach->codePostal();
    ?>" /></p>
  <br/>
  <input type="submit" value="Mettre à jour" name="majCompte" class="marginBottom" />
  <br/>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
