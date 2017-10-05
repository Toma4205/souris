<?php

require_once('/../classeBase.php');

class Coach extends ClasseMere
{
  // Champs BDD
  private $_id;
  private $_nom;
  private $_motDePasse;
  private $_mail;
  private $_codePostal;
  private $_dateCreation;
  private $_dateMaj;

  // Champs pour Vue
  private $_amis;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function motDePasse() { return $this->_motDePasse; }
  public function mail() { return $this->_mail; }
  public function codePostal() { return $this->_codePostal; }
  public function dateCreation() { return $this->_dateCreation; }
  public function dateMaj() { return $this->_dateMaj; }
  public function amis() { return $this->_amis; }

  public function setId($id)
  {
    // L'identifiant du personnage sera, quoi qu'il arrive, un nombre entier.
    $this->_id = (int) $id;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setMot_de_passe($motDePasse)
  {
      $this->_motDePasse = $motDePasse;
  }

  public function setMail($mail)
  {
      $this->_mail = $mail;
  }

  public function setCode_postal($codePostal)
  {
      $this->_codePostal = $codePostal;
  }

  public function setDate_creation($dateCreation)
  {
      $this->_dateCreation = $dateCreation;
  }

  public function setDate_maj($dateMaj)
  {
      $this->_dateMaj = $dateMaj;
  }
}

 ?>
