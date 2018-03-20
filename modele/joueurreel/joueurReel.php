<?php

require_once(__DIR__ . '/../classeBase.php');

class JoueurReel extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_nom;
  private $_prenom;
  private $_equipe;
  private $_position;
  private $_prix;
  private $_anniv;
  private $_nationalite;
  private $_positionExpert;
  private $_positionSecondaire;

  // Libelle Equipe
  private $_libelleEquipe;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function prenom() { return $this->_prenom; }
  public function equipe() { return $this->_equipe; }
  public function position() { return $this->_position; }
  public function prix() { return $this->_prix; }
  public function anniv() { return $this->_anniv; }
  public function nationalite() { return $this->_nationalite; }
  public function positionExpert() { return $this->_positionExpert; }
  public function positionSecondaire() { return $this->_positionSecondaire; }
  public function libelleEquipe() { return $this->_libelleEquipe; }

  public function positionIHM()
  {
    $positionIHM = ConstantesAppli::GARDIEN_IHM;
    if ($this->_position == ConstantesAppli::DEFENSEUR)
    {
      $positionIHM = ConstantesAppli::DEFENSEUR_IHM;
    }
    else if ($this->_position == ConstantesAppli::MILIEU)
    {
      $positionIHM = ConstantesAppli::MILIEU_IHM;
    }
    else if ($this->_position == ConstantesAppli::ATTAQUANT)
    {
      $positionIHM = ConstantesAppli::ATTAQUANT_IHM;
    }
    return $positionIHM;
  }

  public function setId($id)
  {
    $this->_id = (int) $id;
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

  public function setPrix($prix)
  {
      $this->_prix = $prix;
  }
  
  public function setAnniv($anniv)
  {
      $this->_anniv = $anniv;
  }
  
  public function setNationalite($nat)
  {
      $this->_nationalite = $nat;
  }
  
  public function setPosition_expert($pos)
  {
      $this->_positionExpert = $pos;
  }
  
  public function setPosition_secondaire($pos)
  {
      $this->_positionSecondaire = $pos;
  }

  public function setLibelleEquipe($libelleEquipe)
  {
      $this->_libelleEquipe = $libelleEquipe;
  }
}
?>
