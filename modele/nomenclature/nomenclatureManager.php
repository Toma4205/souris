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
    $nomencls = [];
    $q = $this->_bdd->prepare('SELECT * FROM nomenclature_equipe
      WHERE date_debut < NOW() AND (date_fin IS NULL OR date_fin > NOW())');
    $q->execute();

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$nomencls[] = new NomenclatureEquipe($donnees);
		}
		$q->closeCursor();

    return $nomencls;
	}

  public function findNomenclatureBonusMalus()
  {
    $nomencls = [];
    $q = $this->_bdd->prepare('SELECT * FROM nomenclature_bonus_malus
      WHERE date_debut < NOW() AND (date_fin IS NULL OR date_fin > NOW())');
    $q->execute();

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$nomencls[] = new NomenclatureBonusMalus($donnees);
		}
		$q->closeCursor();

    return $nomencls;
	}

  public function findNomenclatureTactiqueSelonMode($modeExpert)
  {
    $nomencls = [];

    if ($modeExpert == TRUE)
    {
      $q = $this->_bdd->prepare('SELECT * FROM nomenclature_tactique
        WHERE date_debut < NOW() AND (date_fin IS NULL OR date_fin > NOW())
        ORDER BY code');
    }
    else
    {
      $q = $this->_bdd->prepare('SELECT * FROM nomenclature_tactique
        WHERE date_debut < NOW() AND (date_fin IS NULL OR date_fin > NOW())
        ORDER BY nb_def, nb_mil, nb_att');
    }

    $q->execute();
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$nomencls[] = new NomenclatureTactique($donnees);
		}
		$q->closeCursor();

		return $nomencls;
  }

  public function findNomenclatureTactiqueByCode($code)
  {
    $q = $this->_bdd->prepare('SELECT * FROM nomenclature_tactique
      WHERE code = :code');
    $q->execute([':code' => $code]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    return new NomenclatureTactique($donnees);
  }

  public function findNomenclatureStyleCoach()
  {
    $nomencls = [];
    $q = $this->_bdd->prepare('SELECT * FROM nomenclature_style_coach
      WHERE date_debut < NOW() AND (date_fin IS NULL OR date_fin > NOW())');
    $q->execute();

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$nomencls[] = new NomenclatureStyleCoach($donnees);
		}
		$q->closeCursor();

    return $nomencls;
	}
}
