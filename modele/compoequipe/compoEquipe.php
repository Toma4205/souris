<?php

require_once(__DIR__ . '/../classeBase.php');

class CompoEquipe extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_codeTactique;
  private $_codeBonusMalus;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function codeTactique() { return $this->_codeTactique; }
  public function codeBonusMalus() { return $this->_codeBonusMalus; }

  public function setId($id)
  {
    $this->_id = (int) $id;
  }

  public function setCode_tactique($codeTactique)
  {
      $this->_codeTactique = $codeTactique;
  }

  public function setCode_bonus_malus($codeBonusMalus)
  {
      $this->_codeBonusMalus = $codeBonusMalus;
  }
}
?>
