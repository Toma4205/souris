<?php

require_once(__DIR__ . '/../classeBase.php');

class JoueurCompoEquipe extends ClasseBase
{
  // Champs BDD
  private $_idJoueurReel;
  private $_numero;
  private $_capitaine;
  private $_note;
  private $_noteBonus;
  private $_numeroRemplacement;
  private $_idJoueurReelRemplacant;
  private $_noteMinRemplacement;
  private $_nbButReel;
  private $_nbButVirtuel;
  private $_numeroDefinitif;

  // Champs table joueur_reel
  private $_nom;
  private $_prenom;
  private $_nomRemplacant;
  private $_prenomRemplacant;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function idJoueurReel() { return $this->_idJoueurReel; }
  public function numero() { return $this->_numero; }
  public function capitaine() { return $this->_capitaine; }
  public function note() { return $this->_note; }
  public function noteBonus() { return $this->_noteBonus; }
  public function numeroRemplacement() { return $this->_numeroRemplacement; }
  public function idJoueurReelRemplacant() { return $this->_idJoueurReelRemplacant; }
  public function nbButVirtuel() { return $this->_nbButVirtuel; }
  public function nbButReel() { return $this->_nbButReel; }
  public function noteMinRemplacement() { return $this->_noteMinRemplacement; }
  public function numeroDefinitif() { return $this->_numeroDefinitif; }
  public function nom() { return $this->_nom; }
  public function prenom() { return $this->_prenom; }
  public function nomRemplacant() { return $this->_nomRemplacant; }
  public function prenomRemplacant() { return $this->_prenomRemplacant; }

  public function setId_joueur_reel($idJoueurReel)
  {
    $this->_idJoueurReel = (int) $idJoueurReel;
  }

  public function setNumero($numero)
  {
      $this->_numero = $numero;
  }

  public function setCapitaine($capitaine)
  {
      $this->_capitaine = $capitaine;
  }

  public function setNote($note)
  {
      $this->_note = $note;
  }

  public function setNote_bonus($note)
  {
      $this->_noteBonus = $note;
  }

  public function setNumero_remplacement($numeroRemplacement)
  {
      $this->_numeroRemplacement = $numeroRemplacement;
  }

  public function setId_joueur_reel_remplacant($idJoueurReelRemplacant)
  {
      $this->_idJoueurReelRemplacant = $idJoueurReelRemplacant;
  }

  public function setNote_min_remplacement($noteMinRemplacement)
  {
      $this->_noteMinRemplacement = $noteMinRemplacement;
  }

  public function setNb_but_reel($nom)
  {
      $this->_nbButReel = $nom;
  }

  public function setNb_but_virtuel($nbBut)
  {
      $this->_nbButVirtuel = $nbBut;
  }

  public function setNumero_definitif($numeroDefinitif)
  {
      $this->_numeroDefinitif = $numeroDefinitif;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setPrenom($prenom)
  {
      $this->_prenom = $prenom;
  }

  public function setNom_remplacant($nom)
  {
      $this->_nomRemplacant = $nom;
  }

  public function setPrenom_remplacant($prenom)
  {
      $this->_prenomRemplacant = $prenom;
  }
}
?>
