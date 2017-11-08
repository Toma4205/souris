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

		$q = $this->_bdd->prepare('SELECT * FROM joueur_reel
        WHERE position = :position');
    $q->execute([':position' => $position]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurReel($donnees);
		}

		$q->closeCursor();

		return $joueurs;
	}
}
