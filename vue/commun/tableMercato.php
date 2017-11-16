<div class="colonnes sousTitre" style="border-top:thick double #808080;">
  <div class="colonne" style="width:50%;border-right:thick double #808080;">
  <?php
  if (isset($joueursReels))
  {
  ?>
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
  ?>
  </tbody>
</table>
<?php
} else {
  echo 'Aucun joueur en base !!!';
}
?>
</div>
<div class="colonne" style="width:50%;">
  <?php require_once("vue/commun/tableAchatJoueur.php");?>
</div>
</div>
