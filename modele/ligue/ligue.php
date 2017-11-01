<?php

require_once(__DIR__ . '/../classeBase.php');

class Ligue extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_nom;
  private $_etat;
  private $_libellePari;
  private $_modeExpert;
  private $_nbEquipe;
  private $_dateCreation;

  // Champs de coach_ligue
  private $_createur;
  private $_dateValidation;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function etat() { return $this->_etat; }
  public function libellePari() { return $this->_libellePari; }
  public function modeExpert() { return $this->_modeExpert; }
  public function nbEquipe() { return $this->_nbEquipe; }
  public function dateCreation() { return $this->_dateCreation; }
  public function createur() { return $this->_createur; }
  public function dateValidation() { return $this->_dateValidation; }

  public function setId($id)
  {
    $this->_id = (int) $id;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setEtat($etat)
  {
      $this->_etat = $etat;
  }

  public function setLibelle_pari($libellePari)
  {
      $this->_libellePari = $libellePari;
  }

  public function setMode_expert($modeExpert)
  {
      $this->_modeExpert = $modeExpert;
  }

  public function setNb_equipe($nbEquipe)
  {
      $this->_nbEquipe = $nbEquipe;
  }

  public function setDate_creation($dateCreation)
  {
      $this->_dateCreation = $dateCreation;
  }

  public function setCreateur($createur)
  {
      $this->_createur = $createur;
  }

  public function setDate_validation($dateValidation)
  {
      $this->_dateValidation = $dateValidation;
  }
}
