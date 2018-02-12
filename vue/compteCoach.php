<?php
// entete
$vueCss = 'compteCoach.css';
require_once("vue/commun/enteteflex.php");
?>
<section id="sectionMesActions">
    <header>Mes actions en attente</header>
    <?php
    $nbAction = 0;
    if (sizeof($ligues) > 0)
    {
      echo '<div>';
      echo '<ul>';
      foreach($ligues as $value)
      {
        if ($value->etat() == EtatLigue::MERCATO)
        {
          $nbAction++;
          echo '<li class="detail_action_actu">Ton équipe n\'est pas terminée pour la ligue <b>"' . $value->nom() . '"</b> !';
          echo '<span class="float_right">';
          echo '<input type="submit" value="Je prends Neymar et Fekir" name="continuerCreaLigue[' . $value->id() . ']" />';
          echo '</span>';
          echo '</li>';
        }
        elseif ($value->etat() == EtatLigue::CREATION)
        {
          if ($value->createur())
          {
            $nbAction++;
            echo '<li class="detail_action_actu">Ta ligue <b>"' . $value->nom() . '"</b> est toujours en cours de création !';
            echo '<span class="float_right">';
            echo '<input type="submit" value="Je m\'en occupe maintenant" name="continuerCreaLigue[' . $value->id() . ']" />';
            echo '</span>';
            echo '</li>';
          }
          else if (null == $value->dateValidation())
          {
            $nbAction++;
            echo '<li class="detail_action_actu"><b>"' . $value->nomCoachCreateur() . '"</b> t\'invite dans sa ligue <b>"' . $value->nom() . '"</b>';
            if ($value->libellePari() != null) {
              echo '<br/> (enjeu : <b>"' . $value->libellePari() . '"</b>)';
            }
            echo '<span class="float_right">';
            echo '. <input type="submit" value="Je me lance" name="accepterInvitation[' . $value->id() . ']" /> ';
            echo ' <input type="submit" value="J\'ai piscine" name="refuserInvitation[' . $value->id() . ']" />';
            echo '</span>';
            echo '</li>';
          }
        }
      }
      echo '</ul>';
      echo '</div>';
    }
    if ($nbAction == 0) {
      echo '<div>Ton bureau est à jour.</div>';
    }
    ?>
</section>
<section>
    <header>Actualités</header>
    <?php
    $nbActu = 0;
    if (sizeof($ligues) > 0)
    {
      echo '<div>';
      echo '<ul>';

      foreach($ligues as $value)
      {
        if ($value->etat() == EtatLigue::CREATION)
        {
          if ($value->createur() == FALSE && null != $value->dateValidation())
          {
            $nbActu++;
            echo '<li class="detail_action_actu"><b>"' . $value->nomCoachCreateur() . '"</b> doit valider ta participation à la ligue <b>"' . $value->nom() . '"</b>.</li>';
          }
        }
      }
      echo '</ul>';
      echo '</div>';
    }
    if ($nbActu == 0) {
      echo '<div>Aucune actualité pour le moment. <b>#viedeouf</b></div>';
    }
    ?>
</section>
<section id="sectionMesLigues">
      <header>Mes ligues</header>
      <?php
      if (sizeof($ligues) > 0)
      {
      ?>
      <table class="tableBase">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Classement</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach($ligues as $value)
          {
            if ($value->etat() == EtatLigue::EN_COURS || $value->etat() == EtatLigue::TERMINEE)
            {
              echo '<tr><td>' . $value->nom() . '</td>';
              if ($value->classement() != null)
              {
                echo '<td>' . $value->classement() . '</td>';
              }
              else
              {
                echo '<td>Aucun</td>';
              }

              if ($value->etat() == EtatLigue::EN_COURS) {
                echo '<td><input type="submit" value="Rejoindre" name="rejoindre[' . $value->id() . ']" /></td>';
              }
              elseif ($value->etat() == EtatLigue::TERMINEE) {
                echo '<td>A venir... (T)</td>';
                //echo '<td><input type="submit" value="Masquer" name="masquer[' . $value->id() . ']" /></td>';
              }
              echo '</tr>';
            }
          }
          echo '</tbody></table>';
        }
        else
        {
          echo '<br/>';
          echo 'Aucune ligue. Faut se mettre au boulot jeune padawan !';
        }
          ?>
</section>
<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
