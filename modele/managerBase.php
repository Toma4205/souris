<?php

class ManagerBase
{
  protected $_bdd; // Instance de PDO.

  public function setDb(PDO $bdd)
  {
    $this->_bdd = $bdd;
  }

}
