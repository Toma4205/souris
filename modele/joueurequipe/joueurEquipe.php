<?php

require_once(__DIR__ . '/../classeBase.php');

class JoueurEquipe extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_nom;
  private $_prenom;
  private $_equipe;
  private $_position;
  private $_prixOrigine;
  private $_prixAchat;
  private $_tourMercato;
  private $_dateOffre;
  private $_dateValidation;
  private $_nbButReel;
  private $_nbButVirtuel;
  private $_nbMatch;
  private $_totalBut;

  // Code/Libelle Equipe réelle
  private $_codeEquipe;
  private $_libelleEquipe;
  // nom Equipe fictive
  private $_nomEquipe;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function prenom() { return $this->_prenom; }
  public function equipe() { return $this->_equipe; }
  public function position() { return $this->_position; }
  public function prixOrigine() { return $this->_prixOrigine; }
  public function prixAchat() { return $this->_prixAchat; }
  public function tourMercato() { return $this->_tourMercato; }
  public function codeEquipe() { return $this->_codeEquipe; }
  public function libelleEquipe() { return $this->_libelleEquipe; }
  public function nomEquipe() { return $this->_nomEquipe; }
  public function dateOffre() { return $this->_dateOffre; }
  public function dateValidation() { return $this->_dateValidation; }
  public function nbButReel() { return $this->_nbButReel; }
  public function nbButVirtuel() { return $this->_nbButVirtuel; }
  public function nbMatch() { return $this->_nbMatch; }
  public function totalBut() { return $this->_totalBut; }

  public function setId($id)
  {
    $this->_id = $id;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setPrenom($prenom)
  {
      $this->_prenom = $prenom;
  }

  public function setEquipe($equipe)
  {
      $this->_equipe = $equipe;
  }

  public function setPosition($position)
  {
      $this->_position = $position;
  }

  public function setPrixOrigine($prixOrigine)
  {
      $this->_prixOrigine = $prixOrigine;
  }

  public function setPrixAchat($prixAchat)
  {
      $this->_prixAchat = $prixAchat;
  }

  public function setCodeEquipe($codeEquipe)
  {
      $this->_codeEquipe = $codeEquipe;
  }

  public function setLibelleEquipe($libelleEquipe)
  {
      $this->_libelleEquipe = $libelleEquipe;
  }

  public function setNomEquipe($nomEquipe)
  {
      $this->_nomEquipe = $nomEquipe;
  }

  public function setDate_offre($dateOffre)
  {
    $this->_dateOffre = $dateOffre;
  }

  public function setDate_validation($dateValidation)
  {
    $this->_dateValidation = $dateValidation;
  }

  public function setNb_but_reel($nbButReel)
  {
    $this->_nbButReel = $nbButReel;
  }

  public function setNb_but_virtuel($nbButVirtuel)
  {
    $this->_nbButVirtuel = $nbButVirtuel;
  }

  public function setNb_match($nbMatch)
  {
    $this->_nbMatch = $nbMatch;
  }

  public function setTotalBut($totalBut)
  {
    $this->_totalBut = $totalBut;
  }

  public function setTour_mercato($tourMercato)
  {
    $this->_tourMercato = $tourMercato;
  }
}
?>
