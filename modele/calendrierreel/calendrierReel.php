<?php

require_once(__DIR__ . '/../classeBase.php');

class CalendrierReel extends ClasseBase
{
  // Champs BDD
  private $_numJournee;
  private $_dateHeureDebut;
  private $_statut;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function numJournee() { return $this->_numJournee; }
  public function dateHeureDebut() { return $this->_dateHeureDebut; }
  public function statut() { return $this->_statut; }

  public function setNum_journee($numJournee)
  {
      $this->_numJournee = (int) $numJournee;
  }

  public function setDate_heure_debut($dateHeureDebut)
  {
      $this->_dateHeureDebut = $dateHeureDebut;
  }

  public function setStatut($statut)
  {
      $this->_statut = $statut;
  }
}
?>
