<?php
// entete
require_once("vue/commun/entete.php");
?>
<form action="" method="post">
<fieldset>
  <legend>Mercato</legend>
  <p>Budget restant : <output id="budgetRestant" name="budgetRestant"><?php echo $budgetRestant; ?></output>
      <img id="imageBudget" src="./web/img/validation.jpg" alt="Logo du site" width="20px" height="20px" />
      <input type="submit" id="validationMercato" value="Valider mes offres" name="validationMercato" />
      <input type="submit" id="reinitialisation" value="Réinitialiser" name="reinitialisation" />
  </p>
  <br/>
  <fieldset>
    <legend>Gardiens (2 min.)  <img id="imageGB" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></legend>
    <?php
      if (isset($joueursReelsGB))
      {
     ?>
     <div class="colonnes">
       <div class="colonne" style="width:50%;">
     <table class="display" id="tableMercatoGB">
       <thead>
         <tr>
           <th>Nom</th>
           <th>Prénom</th>
           <th>Equipe</th>
           <th>Prix</th>
         </tr>
       </thead>
       <tbody>
         <?php
         foreach($joueursReelsGB as $value)
         {
           echo '<tr id="' . $value->prenomNom() . '"><td>' . $value->nom() . '</td>';
           echo '<td>' . $value->prenom() . '</td>';
           echo '<td>' . $value->equipe() . '</td>';
           echo '<td>' . $value->prix() . '</td>';
           echo '</tr>';
         }
         echo '</tbody></table>';
       }
       else
       {
         echo '<br/>';
         echo 'Aucun gardien en base !!!';
       }
         ?>
    </div>
    <div class="colonne" style="width:50%;">
      <table class="tableBase" id="tableMercatoGBAchat">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Equipe</th>
            <th>Prix origine</th>
            <th>Prix</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach($joueursPrepaMercato as $value)
          {
            // Attention, si changement => effectuer aussi dans mercato.js
            if ($value->position() == ConstantesAppli::GARDIEN)
            {
              echo '<tr id="Achat_' . $value->id() . '">';
              echo '<td>' . $value->nom() . '</td>';
              echo '<td>' . $value->prenom() . '</td>';
              echo '<td>' . $value->equipe() . '</td>';
              echo '<td>' . $value->prixOrigine() . '</td>';
              echo '<td><input type="text" name="name_' . $value->id() . '" value="' . $value->prixAchat() . '" onchange="javascript:recalculerBudgetRestant();"/></td>';
              echo '<td><img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" onclick="javascript:supprimerAchatJoueur(\'' . $value->id() . '\', \'tableMercatoGB\');" /></td>';
              echo '</tr>';
            }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</fieldset>
<fieldset>
  <legend>Défenseurs (6 min.)  <img id="imageDEF" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></legend>
  <?php
    if (isset($joueursReelsDEF))
    {
   ?>
   <div class="colonnes">
     <div class="colonne" style="width:50%;">
   <table class="display" id="tableMercatoDEF">
     <thead>
       <tr>
         <th>Nom</th>
         <th>Prénom</th>
         <th>Equipe</th>
         <th>Prix</th>
       </tr>
     </thead>
     <tbody>
       <?php
       foreach($joueursReelsDEF as $value)
       {
         echo '<tr id="' . $value->prenomNom() . '"><td>' . $value->nom() . '</td>';
         echo '<td>' . $value->prenom() . '</td>';
         echo '<td>' . $value->equipe() . '</td>';
         echo '<td>' . $value->prix() . '</td>';
         echo '</tr>';
       }
       echo '</tbody></table>';
     }
     else
     {
       echo '<br/>';
       echo 'Aucun défenseur en base !!!';
     }
       ?>
  </div>
  <div class="colonne" style="width:50%;">
    <table class="tableBase" id="tableMercatoDEFAchat">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Equipe</th>
          <th>Prix origine</th>
          <th>Prix</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach($joueursPrepaMercato as $value)
        {
          // Attention, si changement => effectuer aussi dans mercato.js
          if ($value->position() == ConstantesAppli::DEFENSEUR)
          {
            echo '<tr id="Achat_' . $value->id() . '">';
            echo '<td>' . $value->nom() . '</td>';
            echo '<td>' . $value->prenom() . '</td>';
            echo '<td>' . $value->equipe() . '</td>';
            echo '<td>' . $value->prixOrigine() . '</td>';
            echo '<td><input type="text" name="name_' . $value->id() . '" value="' . $value->prixAchat() . '" onchange="javascript:recalculerBudgetRestant();"/></td>';
            echo '<td><img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" onclick="javascript:supprimerAchatJoueur(\'' . $value->id() . '\', \'tableMercatoDEF\');" /></td>';
            echo '</tr>';
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
</fieldset>
<fieldset>
  <legend>Milieux (6 min.)  <img id="imageMIL" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></legend>
  <?php
    if (isset($joueursReelsMIL))
    {
   ?>
   <div class="colonnes">
     <div class="colonne" style="width:50%;">
   <table class="display" id="tableMercatoMIL">
     <thead>
       <tr>
         <th>Nom</th>
         <th>Prénom</th>
         <th>Equipe</th>
         <th>Prix</th>
       </tr>
     </thead>
     <tbody>
       <?php
       foreach($joueursReelsMIL as $value)
       {
         echo '<tr id="' . $value->prenomNom() . '"><td>' . $value->nom() . '</td>';
         echo '<td>' . $value->prenom() . '</td>';
         echo '<td>' . $value->equipe() . '</td>';
         echo '<td>' . $value->prix() . '</td>';
         echo '</tr>';
       }
       echo '</tbody></table>';
     }
     else
     {
       echo '<br/>';
       echo 'Aucun milieu en base !!!';
     }
       ?>
  </div>
  <div class="colonne" style="width:50%;">
    <table class="tableBase" id="tableMercatoMILAchat">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Equipe</th>
          <th>Prix origine</th>
          <th>Prix</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach($joueursPrepaMercato as $value)
        {
          // Attention, si changement => effectuer aussi dans mercato.js
          if ($value->position() == ConstantesAppli::MILIEU)
          {
            echo '<tr id="Achat_' . $value->id() . '">';
            echo '<td>' . $value->nom() . '</td>';
            echo '<td>' . $value->prenom() . '</td>';
            echo '<td>' . $value->equipe() . '</td>';
            echo '<td>' . $value->prixOrigine() . '</td>';
            echo '<td><input type="text" name="name_' . $value->id() . '" value="' . $value->prixAchat() . '" onchange="javascript:recalculerBudgetRestant();"/></td>';
            echo '<td><img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" onclick="javascript:supprimerAchatJoueur(\'' . $value->id() . '\', \'tableMercatoMIL\');" /></td>';
            echo '</tr>';
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
</fieldset>
<fieldset>
  <legend>Attaquants (3 min.)  <img id="imageATT" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></legend>
  <?php
    if (isset($joueursReelsATT))
    {
   ?>
   <div class="colonnes">
     <div class="colonne" style="width:50%;">
   <table class="display" id="tableMercatoATT">
     <thead>
       <tr>
         <th>Nom</th>
         <th>Prénom</th>
         <th>Equipe</th>
         <th>Prix</th>
       </tr>
     </thead>
     <tbody>
       <?php
       foreach($joueursReelsATT as $value)
       {
         echo '<tr id="' . $value->prenomNom() . '"><td>' . $value->nom() . '</td>';
         echo '<td>' . $value->prenom() . '</td>';
         echo '<td>' . $value->equipe() . '</td>';
         echo '<td>' . $value->prix() . '</td>';
         echo '</tr>';
       }
       echo '</tbody></table>';
     }
     else
     {
       echo '<br/>';
       echo 'Aucun attaquant en base !!!';
     }
       ?>
  </div>
  <div class="colonne" style="width:50%;">
    <table class="tableBase" id="tableMercatoATTAchat">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Equipe</th>
          <th>Prix origine</th>
          <th>Prix</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach($joueursPrepaMercato as $value)
        {
          // Attention, si changement => effectuer aussi dans mercato.js
          if ($value->position() == ConstantesAppli::ATTAQUANT)
          {
            echo '<tr id="Achat_' . $value->id() . '">';
            echo '<td>' . $value->nom() . '</td>';
            echo '<td>' . $value->prenom() . '</td>';
            echo '<td>' . $value->equipe() . '</td>';
            echo '<td>' . $value->prixOrigine() . '</td>';
            echo '<td><input type="text" name="name_' . $value->id() . '" value="' . $value->prixAchat() . '" onchange="javascript:recalculerBudgetRestant();"/></td>';
            echo '<td><img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" onclick="javascript:supprimerAchatJoueur(\'' . $value->id() . '\', \'tableMercatoATT\');" /></td>';
            echo '</tr>';
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
</fieldset>
</fieldset>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
