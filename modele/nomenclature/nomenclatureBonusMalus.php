<?php

require_once(__DIR__ . '/../classeBase.php');

class NomenclatureBonusMalus extends ClasseBase
{
  // Champs BDD
  private $_code;
  private $_libelleCourt;
  private $_libelle;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function code() { return $this->_code; }
  public function libelleCourt() { return $this->_libelleCourt; }
  public function libelle() { return $this->_libelle; }

  public function setCode($code)
  {
    $this->_code = $code;
  }

  public function setLibelle($libelle)
  {
      $this->_libelle = $libelle;
  }

  public function setLibelle_court($libelle)
  {
      $this->_libelleCourt = $libelle;
  }
}
?>
