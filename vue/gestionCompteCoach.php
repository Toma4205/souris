<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<fieldset>
  <legend>Mes informations</legend>
  <p>Nom         : <input type="text" name="nom" size="40" value="<?php
      echo $coach->nom();
    ?>" /></p>
  <p>Mail        : <input type="text" name="mail" size="50" value="<?php
      echo $coach->mail();
    ?>" /></p>
  <p>Code Postal : <input type="text" name="codePostal" size="5" value="<?php
      echo $coach->codePostal();
    ?>" /></p>
  <br/>
  <input type="submit" value="Mettre à jour" name="majCompte" />
</fieldset>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
