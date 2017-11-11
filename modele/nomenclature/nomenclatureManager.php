<?php

require_once(__DIR__ . '/../managerBase.php');

class NomenclatureManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findNomenclatureEquipe()
  {
    $equipes = [];

    $q = $this->_bdd->prepare('SELECT * FROM nomenclature_equipe
      WHERE date_debut > NOW() AND (date_fin IS NULL OR date_fin < NOW())');
    $q->execute();

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$equipes[] = new NomenclatureEquipe($donnees);
		}

		$q->closeCursor();

		return $equipes;
	}
}
