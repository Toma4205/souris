<?php

require_once(__DIR__ . '/../managerBase.php');

class ActualiteCoachManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findByCoach($idCoach)
  {
    $q = $this->_bdd->prepare('SELECT *
          FROM actualite_coach
          WHERE id_coach = :id
          AND date_suppression IS NULL');
    $q->execute([':id' => $idCoach]);

    $actus = [];
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $actus[] = new ActualiteCoach($donnees);
    }
    $q->closeCursor();

    return $actus;
  }
}
