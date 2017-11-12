<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
  <!-- ***************************************
  //   ***** DEBUT PARTIE CREATION LIGUE *****
  //   *************************************** -->
<fieldset>
    <legend>Paramètres de ligue</legend>
    <p>Nom <br/>
      <input type="text" class="width_200px" name="nom" size="40"
        value=<?php echo htmlspecialchars($creaLigue->nom());?> disabled/></p>
    <p>Pack Bonus/Malus <br/>
      <select name="bonusMalus" disabled><?php
            $arrayBonusMalus = [];
            $arrayBonusMalus[ConstantesAppli::BONUS_MALUS_AUCUN] = 'Aucun';
            $arrayBonusMalus[ConstantesAppli::BONUS_MALUS_CLASSIQUE] = 'Classique';
            $arrayBonusMalus[ConstantesAppli::BONUS_MALUS_FOLIE] = 'Folie';
            foreach ($arrayBonusMalus as $cle => $value)
            {
              if($creaLigue->bonusMalus() == $cle)
              {
                  echo '<option value="' . $cle . '" selected="selected">' . $value . '</option>';
              }
            }
         ?>
      </select>
    </p>
    <p>Mode expert <br/>
      <input type="checkbox" name="modeExpert" <?php
          if ($creaLigue->modeExpert() == 1)
          {
            echo 'checked';
          }?> disabled/>
    </p>
    <p>Mode mercato <br/>
      <select name="modeMercato" disabled><?php
            $arrayModeMercato = [];
            $arrayModeMercato[ConstantesAppli::MERCATO_ENCHERE] = 'Enchères';
            $arrayModeMercato[ConstantesAppli::MERCATO_DRAFT] = 'Draft';
            foreach ($arrayModeMercato as $cle => $value)
            {
              if($creaLigue->modeMercato() == $cle)
              {
                  echo '<option value="' . $cle . '" selected="selected">' . $value . '</option>';
              }
            }
         ?>
      </select>
    </p>
    <p>Un petit pari pour mettre du piquant ?<br/>
      <textarea name="libellePari" rows="5" cols="30" disabled><?php
           echo htmlspecialchars($creaLigue->libellePari());?></textarea>
    </p>
</fieldset>
<!-- ****************************************
//   ***** DEBUT PARTIE CREATION EQUIPE *****
//   **************************************** -->
<fieldset>
    <legend>Mon équipe</legend>
    <p>Nom *<br/>
      <input type="text" class="width_200px" name="nomEquipe" size="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->nom());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Ville *<br/>
      <input type="text" class="width_200px" name="villeEquipe" size="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->ville());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Stade *<br/>
      <input type="text" class="width_200px" name="stadeEquipe" size="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->stade());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <?php
      // Création équipe non validée
      if (!isset($equipe) || null == $equipe->id())
      {
        echo '<input type="submit" value="Créer" name="creationEquipe" />';
      }
    ?>
</fieldset>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
