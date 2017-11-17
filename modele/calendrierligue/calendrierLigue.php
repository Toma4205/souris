<?php

require_once(__DIR__ . '/../classeBase.php');

class CalendrierLigue extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_numJournee;
  private $_scoreDom;
  private $_scoreExt;

  // nom Equipe fictive
  private $_nomEquipeDom;
  private $_nomEquipeExt;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function numJournee() { return $this->_numJournee; }
  public function scoreDom() { return $this->_scoreDom; }
  public function scoreExt() { return $this->_scoreExt; }
  public function nomEquipeDom() { return $this->_nomEquipeDom; }
  public function nomEquipeExt() { return $this->_nomEquipeExt; }

  public function setId($id)
  {
    $this->_id = $id;
  }

  public function setNum_journee($numJournee)
  {
      $this->_numJournee = $numJournee;
  }

  public function setScore_dom($scoreDom)
  {
      $this->_scoreDom = $scoreDom;
  }

  public function setScore_ext($scoreExt)
  {
      $this->_scoreExt = $scoreExt;
  }

  public function setNomEquipeDom($nomEquipeDom)
  {
      $this->_nomEquipeDom = $nomEquipeDom;
  }

  public function setNomEquipeExt($nomEquipeExt)
  {
      $this->_nomEquipeExt = $nomEquipeExt;
  }
}
?>
