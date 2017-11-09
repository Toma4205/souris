<?php

require_once(__DIR__ . '/../managerBase.php');

class JoueurReelManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findByPosition($position)
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT j.*, n.libelle as libelleEquipe FROM joueur_reel j
        JOIN nomenclature_equipe n ON j.equipe = n.code
        WHERE j.position = :position');
    $q->execute([':position' => $position]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurReel($donnees);
		}

		$q->closeCursor();

		return $joueurs;
	}

  public function findAll()
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT j.*, n.libelle as libelleEquipe FROM joueur_reel j
        JOIN nomenclature_equipe n ON j.equipe = n.code');
    $q->execute();

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurReel($donnees);
		}

		$q->closeCursor();

		return $joueurs;
	}
}
