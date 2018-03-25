<?php

require_once(__DIR__ . '/../classeBase.php');

class ActualiteCoach extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_idCoach;
  private $_idEquipe;
  private $_libelle;
  private $_dateCreation;
  private $_dateSuppression;

  // Champs Table equipe
  private $_nomEquipe;

  // Champs Table ligue
  private $_nomLigue;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function idCoach() { return $this->_idCoach; }
  public function idEquipe() { return $this->_idEquipe; }
  public function libelle() { return $this->_libelle; }
  public function dateCreation() { return $this->_dateCreation; }
  public function dateSuppression() { return $this->_dateSuppression; }
  public function nomEquipe() { return $this->_nomEquipe; }
  public function nomLigue() { return $this->_nomLigue; }

  public function setId($id)
  {
    $this->_id = (int) $id;
  }

  public function setId_coach($id)
  {
    $this->_idCoach = (int) $id;
  }

  public function setId_equipe($id)
  {
    $this->_idEquipe = (int) $id;
  }

  public function setLibelle($libelle)
  {
      $this->_libelle = $libelle;
  }

  public function setDate_creation($dateCreation)
  {
      $this->_dateCreation = $dateCreation;
  }

  public function setDate_suppression($dateSuppression)
  {
      $this->_dateSuppression = $dateSuppression;
  }

  public function setNom_equipe($nomEquipe)
  {
      $this->_nomEquipe = $nomEquipe;
  }

  public function setNom_ligue($nomLigue)
  {
      $this->_nomLigue = $nomLigue;
  }
}

 ?>
