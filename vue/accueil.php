<?php
// entete
require_once("vue/commun/enteteflex.php");
?>
<div class="conteneurRow width_100pc">
  <section class="sectionAccueil borderRadiusGauche doubleBordureDroite">
    <h2>Déjà inscrit ?</h2>
    <?php
      if (isset($messageConn))
      {
        echo '<span class="erreur">' . $messageConn . '</span>';
      }
     ?>
    <p>Nom coach<br/>
        <input type="text" class="width_200px" name="nom" maxlength="40" value="<?php
            if(isset($_POST['nom']))
            {
              echo htmlspecialchars($_POST['nom']);
            }
          ?>" />
    </p>
    <p>Mot de passe<br/>
        <input type="password" class="width_200px" name="motDePasse" />
    </p>
    <input type="submit" value="J'ai mon diplôme" name="connexion" />
    <!--<input type="image" title="Essai" value="J'ai mon diplôme" style="background: transparent none; width=200px; height=20px;"
      src="./web/img/bouton.jpg" alt="Se connecter" />-->
  </section>
  <section class="sectionAccueil borderRadiusDroit">
    <h2>Nouveau coach ?</h2>
    <?php
      if (isset($messageInscr))
      {
        echo '<span class="erreur">' . $messageInscr . '</span>';
      }
     ?>
    <p>Mail<br/>
         <input type="email" class="width_200px" name="mailCrea" maxlength="50" value="<?php
             if(isset($_POST['mailCrea']))
             {
               echo htmlspecialchars($_POST['mailCrea']);
             }
           ?>" />
    </p>
    <p>Nom coach<br/>
        <input type="text" class="width_200px" name="nomCrea" maxlength="40" value="<?php
            if(isset($_POST['nomCrea']))
            {
              echo htmlspecialchars($_POST['nomCrea']);
            }
          ?>" />
    </p>
    <p>Mot de passe<br/>
        <input type="password" class="width_200px" name="motDePasseCrea" />
    </p>
    <p>Confirmation mot de passe<br/>
        <input type="password" class="width_200px" name="confirmMotDePasseCrea" />
    </p>
    <input type="submit" value="Je m'inscris" name="inscription" class="marginBottom" />
  </section>
</div>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
