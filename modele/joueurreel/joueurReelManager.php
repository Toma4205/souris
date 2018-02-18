<?php

require_once(__DIR__ . '/../managerBase.php');

class JoueurReelManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function getNoteTemp($idJoueur, $numJournee)
  {
    $q = $this->_bdd->prepare('SELECT js.note
        FROM joueur_stats js
        JOIN joueur_reel jr ON js.id = jr.cle_roto_primaire
        WHERE js.journee = :numJournee
        AND jr.id = :id');
    $q->execute([':id' => $idJoueur, 'numJournee' => $numJournee]);
    return $q->fetchColumn();
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

  public function findJoueurRestantByLigue($idLigue, $tourMercato)
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT j.*, n.libelle as libelleEquipe
        FROM joueur_reel j
        JOIN nomenclature_equipe n ON j.equipe = n.code
        WHERE id NOT IN (SELECT DISTINCT(id_joueur_reel)
          FROM joueur_equipe je
          WHERE id_ligue = :id
          AND tour_mercato < :tour)');
    $q->execute([':id' => $idLigue, ':tour' => $tourMercato]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurReel($donnees);
		}

		$q->closeCursor();

		return $joueurs;
  }
}
