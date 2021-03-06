<?php

require_once(__DIR__ . '/../classeBase.php');

class BonusMalus extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_code;
  private $_selectJoueur;
  private $_idEquipe;
  private $_idCalLigue;
  private $_idJoueurReelEquipe;
  private $_idJoueurReelAdverse;

  // Champs tables jointes
  private $_libelleCourt;
  private $_libelle;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function code() { return $this->_code; }
  public function selectJoueur() { return $this->_selectJoueur; }
  public function idJoueurReelEquipe() { return $this->_idJoueurReelEquipe; }
  public function idJoueurReelAdverse() { return $this->_idJoueurReelAdverse; }
  public function libelle() { return $this->_libelle; }
  public function libelleCourt() { return $this->_libelleCourt; }

  public function setId($id)
  {
    $this->_id = $id;
  }

  public function setCode($code)
  {
      $this->_code = $code;
  }

  public function setSelect_joueur($selectJoueur)
  {
      $this->_selectJoueur = $selectJoueur;
  }

  public function setId_joueur_reel_equipe($idJoueurReelEquipe)
  {
    $this->_idJoueurReelEquipe = $idJoueurReelEquipe;
  }

  public function setId_joueur_reel_adverse($idJoueurReelAdverse)
  {
    $this->_idJoueurReelAdverse = $idJoueurReelAdverse;
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
