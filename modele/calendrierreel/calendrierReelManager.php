<?php

require_once(__DIR__ . '/../managerBase.php');

class CalendrierReelManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findProchaineJournee()
  {
      $q = $this->_bdd->prepare('SELECT * FROM calendrier_reel
        WHERE date_heure_debut > NOW() ORDER BY date_heure_debut ASC LIMIT 1');

      $q->execute();
      $donnees = $q->fetch(PDO::FETCH_ASSOC);
      $q->closeCursor();

      // Si plus de calendrier
      if (is_bool($donnees))
      {
        return new CalendrierReel([]);
      }
      else
      {
        return new CalendrierReel($donnees);
      }
  }
}
