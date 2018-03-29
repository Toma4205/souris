<?php

require_once(__DIR__ . '/../classeBase.php');

class CompoEquipe extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_codeTactique;
  private $_codeBonusMalus;
  private $_pariDom;
  private $_pariExt;

  // Jointure table;
  private $_libCourtBonusMalus;
  private $_libLongBonusMalus;
  private $_nomJoueurReelEquipe;
  private $_nomJoueurReelAdverse;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function codeTactique() { return $this->_codeTactique; }
  public function codeBonusMalus() { return $this->_codeBonusMalus; }
  public function pariDom() { return $this->_pariDom; }
  public function pariExt() { return $this->_pariExt; }
  public function libCourtBonusMalus() { return $this->_libCourtBonusMalus; }
  public function libLongBonusMalus() { return $this->_libLongBonusMalus; }
  public function nomJoueurReelEquipe() { return $this->_nomJoueurReelEquipe; }
  public function nomJoueurReelAdverse() { return $this->_nomJoueurReelAdverse; }

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

  public function setPari_dom($pariDom)
  {
      $this->_pariDom = $pariDom;
  }

  public function setPari_ext($pariExt)
  {
      $this->_pariExt = $pariExt;
  }

  public function setLibCourtBonusMalus($libCourtBonusMalus)
  {
      $this->_libCourtBonusMalus = $libCourtBonusMalus;
  }

  public function setLibLongBonusMalus($libLongBonusMalus)
  {
      $this->_libLongBonusMalus = $libLongBonusMalus;
  }

  public function setNomJoueurReelEquipe($nomJoueurReelEquipe)
  {
      $this->_nomJoueurReelEquipe = $nomJoueurReelEquipe;
  }

  public function setNomJoueurReelAdverse($nomJoueurReelAdverse)
  {
      $this->_nomJoueurReelAdverse = $nomJoueurReelAdverse;
  }
}
?>
