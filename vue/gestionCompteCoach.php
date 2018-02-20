<?php
// entete
require_once("vue/commun/enteteflex.php");
?>
<section class="conteneurRow avecBordureInf">
  <div class="formulaire">
    <header>Mes informations</header>
    <?php
      if (isset($message))
      {
        echo '<span class="erreur">' . $message . '</span>';
      }
    ?>
    <p>Nom Coach<br/>
      <input type="text" class="width_200px" name="nom" maxlength="40" value="<?php
        echo $coach->nom();
        ?>" /></p>
    <p>Mail <br/>
      <input type="email" class="width_200px" name="mail" maxlength="50" value="<?php
        echo $coach->mail();
        ?>" /></p>
    <p>Code Postal <br/>
      <input type="text" class="width_200px" name="codePostal" maxlength="5" value="<?php
        echo $coach->codePostal();
        ?>" /></p>
    <p>Afficher mes ligues masquées : <input type="checkbox" name="affLigueMasquee" <?php
        if ($coach->affLigueMasquee() == 1){echo 'checked';}
        ?> /></p>
    <input type="submit" value="Mettre à jour" name="majCompte" class="marginBottom" />
  </div>
</section>
<section class="conteneurRow">
  <div class="formulaire">
    <header>Nouveau mot de passe ?</header>
    <?php
      if (isset($messageMdp))
      {
        echo '<span class="erreur">' . $messageMdp . '</span>';
      }
    ?>
    <p>Mot de passe actuel<br/>
      <input type="password" class="width_200px" name="motDePasseActuel" />
    </p>
    <p>Nouveau mot de passe<br/>
      <input type="password" class="width_200px" name="motDePasseCrea" />
    </p>
    <p>Confirmation mot de passe<br/>
      <input type="password" class="width_200px" name="confirmMotDePasseCrea" />
    </p>
    <input type="submit" value="Je suis ou ouf !" name="majMotDePasse" class="marginBottom" />
  </div>
</section>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
