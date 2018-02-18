<?php

// Affichage des joueurs enregistrés en BDD
function afficherJoueurPrepaMercato($joueursAchetes, $position)
{
  foreach($joueursAchetes as $value)
  {
    // Attention, si changement => effectuer aussi dans mercato.js
    if ($value->position() == $position)
    {
      echo '<p id="Achat_' . $value->id() . '" class="joueurEnCours"><span class="float_left"><img src="web/img/maillot/shirt_' . strtolower($value->codeEquipe()) . '.png"
        alt="' . $value->codeEquipe() . '" width="20px" height="20px" /></span>';
      echo '<b>' . $value->nom() . ' ' . $value->prenom() . '</b> - ' . $value->libelleEquipe() . ' (prix = ' . $value->prixOrigine() . ')';
      echo '<span class="float_right fond_blanc">';
      echo '<input type="text" class="inputPrix" name="name_' . $value->id() . '" value="' . $value->prixAchat() . '" onchange="javascript:recalculerBudgetRestant();"/>';
      echo ' <img src="./web/img/croix.jpg" alt="Supprimer" width="15px" height="15px" onclick="javascript:supprimerAchatJoueur(\'' . $value->id() . '\');" />';
      echo '</span></p>';
    }
  }
}

// Affichage des joueurs enregistrés en BDD
function afficherJoueurDejaAchete($joueursAchetes, $position)
{
  foreach($joueursAchetes as $value)
  {
    // Attention, si changement => effectuer aussi dans mercato.js
    if ($value->position() == $position)
    {
      echo '<p id="Achat_' . $value->id() . '" class="joueurAchete"><span class="float_left"><img src="web/img/maillot/shirt_' . strtolower($value->codeEquipe()) . '.png"
        alt="' . $value->codeEquipe() . '" width="20px" height="20px" /></span>';
      echo '<b>' . $value->nom() . ' ' . $value->prenom() . '</b> - ' . $value->libelleEquipe() . ' (prix = ' . $value->prixOrigine() . ')';
      echo '<span class="float_right fond_blanc">';
      echo '<input type="text" class="inputPrix" value="' . $value->prixAchat() . '" disabled/>';
      echo '</span></p>';
    }
  }
}

function ajouterMinEtImage($id, $nbMini, $tourValide)
{
  if ($tourValide == FALSE) {
    echo '(' . $nbMini . ' min.) <img id="' . $id . '" src="./web/img/erreur.jpg" width="20px" height="20px" />';
  }
}
?>

<div id="divAchatGB">
  <p><h3 class="categorie">Gardiens <?php ajouterMinEtImage('imageGB', 2, $tourValide); ?></h3></p>
  <div id="listeAchatGB">
    <?php
    if (isset($joueursAchetes)){afficherJoueurDejaAchete($joueursAchetes, ConstantesAppli::GARDIEN);}
    if (isset($joueursPrepaMercato)){afficherJoueurPrepaMercato($joueursPrepaMercato, ConstantesAppli::GARDIEN);}
    ?>
  </div>
</div>
<div id="divAchatDEF">
  <p><h3 class="categorie">Défenseurs <?php ajouterMinEtImage('imageDEF', 6, $tourValide); ?></h3></p>
  <div id="listeAchatDEF">
    <?php
    if (isset($joueursAchetes)){afficherJoueurDejaAchete($joueursAchetes, ConstantesAppli::DEFENSEUR);}
    if (isset($joueursPrepaMercato)){afficherJoueurPrepaMercato($joueursPrepaMercato, ConstantesAppli::DEFENSEUR);}
    ?>
  </div>
</div>
<div id="divAchatMIL">
  <p><h3 class="categorie">Milieux <?php ajouterMinEtImage('imageMIL', 6, $tourValide); ?></h3></p>
  <div id="listeAchatMIL">
    <?php
    if (isset($joueursAchetes)){afficherJoueurDejaAchete($joueursAchetes, ConstantesAppli::MILIEU);}
    if (isset($joueursPrepaMercato)){afficherJoueurPrepaMercato($joueursPrepaMercato, ConstantesAppli::MILIEU);}
    ?>
  </div>
</div>
<div id="divAchatATT">
  <p><h3 class="categorie">Attaquants <?php ajouterMinEtImage('imageATT', 3, $tourValide); ?></h3></p>
  <div id="listeAchatATT">
    <?php
    if (isset($joueursAchetes)){afficherJoueurDejaAchete($joueursAchetes, ConstantesAppli::ATTAQUANT);}
    if (isset($joueursPrepaMercato)){afficherJoueurPrepaMercato($joueursPrepaMercato, ConstantesAppli::ATTAQUANT);}
    ?>
  </div>
</div>
