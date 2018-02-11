<?php

require_once(__DIR__ . '/../classeBase.php');

class NomenclatureStyleCoach extends ClasseBase
{
  // Champs BDD
  private $_code;
  private $_libelle;
  private $_description;
  private $_nomImage;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function code() { return $this->_code; }
  public function libelle() { return $this->_libelle; }
  public function description() { return $this->_description; }
  public function nomImage() { return $this->_nomImage; }

  public function setCode($code)
  {
    $this->_code = $code;
  }

  public function setLibelle($libelle)
  {
      $this->_libelle = $libelle;
  }

  public function setDescription($description)
  {
    $this->_description = $description;
  }

  public function setNom_image($nomImage)
  {
    $this->_nomImage = $nomImage;
  }
}
?>
