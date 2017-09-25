<?php
  // Ouverture de la session
  session_start();

  // Si l'URL contient le paramètre 'deconnexion'
  if (isset($_GET['deconnexion']) AND $_GET['deconnexion'] == 'true')
  {
      // Si un nom est stocké => on détruit la session
      if (isset($_SESSION['nom']))
      {
        session_destroy();
        $_SESSION['nom'] = NULL;
        $_SESSION['nom_crea'] = NULL;
        echo 'Session détruite !';
      }
      // Sinon, la session n'a pas été initialisée => on ne fait rien
      else {
        echo 'Session : ' . session_id() . ', statut=' . session_status();
      }
  }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon super site</title>
    </head>

    <body>

    <!-- L'en-tête -->
    <?php include("commun/entete.php"); ?>

    <?php
      if (isset($_GET['erreur']))
      {
        switch ($_GET['erreur']) {
          case 'connexion':
            echo '<p><strong>Couple nom/mot de passe invalide !</strong></p>';
            break;
          case 'inscription':
            echo '<p><strong>Un coach utilise déjà ce nom !</strong></p>';
            break;
          case 'champs':
            echo '<p><strong>Nom et mot de passe obligatoires !</strong></p>';
            break;
        }
      }
    ?>

    <!-- Le menu -->
    <?php // include("commun/menu.php"); ?>

    <!-- Le corps -->
    <div id="corps">
      <form method="post" action="cible.php?action=connexion">
          <h1>Déjà inscrit ?</h1>
          <p>Nom coach : <input type="text" name="nom" value="<?php
              if(isset($_SESSION['nom']))
              {
                echo htmlspecialchars($_SESSION['nom']);
              }
            ?>" size="40" /></p>
          <p>Mot de passe : <input type="password" name="mot_de_passe" /></p>
          <br/>
          <input type="submit" value="Se connecter" />
        </form>

        <form method="post" action="cible.php?action=inscription">
          <h1>Nouveau coach ?</h1>
          <p>Nom coach : <input type="text" name="nom_crea" value="<?php
              if(isset($_SESSION['nom_crea']))
              {
                echo htmlspecialchars($_SESSION['nom_crea']);
              }
            ?>" size="40" /></p>
          <p>Mot de passe : <input type="password" name="mot_de_passe_crea" /></p>
          <p>Confirmation mot de passe : <input type="password" name="confirm_mot_de_passe_crea" /></p>

          <br/>
          <input type="submit" value="S'inscrire" />
        </form>
          <!-- MEMO : types d'input d'un formulaire
          <p>Code postal :
            <select name="code_postal">
              <option value=""></option>
              <option value="44000">Nantes</option>
              <option value="44117">St André des Eaux</option>
              <option value="44800">St Herblain</option>
            </select></p>
          <p>Equipes supportées : <br/>
            <input type="checkbox" name="equipe1" id="equipe1" /> <label for="equipe1">PSG</label><br/>
            <input type="checkbox" name="equipe2" id="equipe2" /> <label for="equipe2">Nantes</label><br/>
            <input type="checkbox" name="equipe3" id="equipe3" /> <label for="equipe3">ASSE</label>
          </p>
          <p>Vous êtes un(e) : <br/>
            <input type="radio" name="sexe" value="H" id="H" checked="checked" /> <label for="H">Homme</label>
            <input type="radio" name="sexe" value="F" id="F" /> <label for="F">Femme</label>
          </p>
          <p>Commentaire : <textarea name="comentaire" rows="8" cols="45"></textarea></p>

          <input type="hidden" name="info_cachee" value="toto" />
          -->
      </div>

    <!-- Le pied de page -->
    <?php include("commun/pied_de_page.php"); ?>

    </body>
</html>
