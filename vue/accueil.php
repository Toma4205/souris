<?php
// entete
require_once("vue/commun/entete.php");
?>
    <p>Nombre de coachs : <?= $manager->count() ?></p>

    <form action="" method="post">
      <h1>Déjà inscrit ?</h1>
      <p>Nom coach : <input type="text" name="nom" size="40" value="<?php
          if(isset($_POST['nom']))
          {
            echo htmlspecialchars($_POST['nom']);
          }
        ?>" /></p>
      <p>Mot de passe : <input type="password" name="motDePasse" /></p>
      <br/>
      <input type="submit" value="Se connecter" name="connexion" />
      <br/>
      <h1>Nouveau coach ?</h1>
      <p>Nom coach : <input type="text" name="nomCrea" size="40" value="<?php
          if(isset($_POST['nomCrea']))
          {
            echo htmlspecialchars($_POST['nomCrea']);
          }
        ?>" /></p>
      <p>Mot de passe : <input type="password" name="motDePasseCrea" /></p>
      <p>Confirmation mot de passe : <input type="password" name="confirmMotDePasseCrea" /></p>
      <br/>
      <input type="submit" value="S'inscrire" name="inscription" />
    </form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
