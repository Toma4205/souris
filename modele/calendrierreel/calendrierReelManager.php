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
  
  public function findMatchsByJournee($numJournee)
  {
    $q = $this->_bdd->prepare('SELECT rr.equipeDomicile, rr.equipeVisiteur, n.libelle as libelleDomicile, 
        n2.libelle as libelleVisiteur 
        FROM resultatsl1_reel rr
        JOIN nomenclature_equipe n ON n.code = rr.equipeDomicile
        JOIN nomenclature_equipe n2 ON n2.code = rr.equipeVisiteur
        WHERE journee = :num');
    // TODO MPL mettre en constante l'année
    $q->execute([':num' => '2017'.$numJournee]);
      
    $matchs = [];
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  	{
  	    $matchs[] = new CalendrierReel($donnees);
  	}

  	$q->closeCursor();

    return $matchs;
  }
}
