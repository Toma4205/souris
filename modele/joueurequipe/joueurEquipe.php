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
  private $_dateOffre;
  private $_dateValidation;

  // Libelle Equipe réelle
  private $_libelleEquipe;
  // nom Equipe fictive
  private $_nomEquipe;
  private $_tourMercato;

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
  public function libelleEquipe() { return $this->_libelleEquipe; }
  public function nomEquipe() { return $this->_nomEquipe; }
  public function dateOffre() { return $this->_dateOffre; }
  public function dateValidation() { return $this->_dateValidation; }
  public function tourMercato() { return $this->_tourMercato; }

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

  public function setTour_mercato($tourMercato)
  {
    $this->_tourMercato = $tourMercato;
  }
}
?>
