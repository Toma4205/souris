<?php

require_once(__DIR__ . '/../classeBase.php');

class QuantiteBonusMalus extends ClasseBase
{
  // Champs BDD
  private $_code;
  private $_nbPackClassique;
  private $_nbPackFolie;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function code() { return $this->_code; }
  public function nbPackClassique() { return $this->_nbPackClassique; }
  public function nbPackFolie() { return $this->_nbPackFolie; }

  public function setCode($code)
  {
      $this->_code = $code;
  }

  public function setNb_pack_classique($nbPackClassique)
  {
    $this->_nbPackClassique = $nbPackClassique;
  }

  public function setNb_pack_folie($nbPackFolie)
  {
    $this->_nbPackFolie = $nbPackFolie;
  }
}
?>
