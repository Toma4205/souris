<section id="actualite_joueurs">
<?php
    function afficherEtatJoueur($joueur,$etat)
    {
      $trouve = 0;
      if($joueur->etat() == $etat) {
          echo '<li class="detail_etat_joueur conteneurRow">';
          echo '<div class="width_10pc"><img src="web/img/maillot/shirt_' . strtolower($joueur->codeEquipe()) . '.png" alt="' . $joueur->codeEquipe() . '" width="20px" height="20px" /></div>';
          echo '<div class="width_35pc text_align_left margin_auto_vertical">'.$joueur->nom().' '.$joueur->prenom().'</div>';
          echo '</li>';
          $trouve = 1;
      }
      return $trouve;
    }

    if (isset($joueurs)) {
        $trouve = 0;
        echo '<div class="detail_effectif calendrier_reel_journee_bloc">';
        echo '<div class="calendrier_reel_journee_titre">Fragiles';
        echo '<img class="margin_left_5px" src="web/img/OUT.png" alt="OUT" width="20px" height="20px" />';
        echo '</div><ul>';
        foreach($joueurs as $joueur) {
            $trouve += afficherEtatJoueur($joueur,ConstantesAppli::BLESSE);
        }
        if($trouve == 0) {
            echo '<li class="detail_etat_joueur conteneurRow">';
            echo '<div class="width_55pc text_align_left">Aucun joueur dans  cette catégorie</div>';
            echo '</li>';
        } else {
            $trouve = 0;
        }
        echo '</ul>';
        echo '</div>';

        echo '<div class="detail_effectif calendrier_reel_journee_bloc">';
        echo '<div class="calendrier_reel_journee_titre">Nerveux';
        echo '<img class="margin_left_5px" src="web/img/SUS.png" alt="SUS" width="20px" height="20px" />';
        echo '</div><ul>';
        foreach($joueurs as $joueur) {
            $trouve += afficherEtatJoueur($joueur,ConstantesAppli::SUSPENDU);
        }
        if($trouve == 0) {
            echo '<li class="detail_etat_joueur conteneurRow">';
            echo '<div class="width_55pc text_align_left">Aucun joueur dans  cette catégorie</div>';
            echo '</li>';
        } else {
            $trouve = 0;
        }
        echo '</ul>';
        echo '</div>';

        echo '<div class="detail_effectif calendrier_reel_journee_bloc">';
        echo '<div class="calendrier_reel_journee_titre">Suspense';
        echo '<img class="margin_left_5px" src="web/img/GTD.png" alt="Incertains" width="20px" height="20px" />';
        echo '</div><ul>';
        foreach($joueurs as $joueur) {
            $trouve += afficherEtatJoueur($joueur,ConstantesAppli::INCERTAIN);
        }
        if($trouve == 0) {
            echo '<li class="detail_etat_joueur conteneurRow">';
            echo '<div class="width_55pc text_align_left">Aucun joueur dans  cette catégorie</div>';
            echo '</li>';
        } else {
            $trouve = 0;
        }
        echo '</ul>';
        echo '</div>';
    } else {
        echo '<p>Aucun joueur réel trouvé en base pour la journée</p>';
    }
    ?>
</section>
