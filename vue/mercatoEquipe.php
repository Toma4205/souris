<?php
// entete
require_once("vue/commun/entete.php");
?>
<fieldset>
    <?php
    if (!isset($_SESSION[ConstantesSession::EQUIPE_CREA]))
    {
      echo '<p>Merci de créer votre équipe afin de pouvoir effectuer votre mercato.</p>';
    }
    ?>
</fieldset>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
