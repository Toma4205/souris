<?php

class Coach
{
  private $_id;
  private $_nom;
  private $_motDePasse;
  private $_mail;
  private $_codePostal;

  public function __construct(array $donnees)
  {
    $this->hydrate($donnees);
  }

  // Un tableau de données doit être passé à la fonction (d'où le préfixe « array »).
  public function hydrate(array $donnees)
  {
    foreach ($donnees as $key => $value)
    {
      // On récupère le nom du setter correspondant à l'attribut.
      $method = 'set'.ucfirst($key);

      // Si le setter correspondant existe.
      if (method_exists($this, $method))
      {
        // On appelle le setter.
        $this->$method($value);
      }
    }
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function motDePasse() { return $this->_motDePasse; }
  public function mail() { return $this->_mail; }
  public function codePostal() { return $this->_codePostal; }

  public function setId($id)
  {
    // L'identifiant du personnage sera, quoi qu'il arrive, un nombre entier.
    $this->_id = (int) $id;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setMotDePasse($motDePasse)
  {
      $this->_motDePasse = $motDePasse;
  }

  public function setMail($mail)
  {
      $this->_mail = $mail;
  }

  public function setCodePostal($codePostal)
  {
      $this->_codePostal = $codePostal;
  }
}


 ?>
