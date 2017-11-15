<?php

require_once(__DIR__ . '/../managerBase.php');

class LigueManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function creerLigue($idCoach, Ligue $ligue)
  {
    // création de la ligue
    $q = $this->_bdd->prepare('INSERT INTO ligue(nom, etat, libelle_pari, mode_expert,
      bonus_malus, mode_mercato, tour_mercato, date_creation)
      VALUES(:nom, :etat, :libellePari, :modeExpert, :bonusMalus, :modeMercato, 1, NOW())');
    $q->bindValue(':nom', $ligue->nom());
    $q->bindValue(':etat', EtatLigue::CREATION);
    $q->bindValue(':libellePari', $ligue->libellePari());
    $q->bindValue(':modeExpert', $ligue->modeExpert());
    $q->bindValue(':bonusMalus', $ligue->bonusMalus());
    $q->bindValue(':modeMercato', $ligue->modeMercato());

    $q->execute();

    // récupération de l'id
    $idLigue = $this->_bdd->lastInsertId();

    // création du lien avec le coach
    $q = $this->_bdd->prepare('INSERT INTO coach_ligue(id_coach, id_ligue, createur, date_validation)
      VALUES(:idCoach, :idLigue, 1, NOW())');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();

    return $idLigue;
  }

  public function inviterCoachDansLigue($idCoach, $idLigue)
  {
    $q = $this->_bdd->prepare('INSERT INTO coach_ligue(id_coach, id_ligue, createur)
      VALUES(:idCoach, :idLigue, 0)');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();
  }

  public function validerParticipants($idLigue, $tabIdCoach)
  {
    // Suppression des liens non sélectionnés
    $inQuery = implode(',', array_fill(0, count($tabIdCoach), '?'));

    $q = $this->_bdd->prepare('DELETE FROM coach_ligue
      WHERE id_ligue = ? AND createur = 0 AND id_coach NOT IN (' . $inQuery . ')');
    $q->bindValue(1, $idLigue);
    $index=2;
    foreach($tabIdCoach as $cle => $value)
    {
      $q->bindValue($index, $value);
      $index++;
    }

    $q->execute();

    // Maj état ligue
    $this->mettreAJourEtatLigue(EtatLigue::MERCATO, $idLigue);
  }

  public function mettreAJourEtatLigue($etat, $idLigue)
  {
    $q = $this->_bdd->prepare('UPDATE ligue SET etat = :etat WHERE id = :idLigue');
    $q->bindValue(':etat', $etat);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();
  }

  public function accepterInvitationLigue($idCoach, $idLigue)
  {
    $q = $this->_bdd->prepare('UPDATE coach_ligue SET date_validation = NOW()
          WHERE id_coach = :idCoach AND id_ligue = :idLigue');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();
  }

  public function refuserInvitationLigue($idCoach, $idLigue)
  {
    $q = $this->_bdd->prepare('DELETE FROM coach_ligue
        WHERE id_coach = :idCoach AND id_ligue = :idLigue');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();
  }

  public function findLiguesByIdCoach($idCoach)
	{
		$ligues = [];

		// Ligues du coach
		$q = $this->_bdd->prepare('SELECT l.*, c.createur, c.date_validation
						FROM ligue l
						INNER JOIN coach_ligue c ON c.id_ligue = l.id
						WHERE c.id_coach = :id');
		$q->execute([':id' => $idCoach]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$ligues[] = new Ligue($donnees);
		}
		$q->closeCursor();

		return $ligues;
	}

  public function findLigueById($idLigue)
	{
    $q = $this->_bdd->prepare('SELECT * FROM ligue WHERE id = :id');
    $q->execute([':id' => $idLigue]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    return new Ligue($donnees);
	}

  public function findTourMercato($idLigue)
	{
    $q = $this->_bdd->prepare('SELECT tour_mercato FROM ligue WHERE id = :id');
    $q->execute([':id' => $idLigue]);

    return (int) $q->fetchColumn();
	}
}
