<?php

require_once(__DIR__ . '/../classeBase.php');

class Ligue extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_nom;
  private $_etat;
  private $_libellePari;
  private $_modeExpert;
  private $_bonusMalus;
  private $_modeMercato;
  private $_tourMercato;
  private $_dateCreation;

  // Champs de coach_ligue
  private $_createur;
  private $_dateValidation;

  // Champs de coach
  private $_nomCoachCreateur;

  // Champs de equipe
  private $_classement;

  // Champs de calendrier_ligue
  private $_dom; // Détermine si l'équipe du coach est à domicile
  private $_scoreDom;
  private $_scoreExt;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function etat() { return $this->_etat; }
  public function libellePari() { return $this->_libellePari; }
  public function modeExpert() { return $this->_modeExpert; }
  public function bonusMalus() { return $this->_bonusMalus; }
  public function modeMercato() { return $this->_modeMercato; }
  public function tourMercato() { return $this->_tourMercato; }
  public function dateCreation() { return $this->_dateCreation; }
  public function createur() { return $this->_createur; }
  public function dateValidation() { return $this->_dateValidation; }
  public function nomCoachCreateur() { return $this->_nomCoachCreateur; }
  public function classement() { return $this->_classement; }
  public function scoreDom() { return $this->_scoreDom; }
  public function dom() { return $this->_dom; }
  public function scoreExt() { return $this->_scoreExt; }

  public function setId($id)
  {
    $this->_id = (int) $id;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setEtat($etat)
  {
      $this->_etat = $etat;
  }

  public function setLibelle_pari($libellePari)
  {
      $this->_libellePari = $libellePari;
  }

  public function setMode_expert($modeExpert)
  {
      $this->_modeExpert = $modeExpert;
  }

  public function setBonus_malus($bonusMalus)
  {
      $this->_bonusMalus = $bonusMalus;
  }

  public function setMode_mercato($modeMercato)
  {
      $this->_modeMercato = $modeMercato;
  }

  public function setTour_mercato($tourMercato)
  {
      $this->_tourMercato = $tourMercato;
  }

  public function setDate_creation($dateCreation)
  {
      $this->_dateCreation = $dateCreation;
  }

  public function setCreateur($createur)
  {
      $this->_createur = $createur;
  }

  public function setDate_validation($dateValidation)
  {
      $this->_dateValidation = $dateValidation;
  }

  public function setNomCoachCreateur($nomCoachCreateur)
  {
    $this->_nomCoachCreateur = $nomCoachCreateur;
  }

  public function setClassement($classement)
  {
    $this->_classement = (int) $classement;
  }

  public function setDom($dom)
  {
    $this->_dom = $dom;
  }

  public function setScoreDom($scoreDom)
  {
    $this->_scoreDom = $scoreDom;
  }

  public function setScoreExt($scoreExt)
  {
    $this->_scoreExt = $scoreExt;
  }
}
