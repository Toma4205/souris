<?php

require_once(__DIR__ . '/../classeBase.php');

class JoueurCompoEquipe extends ClasseBase
{
  // Champs BDD
  private $_idJoueurReel;
  private $_numero;
  private $_capitaine;
  private $_codeBonusMalus;
  private $_note;
  private $_numeroRemplacement;
  private $_idJoueurReelRemplacant;
  private $_noteMinRemplacement;

  // Champs table joueur_reel
  private $_nom;
  private $_prenom;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function idJoueurReel() { return $this->_idJoueurReel; }
  public function numero() { return $this->_numero; }
  public function capitaine() { return $this->_capitaine; }
  public function codeBonusMalus() { return $this->_codeBonusMalus; }
  public function note() { return $this->_note; }
  public function numeroRemplacement() { return $this->_numeroRemplacement; }
  public function idJoueurReelRemplacant() { return $this->_idJoueurReelRemplacant; }
  public function noteMinRemplacement() { return $this->_noteMinRemplacement; }
  public function nom() { return $this->_nom; }
  public function prenom() { return $this->_prenom; }

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

  public function setCode_bonus_malus($codeBonusMalus)
  {
      $this->_codeBonusMalus = $codeBonusMalus;
  }

  public function setNote($note)
  {
      $this->_note = $note;
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

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setPrenom($prenom)
  {
      $this->_prenom = $prenom;
  }
}
?>
