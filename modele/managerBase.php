<?php

class ManagerBase
{
  protected $_bdd; // Instance de PDO.

  public function db() { return $this->_bdd; }

  public function setDb(PDO $bdd)
  {
    $this->_bdd = $bdd;
  }

}
?>
