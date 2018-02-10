<?php

require_once(__DIR__ . '/../classeBase.php');

class CalendrierLigue extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_idEquipeDom;
  private $_idEquipeExt;
  private $_numJournee;
  private $_scoreDom;
  private $_scoreExt;
  private $_numJourneeCalReel;

  // nom Equipe fictive
  private $_nomEquipeDom;
  private $_nomEquipeExt;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function idEquipeDom() { return $this->_idEquipeDom; }
  public function idEquipeExt() { return $this->_idEquipeExt; }
  public function numJournee() { return $this->_numJournee; }
  public function scoreDom() { return $this->_scoreDom; }
  public function scoreExt() { return $this->_scoreExt; }
  public function numJourneeCalReel() { return $this->_numJourneeCalReel; }
  public function nomEquipeDom() { return $this->_nomEquipeDom; }
  public function nomEquipeExt() { return $this->_nomEquipeExt; }

  public function setId($id)
  {
    $this->_id = $id;
  }

  public function setId_equipe_dom($id)
  {
    $this->_idEquipeDom = $id;
  }

  public function setId_equipe_ext($id)
  {
    $this->_idEquipeExt = $id;
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

  public function setNum_journee_cal_reel($numJournee)
  {
      $this->_numJourneeCalReel = $numJournee;
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
