<?php
// entete
require_once("vue/commun/enteteflex.php");

if (isset($calReel))
{
?>
<section>
  <div class="conteneurRow enteteEquipe">
    <p class="width_25pc">
      <span>
      <?php
        echo $calLigue->nomEquipeDom();
       ?>
      </span>
    </p>
    <p class="width_50pc">
      <?php
        $dateDebut = date_create($calReel->dateHeureDebut());
        echo 'Début de la ' . $calReel->numJournee() . 'e journée de L1' .
          '<br/><span class="heureProchaineJournee">' . date_format($dateDebut, 'd/m/Y H:i:s') . '</span>';
      ?>
    </p>
    <p class="width_25pc">
      <?php
        echo $calLigue->nomEquipeExt();
       ?>
    </p>
  </div>
  <p>A venir...</p>
</section>
<?php
}
else
{
  echo '<p>Plus de match de championnat !</p>';
}
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>
