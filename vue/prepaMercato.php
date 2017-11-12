<?php
// entete
require_once("vue/commun/entete.php");

// Affichage des joueurs enregistrés en BDD
function afficherJoueurDejaAchete($joueursPrepaMercato, $position)
{
  foreach($joueursPrepaMercato as $value)
  {
    // Attention, si changement => effectuer aussi dans mercato.js
    if ($value->position() == $position)
    {
      echo '<p id="Achat_' . $value->id() . '" class="joueurAchete">';
      echo '<b>' . $value->nom() . ' ' . $value->prenom() . '</b> - ' . $value->libelleEquipe() . ' (prix = ' . $value->prixOrigine() . ')';
      echo '<span class="floatRight">';
      echo '<input type="text" class="inputPrix" name="name_' . $value->id() . '" value="' . $value->prixAchat() . '" onchange="javascript:recalculerBudgetRestant();"/>';
      echo ' <img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" onclick="javascript:supprimerAchatJoueur(\'' . $value->id() . '\');" />';
      echo '</span></p>';
    }
  }
}
?>
<form action="" method="post">
<fieldset>
  <legend>Mercato</legend>
  <p>Budget restant : <output id="budgetRestant" name="budgetRestant"><?php echo $budgetRestant; ?></output> M€ (T'as jamais été aussi riche)
      <img id="imageBudget" src="./web/img/validation.jpg" width="20px" height="20px" />
      <input type="submit" id="validationMercato" value="Valider mes offres" name="validationMercato" />
      <input type="submit" id="reinitialisation" value="Réinitialiser" name="reinitialisation" />
  </p>
  <br/>
    <?php
      if (isset($joueursReels))
      {
     ?>
     <div class="colonnes">
       <div class="colonne" style="width:50%;">
         <div id="filtre" class="cache">
           <div id="filtrePoste" class="filtreMercato">
             <select name="Poste" onchange="javascript:filtrerSurPoste(this);">
             <option value="Poste" selected="selected">Poste</option>
             <?php echo '<option value="' . ConstantesAppli::GARDIEN_IHM . '">' . ConstantesAppli::GARDIEN_IHM . '</option>' ?>
             <?php echo '<option value="' . ConstantesAppli::DEFENSEUR_IHM . '">' . ConstantesAppli::DEFENSEUR_IHM . '</option>' ?>
             <?php echo '<option value="' . ConstantesAppli::MILIEU_IHM . '">' . ConstantesAppli::MILIEU_IHM . '</option>' ?>
             <?php echo '<option value="' . ConstantesAppli::ATTAQUANT_IHM . '">' . ConstantesAppli::ATTAQUANT_IHM . '</option>' ?>
             </select>
           </div>
           <div id="filtreEquipe" class="filtreMercato">
             <select name="Equipe" onchange="javascript:filtrerSurEquipe(this);">
             <option value="Equipe" selected="selected">Equipe</option>
             <?php
              if (isset($equipes))
              {
                foreach($equipes as $cle => $value)
                {
                  echo '<option value="' . $value->libelle() . '">' . $value->libelle() . '</option>';
                }
              }
             ?>
             </select>
           </div>
           <div id="filtrePrix" class="filtreMercato">
             <input type="search" id="filtrePrixMin" placeholder="Prix min"
              class="width_80px" onchange="javascript:filtrerSurPrixMin(this);"/>
             <input type="search" id="filtrePrixMax" placeholder="Prix max"
              class="width_80px" onchange="javascript:filtrerSurPrixMax(this);"/>
           </div>
         </div>
     <table class="display" id="tableMercato">
       <thead>
         <tr>
           <th>Joueur</th>
           <th>Poste</th>
           <th>Equipe</th>
           <th>Prix</th>
         </tr>
       </thead>
       <tbody>
         <?php
         foreach($joueursReels as $value)
         {
           echo '<tr id="' . $value->id() . '"><td>' . $value->nom() . ' ' . $value->prenom() . '</td>';
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
      <div id="divAchatGB">
        <p><h2>Gardiens (2 min.) <img id="imageGB" src="./web/img/erreur.jpg" width="20px" height="20px" /></h2></p>
        <div id="listeAchatGB">
          <?php
          afficherJoueurDejaAchete($joueursPrepaMercato, ConstantesAppli::GARDIEN);
          ?>
        </div>
      </div>
      <div id="divAchatDEF">
        <p><h2>Défenseurs (6 min.) <img id="imageDEF" src="./web/img/erreur.jpg" width="20px" height="20px" /></h2></p>
        <div id="listeAchatDEF">
          <?php
          afficherJoueurDejaAchete($joueursPrepaMercato, ConstantesAppli::DEFENSEUR);
          ?>
        </div>
      </div>
      <div id="divAchatMIL">
        <p><h2>Milieux (6 min.) <img id="imageMIL" src="./web/img/erreur.jpg" width="20px" height="20px" /></h2></p>
        <div id="listeAchatMIL">
          <?php
          afficherJoueurDejaAchete($joueursPrepaMercato, ConstantesAppli::MILIEU);
          ?>
        </div>
      </div>
      <div id="divAchatATT">
        <p><h2>Attaquants (3 min.) <img id="imageATT" src="./web/img/erreur.jpg" width="20px" height="20px" /></h2></p>
        <div id="listeAchatATT">
          <?php
          afficherJoueurDejaAchete($joueursPrepaMercato, ConstantesAppli::ATTAQUANT);
          ?>
        </div>
      </div>
    </div>
  </div>
</fieldset>
</form>
<?php
// Le pied de page
require_once("vue/commun/pied_de_page.php");
?>
