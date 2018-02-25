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

  public final function supprimerLigue($idLigue) {
		$q = $this->_bdd->prepare('DELETE FROM ligue WHERE id = :id');
		$q->bindValue(':id', $idLigue);

		$q->execute();
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

  public function findLiguesEnCoursByIdCoach($idCoach, $numJourneeEnCours)
  {
    $ligues = [];

		// Ligues du coach
		$q = $this->_bdd->prepare('SELECT l.*, cl.createur, cl.date_validation, e.classement, (cal.id_equipe_dom = e.id) as dom,
            c.nom as nomCoachCreateur, cal.score_dom as scoreDom, cal.score_ext as scoreExt
						FROM ligue l
						INNER JOIN coach_ligue cl ON cl.id_ligue = l.id
            LEFT JOIN coach c ON c.id = (SELECT cl2.id_coach FROM coach_ligue cl2 WHERE cl2.id_ligue = cl.id_ligue AND cl2.createur = TRUE)
            LEFT JOIN equipe e ON e.id_ligue = l.id AND e.id_coach = :id
            LEFT JOIN calendrier_ligue cal ON cal.id_ligue = l.id AND num_journee_cal_reel = :numJournee AND (id_equipe_dom = e.id OR id_equipe_ext = e.id)
						WHERE cl.id_coach = :id
            AND (cl.masquee = FALSE OR c.aff_ligue_masquee = TRUE)');
		$q->execute([':id' => $idCoach, ':numJournee' => $numJourneeEnCours]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$ligues[] = new Ligue($donnees);
		}
		$q->closeCursor();

		return $ligues;
  }

  public function findLiguesByIdCoach($idCoach)
	{
		$ligues = [];

		// Ligues du coach
		$q = $this->_bdd->prepare('SELECT l.*, cl.createur, cl.date_validation, e.classement, c.nom as nomCoachCreateur
						FROM ligue l
						INNER JOIN coach_ligue cl ON cl.id_ligue = l.id
            LEFT JOIN coach c ON c.id = (SELECT cl2.id_coach FROM coach_ligue cl2 WHERE cl2.id_ligue = cl.id_ligue AND cl2.createur = TRUE)
            LEFT JOIN equipe e ON e.id_ligue = l.id AND e.id_coach = :id
						WHERE cl.id_coach = :id
            AND (cl.masquee = FALSE OR c.aff_ligue_masquee = TRUE)');
		$q->execute([':id' => $idCoach]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$ligues[] = new Ligue($donnees);
		}
		$q->closeCursor();

		return $ligues;
	}

  public function masquerLigue($idLigue, $idCoach)
  {
    $q = $this->_bdd->prepare('UPDATE coach_ligue SET masquee = TRUE
          WHERE id_coach = :idCoach AND id_ligue = :idLigue');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();
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
