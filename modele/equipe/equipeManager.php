<?php

require_once('/../managerBase.php');

class EquipeManager extends MangerBase
{

  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

}
