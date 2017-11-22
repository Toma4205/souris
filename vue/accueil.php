<?php
// entete
require_once("vue/commun/enteteflex.php");
?>
<section class="sectionAccueil borderRadiusGauche doubleBordureDroite">
    <h2>Déjà inscrit ?</h2>
    <p>Nom coach<br/>
        <input type="text" class="width_200px" name="nom" size="40" value="<?php
            if(isset($_POST['nom']))
            {
              echo htmlspecialchars($_POST['nom']);
            }
          ?>" />
    </p>
    <p>Mot de passe<br/>
        <input type="password" class="width_200px" name="motDePasse" />
    </p>
    <input type="submit" value="Se connecter" name="connexion" />
</section>
<section class="sectionAccueil borderRadiusDroit">
    <h2>Nouveau coach ?</h2>
    <p>Nom coach<br/>
        <input type="text" class="width_200px" name="nomCrea" size="40" value="<?php
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
    <input type="submit" value="S'inscrire" name="inscription" class="marginBottom" />
</section>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
