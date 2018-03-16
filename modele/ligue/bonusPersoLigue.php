<?php

require_once(__DIR__ . '/../classeBase.php');

class BonusPersoLigue extends ClasseBase
{
  // Champs BDD
  private $_code;
  private $_nb;
  private $_idLigue;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function code() { return $this->_code; }
  public function nb() { return $this->_nb; }
  public function idLigue() { return $this->_idLigue; }

  public function setCode($code)
  {
      $this->_code = $code;
  }

  public function setNb($nb)
  {
      $this->_nb = $nb;
  }

  public function setId_ligue($id)
  {
      $this->_idLigue = $id;
  }
}
