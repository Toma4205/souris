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
    $q = $this->_bdd->prepare('INSERT INTO ligue(nom, etat, libelle_pari, mode_expert, nb_equipe, date_creation)
      VALUES(:nom, :etat, :libellePari, :modeExpert, :nbEquipe, NOW())');
    $q->bindValue(':nom', $ligue->nom());
    $q->bindValue(':etat', EtatLigue::CREATION);
    $q->bindValue(':libellePari', $ligue->libellePari());
    $q->bindValue(':modeExpert', $ligue->modeExpertBool());
    $q->bindValue(':nbEquipe', $ligue->nbEquipe());

    $q->execute();

    // récupération de l'id
    $idLigue = $this->_bdd->lastInsertId();

    // création du lien avec le coach
    $q = $this->_bdd->prepare('INSERT INTO coach_ligue(id_coach, id_ligue, createur, date_validation)
      VALUES(:idCoach, :idLigue, TRUE, NOW())');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();

    return $idLigue;
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
}
