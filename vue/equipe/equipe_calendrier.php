<section id="calendrier_reel">
    <?php
    function afficherMatchCalendrierReel($match)
    {
        echo '<li class="detail_calendrier_reel_match conteneurRow">';
        echo '<div class="width_10pc"><img src="web/img/maillot/shirt_' . strtolower($match->equipeDomicile()) . '.png" alt="' . $match->equipeDomicile() . '" width="20px" height="20px" /></div>';
        echo '<div class="width_35pc text_align_right margin_auto_vertical">'.$match->libelleDomicile().'</div>';
        echo '<div class="width_10pc margin_auto_vertical">vs</div>';
        echo '<div class="width_35pc text_align_left margin_auto_vertical">'.$match->libelleVisiteur().'</div>';
        echo '<div class="width_10pc"><img src="web/img/maillot/shirt_' . strtolower($match->equipeVisiteur()) . '.png" alt="' . $match->equipeVisiteur() . '" width="20px" height="20px" /></div>';
        echo '</li>';
    }

    if (isset($matchsCalReel)) {
        echo '<div class="detail_effectif calendrier_reel_journee_bloc">';
        echo '<div class="calendrier_reel_journee_titre">Calendrier L1 journée '.$calReel->numJournee().'</div>';
        echo '<ul>';
        foreach($matchsCalReel as $match)
        {
            afficherMatchCalendrierReel($match);
        }
        echo '</ul>';
        echo '</div>';
    } else {
        echo '<p>Aucun calendrier réel trouvé en base pour la journée ' . $calLigue->numJournee() . '</p>';
    }
    ?>
</section>
