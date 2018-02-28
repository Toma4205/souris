<?php

//Fonction d'écriture des logs dans un fichier
function addLogEvent($event)
{
    date_default_timezone_set('Europe/Paris');
	$time = date("D, d M Y H:i:s");
    $time = "[".$time."] ";
	
	
	$year_month = date("YF");
	$fichier = __DIR__ . '\\logs\\'.$year_month.'.log';
	
    $event = $time.$event."\n";
 
    file_put_contents($fichier, $event, FILE_APPEND);
}




?>