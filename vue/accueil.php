<?php
// entete
require_once("vue/commun/entete.php");
?>
  <p>Nombre de coachs : <?= $manager->count() ?></p>
  <div class="colonnes">
    <form action="" method="post">
      <div class="colonne">
        <h2>Déjà inscrit ?</h2>
        <p>Nom coach : </p>
        <p><input type="text" name="nom" size="40" value="<?php
            if(isset($_POST['nom']))
            {
              echo htmlspecialchars($_POST['nom']);
            }
          ?>" /></p>
        <p>Mot de passe : </p>
        <p><input type="password" name="motDePasse" /></p>
        <br/>
        <input type="submit" value="Se connecter" name="connexion" />
      </div>
      <div class="colonne">
        <h2>Nouveau coach ?</h2>
        <p>Nom coach : </p>
        <p><input type="text" name="nomCrea" size="40" value="<?php
            if(isset($_POST['nomCrea']))
            {
              echo htmlspecialchars($_POST['nomCrea']);
            }
          ?>" /></p>
        <p>Mot de passe : </p>
        <p><input type="password" name="motDePasseCrea" /></p>
        <p>Confirmation mot de passe : </p>
        <p><input type="password" name="confirmMotDePasseCrea" /></p>
        <br/>
        <input type="submit" value="S'inscrire" name="inscription" />
      </div>
    </form>
  </div>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
