<?php
// entete
require_once("vue/commun/enteteflex.php");

if (isset($joueursEquipeTries))
{
  for($i = ($creaLigue->tourMercato() - 1) ; $i > 0 ; $i--)
  {
    echo '<section><header>Tour ' . $i . '</header>';
    echo '<table class="tableMercatoLigue"><tbody>';
    foreach($joueursEquipeTries as $cle => $joueur)
    {
      if ($joueur->tourMercato() == $i)
      {
        $dateOffre = date_create($joueur->dateOffre());
        if ($joueur->dateValidation() != null)
        {
            echo '<tr class="joueurAchete">';
            echo '<td>' . $joueur->nom() . ' ' . $joueur->prenom() . '</td>';
            echo '<td>' . $joueur->libelleEquipe() . '</td>';
            echo '<td>' . $joueur->prixAchat() . ' M€</td>';
            echo '<td>' . $joueur->nomEquipe() . '</td>';
            echo '<td>' . date_format($dateOffre, 'd/m/Y H:i:s') . '</td></tr>';
            ;
        }
        else
        {
          echo '<tr class="joueurEnCours">';
          echo '<td></td>';
          echo '<td></td>';
          echo '<td>' . $joueur->prixAchat() . '</td>';
          echo '<td>' . $joueur->nomEquipe() . '</td>';
          echo '<td>' . date_format($dateOffre, 'd/m/Y H:i:s') . '</td></tr>';
        }
        unset($joueursEquipeTries[$cle]);
      }
    }
    echo '</tbody></table></section>';
  }
  /*foreach($joueursEquipeTries as $cle => $joueur)
  {
    $classeCss = 'joueurEnCours';
    if ($joueur->dateValidation() != null)
    {
        $classeCss = 'joueurAchete';
    }

    echo '<div class="' . $classeCss . '">' . $joueur->nom() . ' ' . $joueur->prenom() . ' - ' . $joueur->libelleEquipe()
      . ' - ' . $joueur->prixAchat() . ' M€ (' . $joueur->nomEquipe() . ')</div><br/>';
  }*/
} else {
  echo '<p>Aucun tour terminé pour le moment.</p>';
}

// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
