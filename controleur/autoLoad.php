<?php
function autoload($classname)
{
  // Transforme la première lettre en minuscule
  $classname = lcfirst($classname);
  $sources = array("modele/confrere/$classname.php", "modele/coach/$classname.php",
    "modele/equipe/$classname.php", "modele/ligue/$classname.php",
    "modele/joueurreel/$classname.php", "modele/prepamercato/$classname.php",
    "modele/nomenclature/$classname.php", "modele/joueurequipe/$classname.php",
    "modele/calendrierligue/$classname.php", "modele/bonusmalus/$classname.php",
    "modele/calendrierreel/$classname.php", "modele/compoequipe/$classname.php",
    "modele/caricatureequipe/$classname.php", "modele/actualitecoach/$classname.php");

    foreach ($sources as $source) {
          if (file_exists($source)) {
              //echo 'Trouve : ' . $source . '<br/>';
              require_once $source;
              break;
          } else {
            //echo 'Err : ' . $source . '<br/>';
          }
      }
}

spl_autoload_register('autoload');
