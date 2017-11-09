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
    <legend>Joueurs</legend>
    <?php
      if (isset($joueursReels))
      {
     ?>
     <div class="colonnes">
       <div class="colonne" style="width:50%;">
     <table class="display" id="tableMercato">
       <thead>
         <tr>
           <th>Nom</th>
           <th>Prénom</th>
           <th>Poste</th>
           <th>Equipe</th>
           <th>Prix</th>
         </tr>
       </thead>
       <tbody>
         <?php
         foreach($joueursReels as $value)
         {
           echo '<tr id="' . $value->id() . '"><td>' . $value->nom() . '</td>';
           echo '<td>' . $value->prenom() . '</td>';
           echo '<td>' . $value->positionIHM() . '</td>';
           echo '<td>' . $value->libelleEquipe() . '</td>';
           echo '<td>' . $value->prix() . '</td>';
           echo '</tr>';
         }
         echo '</tbody></table>';
       }
       else
       {
         echo '<br/>';
         echo 'Aucun joueur en base !!!';
       }
         ?>
    </div>
    <div class="colonne" style="width:50%;">
      <p>Gardiens (2 min.) <img id="imageGB" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></p>
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
      <p>Défenseurs (6 min.) <img id="imageDEF" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></p>
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
      <p>Milieux (6 min.) <img id="imageMIL" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></p>
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
      <p>Attaquant (3 min.) <img id="imageATT" src="./web/img/erreur.jpg" alt="Logo du site" width="20px" height="20px" /></p>
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
