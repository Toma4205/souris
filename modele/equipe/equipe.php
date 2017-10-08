<?php

require_once(__DIR__ . '/../classeBase.php');

class Equipe extends ClasseBase
{
  // Champs BDD
  private $_id;
  private $_nom;
  private $_ville;
  private $_stade;
  private $_budgetRestant;
  private $_finMercato;
  private $_nbMatch;
  private $_nbVictoire;
  private $_nbNul;
  private $_nbDefaite;
  private $_nbButPour;
  private $_nbButContre;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function id() { return $this->_id; }
  public function nom() { return $this->_nom; }
  public function ville() { return $this->_ville; }
  public function stade() { return $this->_stade; }
  public function budgetRestant() { return $this->_budgetRestant; }
  public function finMercato() { return $this->_finMercato; }
  public function nbMatch() { return $this->_nbMatch; }
  public function nbVictoire() { return $this->_nbVictoire; }
  public function nbNul() { return $this->_nbNul; }
  public function nbDefaite() { return $this->_nbDefaite; }
  public function nbButPour() { return $this->_nbButPour; }
  public function nbButContre() { return $this->_nbButContre; }

  public function setId($id)
  {
    $this->_id = (int) $id;
  }

  public function setNom($nom)
  {
      $this->_nom = $nom;
  }

  public function setVille($ville)
  {
      $this->_ville = $ville;
  }

  public function setStade($stade)
  {
      $this->_stade = $stade;
  }

  public function setBudget_restant($budgetRestant)
  {
      $this->_budgetRestant = $budgetRestant;
  }

  public function setFin_mercatot($finMercato)
  {
      $this->_finMercato = $finMercato;
  }

    public function setNb_match($nbMatch)
    {
        $this->_nbMatch = $nbMatch;
    }
?>