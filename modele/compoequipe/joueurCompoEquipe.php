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
  private $_numeroRemplacant;
  private $_noteMin;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function idJoueurReel() { return $this->_idJoueurReel; }
  public function numero() { return $this->_numero; }
  public function capitaine() { return $this->_capitaine; }
  public function codeBonusMalus() { return $this->_codeBonusMalus; }
  public function note() { return $this->_note; }
  public function numeroRemplacant() { return $this->_numeroRemplacant; }
  public function noteMin() { return $this->_noteMin; }

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

  public function setNumero_remplacant($numeroRemplacant)
  {
      $this->_numeroRemplacant = $numeroRemplacant;
  }

  public function setNote_min($noteMin)
  {
      $this->_noteMin = $noteMin;
  }
}
?>
