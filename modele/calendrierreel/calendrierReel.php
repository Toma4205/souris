<?php

require_once(__DIR__ . '/../classeBase.php');

class CalendrierReel extends ClasseBase
{
  // Champs BDD
  private $_numJournee;
  private $_dateHeureDebut;
  private $_statut;
  
  // Champs resultatsl1_reel
  private $_equipeDomicile;
  private $_equipeVisiteur;
  private $_libelleDomicile;
  private $_libelleVisiteur;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function numJournee() { return $this->_numJournee; }
  public function dateHeureDebut() { return $this->_dateHeureDebut; }
  public function statut() { return $this->_statut; }
  public function equipeDomicile() { return $this->_equipeDomicile; }
  public function equipeVisiteur() { return $this->_equipeVisiteur; }
  public function libelleDomicile() { return $this->_libelleDomicile; }
  public function libelleVisiteur() { return $this->_libelleVisiteur; }

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
  
  public function setEquipeDomicile($equipeDomicile)
  {
      $this->_equipeDomicile = $equipeDomicile;
  }
  
  public function setEquipeVisiteur($equipeVisiteur)
  {
      $this->_equipeVisiteur = $equipeVisiteur;
  }
  
  public function setLibelleDomicile($libelleDomicile)
  {
      $this->_libelleDomicile = $libelleDomicile;
  }
  
  public function setLibelleVisiteur($libelleVisiteur)
  {
      $this->_libelleVisiteur = $libelleVisiteur;
  }
}
?>
