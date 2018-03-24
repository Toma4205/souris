<?php

require_once(__DIR__ . '/../classeBase.php');

class CaricatureEquipe extends ClasseBase
{
  // Champs BDD
  private $_idEquipe;
  private $_code;
  private $_total;
  private $_idJoueurReel;

  // Nomenclature Caricature
  private $_libelleCourtCaricature;
  private $_libelleCaricature;

  // JoueurReel
  private $_nom;

  // nom Equipe fictive
  private $_nomEquipe;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function idEquipe() { return $this->_idEquipe; }
  public function code() { return $this->_code; }
  public function total() { return $this->_total; }
  public function idJoueurReel() { return $this->_idJoueurReel; }
  public function libelleCourtCaricature() { return $this->_libelleCourtCaricature; }
  public function libelleCaricature() { return $this->_libelleCaricature; }
  public function nom() { return $this->_nom; }
  public function nomEquipe() { return $this->_nomEquipe; }

  public function setId_equipe($id)
  {
    $this->_idEquipe = $id;
  }

  public function setCode($code)
  {
      $this->_code = $code;
  }

  public function setTotal($total)
  {
      $this->_total = $total;
  }

  public function setLibelleCourtCaricature($libelleCaricature)
  {
      $this->_libelleCourtCaricature = $libelleCaricature;
  }

  public function setLibelleCaricature($libelleCaricature)
  {
      $this->_libelleCaricature = $libelleCaricature;
  }

  public function setId_joueur_reel($idJoueur)
  {
      $this->_idJoueur = $idJoueur;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setNomEquipe($nomEquipe)
  {
      $this->_nomEquipe = $nomEquipe;
  }
}
?>
