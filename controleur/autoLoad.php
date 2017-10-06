<?php
function autoload($classname)
{
  $sources = array("modele/confrere/$classname.php", "modele/coach/$classname.php ",
    "modele/equipe/$classname.php ", "modele/ligue/$classname.php ");

    foreach ($sources as $source) {
          if (file_exists($source)) {
              require_once $source;
          }
      }
}

spl_autoload_register('autoload');
