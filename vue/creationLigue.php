<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<fieldset>
    <legend>Paramètres de ligue</legend>
    <p>Nom <br/>
      <input type="text" class="width_200px" name="nom" size="40" value=<?php
        echo '"';
        if(isset($_POST['nom']))
        {
          echo htmlspecialchars($_POST['nom']);
        }
        echo '"', (isset($creaLigue) ? ' disabled' : ' enabled');?>/></p>
    <p>Nombre d'équipes <br/>
      <select name="nbEquipe" <?php
            if (isset($creaLigue))
            {
              echo ' disabled>';
            }
            else
            {
              echo '>';
            }
            $arrayNbEquipe = [2,3,4,5,6,7,8,9,10];
            foreach ($arrayNbEquipe as $value)
            {
              if((isset($_POST['nbEquipe']) && $_POST['nbEquipe'] == $value)
                || (!isset($_POST['nbEquipe']) && $value == 2))
              {
                  echo '<option value="' . $value . '" selected="selected">' . $value . '</option>';
              }
              else
              {
                  echo '<option value="' . $value . '">' . $value . '</option>';
              }
            }
         ?>
      </select>
    </p>
    <p>Mode expert <br/>
      <input type="checkbox" name="modeExpert" <?php
          if (isset($_POST['modeExpert']))
          {
            echo 'checked';
          }
          echo ' ', (isset($creaLigue) ? ' disabled' : ' enabled');?>/>
      <span class="italic"> cf réglement</span>
    </p>
    <p>Un petit pari pour mettre du piquant ?<br/>
      <textarea name="libellePari" rows="5" cols="30" <?php
          echo '"', (isset($creaLigue) ? ' disabled' : ' enabled');?>><?php
           if(isset($_POST['libellePari']))
           {
             echo htmlspecialchars($_POST['libellePari']);
           }?></textarea>
    </p>
    <?php
      // Création ligue non validée
      if (!isset($creaLigue))
      {
        echo '<input type="submit" value="Créer" name="creation" />';
      }
    ?>
</fieldset>
<?php
  // Création ligue validée => envoi demande aux confrères
  if (isset($creaLigue))
  {
?>
<fieldset>
    <legend>Inviter des confrères</legend>
    A venir...
</fieldset>
<?php
  }
?>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
