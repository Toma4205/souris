<?php

$calendrier_reel = array(
date_default_timezone_set('Europe/Paris');
$now = getdate();
$time_fin_dernier_match = null ; 
foreach($calendrier_reel as $ligne_calendrier_reel)
{
	$jour_debut = date("l", strtotime($ligne_calendrier_reel['debut']));
	if(strtotime("now")-strtotime($ligne_calendrier_reel['debut']) >= 0 && strtotime("now")-strtotime($ligne_calendrier_reel['fin'])<=0 && $now['hours'] >= 13)
	{
		addLogEvent( 'Nous sommes dans la '.$ligne_calendrier_reel['num_journee']);
	}else{
		// Hors CRON
		addLogEvent('HORS HORAIRES DU SCRIPT');
}