<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon super site</title>
    </head>

    <body>

    <!-- L'en-tête -->
    <?php include("commun/entete.php"); ?>

    <!-- Le menu -->
    <?php include("commun/menu.php"); ?>

    <!-- Le corps -->
    <form method="post" action="cible.php">

      <div id="corps">
          <h1>Connexion</h1>

          <!-- MEMO : types d'input d'un formulaire -->
          <p>Nom coach : <input type="text" name="nom" /></p>
          <p>Mot de passe : <input type="password" name="mot_de_passe" /></p>
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

          <br/>
          <input type="submit" value="Valider" />
      </div>

    </form>

    <!-- Le pied de page -->
    <?php include("commun/pied_de_page.php"); ?>

    </body>
</html>
