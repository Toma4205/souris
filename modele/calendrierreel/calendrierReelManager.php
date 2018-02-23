<?php

require_once(__DIR__ . '/../managerBase.php');

class CalendrierReelManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findNumJourneeEnCours()
  {
    $q = $this->_bdd->prepare('SELECT num_journee FROM calendrier_reel
      WHERE statut = :statut');
    $q->execute(['statut' => ConstantesAppli::STATUT_CAL_EN_COURS]);
    return $q->fetchColumn();
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

  public function findJourneeByNumero($numJournee)
  {
    $q = $this->_bdd->prepare('SELECT * FROM calendrier_reel
      WHERE num_journee = :num');

    $q->execute([':num' => $numJournee]);
    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    // Si pas de calendrier
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
