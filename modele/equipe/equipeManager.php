<?php

require_once(__DIR__ . '/../managerBase.php');

class EquipeManager extends MangerBase
{

  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

}
