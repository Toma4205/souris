<?php

require_once(__DIR__ . '/../classeBase.php');

class JoueurReel extends ClasseBase
{
  // Champs BDD
  private $_prenomNom;
  private $_nom;
  private $_prenom;
  private $_equipe;
  private $_position;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function prenomNom() { return $this->_prenomNom; }
  public function nom() { return $this->_nom; }
  public function prenom() { return $this->_prenom; }
  public function equipe() { return $this->_equipe; }
  public function position() { return $this->_position; }

  public function setPrenom_nom($prenomNom)
  {
    $this->_prenomNom = $prenomNom;
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
}
?>
