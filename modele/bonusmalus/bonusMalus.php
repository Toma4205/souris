<?php

require_once(__DIR__ . '/../classeBase.php');

class BonusMalus extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_code;
  private $_idEquipe;
  private $_idCalLigue;
  private $_idJoueurReelEquipe;
  private $_idJoueurReelAdverse;
  private $_miTemps;

  // Champs tables jointes
  private $_libelle;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function code() { return $this->_code; }
  public function idJoueurReelEquipe() { return $this->_idJoueurReelEquipe; }
  public function idJoueurReelAdverse() { return $this->_idJoueurReelAdverse; }
  public function miTemps() { return $this->_miTemps; }
  public function libelle() { return $this->_libelle; }

  public function setId($id)
  {
    $this->_id = $id;
  }

  public function setCode($code)
  {
      $this->_code = $code;
  }

  public function setId_joueur_reel_equipe($idJoueurReelEquipe)
  {
    $this->_idJoueurReelEquipe = $idJoueurReelEquipe;
  }

  public function setId_joueur_reel_adverse($idJoueurReelAdverse)
  {
    $this->_idJoueurReelAdverse = $idJoueurReelAdverse;
  }

  public function setMi_temps($miTemps)
  {
      $this->_miTemps = $miTemps;
  }

  public function setLibelle($libelle)
  {
      $this->_libelle = $libelle;
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
