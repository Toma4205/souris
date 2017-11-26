<?php

require_once(__DIR__ . '/../classeBase.php');

class NomenclatureTactique extends ClasseBase
{
  // Champs BDD
  private $_code;
  private $_nbDef;
  private $_nbMil;
  private $_nbAtt;
  private $_nbDc;
  private $_nbDlg;
  private $_nbDld;
  private $_nbMdef;
  private $_nbMc;
  private $_nbMg;
  private $_nbMd;
  private $_nbMoff;
  private $_nbAilg;
  private $_nbAild;
  private $_nbBut;

  public function __construct(array $donnees)
  {
    parent::hydrate($donnees);
  }

  public function code() { return $this->_code; }
  public function nbDef() { return $this->_nbDef; }
  public function nbMil() { return $this->_nbMil; }
  public function nbAtt() { return $this->_nbAtt; }
  public function nbDc() { return $this->_nbDc; }
  public function nbDlg() { return $this->_nbDlg; }
  public function nbDld() { return $this->_nbDld; }
  public function nbMdef() { return $this->_nbMdef; }
  public function nbMc() { return $this->_nbMc; }
  public function nbMg() { return $this->_nbMg; }
  public function nbMd() { return $this->_nbMd; }
  public function nbMoff() { return $this->_nbMoff; }
  public function nbAilg() { return $this->_nbAilg; }
  public function nbAild() { return $this->_nbAild; }
  public function nbBut() { return $this->_nbBut; }

  public function setCode($code)
  {
    $this->_code = $code;
  }

  public function setNb_def($nbDef)
  {
      $this->_nbDef = $nbDef;
  }

  public function setNb_mil($nbMil)
  {
      $this->_nbMil = $nbMil;
  }

  public function setNb_att($nbAtt)
  {
      $this->_nbAtt = $nbAtt;
  }

  public function setNb_dc($nbDc)
  {
      $this->_nbDc = $nbDc;
  }

  public function setNb_dlg($nbDlg)
  {
      $this->_nbDlg = $nbDlg;
  }

  public function setNb_dld($nbDld)
  {
      $this->_nbDld = $nbDld;
  }

  public function setNb_mdef($nbMdef)
  {
      $this->_nbMdef = $nbMdef;
  }

  public function setNb_mc($nbMc)
  {
      $this->_nbMc = $nbMc;
  }

  public function setNb_mg($nbMg)
  {
      $this->_nbMg = $nbMg;
  }

  public function setNb_md($nbMd)
  {
      $this->_nbMd = $nbMd;
  }

  public function setNb_moff($nbMoff)
  {
      $this->_nbMoff = $nbMoff;
  }

  public function setNb_ailg($nbAilg)
  {
      $this->_nbAilg = $nbAilg;
  }

  public function setNb_aild($nbAild)
  {
      $this->_nbAild = $nbAild;
  }

  public function setNb_but($nbBut)
  {
      $this->_nbBut = $nbBut;
  }
}
?>
