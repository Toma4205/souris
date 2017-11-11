<?php

require_once(__DIR__ . '/../classeBase.php');

class NomenclatureEquipe extends ClasseBase
{
  // Champs BDD
  private $_code;
  private $_libelle;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function code() { return $this->_code; }
  public function libelle() { return $this->_libelle; }

  public function setCode($code)
  {
    $this->_code = $code;
  }

  public function setLibelle($libelle)
  {
      $this->_libelle = $libelle;
  }
}
?>
