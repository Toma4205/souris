<?php

require_once(__DIR__ . '/../classeBase.php');

class Ligue extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_nom;
  private $_libellePari;
  private $_modeExpert;
  private $_nbEquipe;
  private $_dateCreation;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function libellePari() { return $this->_libellePari; }
  public function modeExpert() { return $this->_modeExpert; }
  public function modeExpertBool() {
    $mode = false;
    if (isset($this->_modeExpert))
    {
      $mode = true;
    }
    return $mode; 
  }
  public function nbEquipe() { return $this->_nbEquipe; }
  public function dateCreation() { return $this->_dateCreation; }

  public function setId($id)
  {
    $this->_id = (int) $id;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setLibellePari($libellePari)
  {
      $this->_libellePari = $libellePari;
  }

  public function setModeExpert($modeExpert)
  {
      $this->_modeExpert = $modeExpert;
  }

  public function setNbEquipe($nbEquipe)
  {
      $this->_nbEquipe = $nbEquipe;
  }

  public function setDate_creation($dateCreation)
  {
      $this->_dateCreation = $dateCreation;
  }
}
