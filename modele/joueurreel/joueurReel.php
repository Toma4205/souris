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

  public function setLibelleEquipe($libelleEquipe)
  {
      $this->_libelleEquipe = $libelleEquipe;
  }
}
?>
