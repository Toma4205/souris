<?php
// entete
$vueCss = 'creationEquipe.css';
$vueJs = 'creationEquipe.js';

require_once("vue/commun/enteteflex.php");
?>
  <!-- ***************************************
  //   ***** DEBUT PARTIE CREATION LIGUE *****
  //   *************************************** -->
<section class="conteneurRow avecBordureInf">
  <div class="formulaire">
    <header>Paramètres de ligue</header>
    <?php
      if (isset($messageEquipe))
      {
        echo '<span class="erreur">' . $messageEquipe . '</span>';
      }
    ?>
    <p>Nom <br/>
      <input type="text" class="width_200px" name="nom" maxlength="40"
        value="<?php echo htmlspecialchars($creaLigue->nom());?>" disabled/></p>
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
  </div>
</section>
<!-- ****************************************
//   ***** DEBUT PARTIE CREATION EQUIPE *****
//   **************************************** -->
<section class="conteneurRow">
  <div class="formulaire">
    <header>Mon équipe</header>
    <p>Nom *<br/>
      <input type="text" class="width_200px" name="nomEquipe" maxlength="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->nom());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Ville *<br/>
      <input type="text" class="width_200px" name="villeEquipe" maxlength="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->ville());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Stade *<br/>
      <input type="text" class="width_200px" name="stadeEquipe" maxlength="30" value=<?php
        echo '"';
        if(isset($equipe))
        {
          echo htmlspecialchars($equipe->stade());
        }
        echo '"', (isset($equipe) && null != $equipe->id() ? ' disabled' : ' enabled');?>/></p>
    <p>Quel style de coach es-tu ? *</p>
    <input type="hidden" id="codeStyleCoach" name="codeStyleCoach" />
    <?php
      if (isset($equipe) && null != $equipe->id())
      {
        foreach ($nomenclStyleCoach as $key => $value) {
          if ($value->code() == $equipe->codeStyleCoach()) {
            echo '<div>';
            echo '<div class="crea_equipe_coach_titre">' . $value->libelle() . '</div>';
            echo '<img src="web/img/coach/' . $value->nomImage() . '" title="' . $value->description() . '" alt="' . $value->libelle() . '" width="60px" height="60px"/>';
            echo '</div>';
            break;
          }
        }
      }
      else if (isset($nomenclStyleCoach))
      {
        $index = 0;
        foreach ($nomenclStyleCoach as $key => $value) {
            if ($index == 0) {
              echo '<div class="conteneurRow crea_equipe_coach">';
            } else if ($index % 4 == 0) {
              echo '</div>';
              echo '<div class="conteneurRow crea_equipe_coach">';
            }
            echo '<div class="crea_equipe_coach_style" id="' . $value->code() . '" onclick="javascript:selectStyleCoach(\'' . $value->code() . '\')">';
            echo '<div class="crea_equipe_coach_titre">' . $value->libelle() . '</div>';
            echo '<img src="web/img/coach/' . $value->nomImage() . '" title="' . $value->description() . '" alt="' . $value->libelle() . '" width="60px" height="60px"/>';
            echo '</div>';
            $index++;
        }
        echo '</div>';
      }
      else
      {
        echo '<div>Aucune nomenclature trouvée. Veuillez nous contacter.</div>';
      }
     ?>
    <?php
      // Création équipe non validée
      if (!isset($equipe) || null == $equipe->id())
      {
        echo '<input type="submit" value="Créer" name="creationEquipe" class="marginBottom" />';
      }
    ?>
  </div>
</section>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
