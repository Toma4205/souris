<?php
// entete
require_once("vue/commun/enteteflex.php");
?>
<div class="conteneurRow width_100pc">
  <section class="sectionAccueil borderRadiusGauche doubleBordureDroite">
    <header>Déjà inscrit ?</header>
    <div class="width_200px margin_auto">
    <?php
      if (isset($messageConn))
      {
        echo '<span class="erreur">' . $messageConn . '</span>';
      }
     ?>
    <div class="bloc_formulaire">
        <div class="titre_formulaire">Mail</div>
        <input type="email" name="mail" maxlength="50" class="width_100pc" value="<?php
            if(isset($_POST['mail']))
            {
              echo htmlspecialchars($_POST['mail']);
            }
          ?>" />
    </div>
    <div class="bloc_formulaire">
        <div class="titre_formulaire">Mot de passe</div>
        <input type="password" class="width_100pc" name="motDePasse" />
    </div>
    <input type="submit" value="J'ai mon diplôme" name="connexion" />
    <!--<input type="image" title="Essai" value="J'ai mon diplôme" style="background: transparent none; width=200px; height=20px;"
      src="./web/img/bouton.jpg" alt="Se connecter" />-->
    </div>
  </section>
  <section class="sectionAccueil borderRadiusDroit">
    <header>Nouveau coach ?</header>
    <div class="width_200px margin_auto">
    <?php
      if (isset($messageInscr))
      {
        echo '<span class="erreur">' . $messageInscr . '</span>';
      }
     ?>
    <div class="bloc_formulaire">
        <div class="titre_formulaire">Mail</div>
        <input type="email" class="width_100pc" name="mailCrea" maxlength="50" value="<?php
             if(isset($_POST['mailCrea']))
             {
               echo htmlspecialchars($_POST['mailCrea']);
             }
           ?>" />
    </div>
    <div class="bloc_formulaire">
        <div class="titre_formulaire">Nom coach</div>
        <input type="text" class="width_100pc" name="nomCrea" maxlength="40" value="<?php
            if(isset($_POST['nomCrea']))
            {
              echo htmlspecialchars($_POST['nomCrea']);
            }
          ?>" />
    </div>
    <div class="bloc_formulaire">
        <div class="titre_formulaire">Mot de passe</div>
        <input type="password" class="width_100pc" name="motDePasseCrea" />
    </div>
     <div class="bloc_formulaire">
        <div class="titre_formulaire">Confirmation mot de passe</div>
        <input type="password" class="width_100pc" name="confirmMotDePasseCrea" />
    </div>
    <input type="submit" value="Je m'inscris" name="inscription" class="marginBottom" />
    </div>
  </section>
</div>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
