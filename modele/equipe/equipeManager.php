<?php

require_once(__DIR__ . '/../managerBase.php');

class EquipeManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function creerEquipe(Equipe $equipe, $idCoach, $idLigue)
  {
		$q = $this->_bdd->prepare('INSERT INTO equipe(id_coach, id_ligue, nom, ville, stade, budget_restant,
        fin_mercato, nb_match, nb_victoire, nb_nul, nb_defaite, nb_but_pour, nb_but_contre)
        VALUES(:idCoach, :idLigue, :nom, :ville, :stade, :budget, 0, 0, 0, 0, 0, 0, 0)');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);
    $q->bindValue(':nom', $equipe->nom());
    $q->bindValue(':ville', $equipe->ville());
    $q->bindValue(':stade', $equipe->stade());
    $q->bindValue(':budget', ConstantesAppli::BUDGET_INIT);

    $q->execute();
	}

  public function findEquipeByCoachEtLigue($idCoach, $idLigue)
  {
    $q = $this->_bdd->prepare('SELECT * FROM equipe WHERE id_coach = :idCoach AND id_ligue = :idLigue');
    $q->execute([':idCoach' => $idCoach, ':idLigue' => $idLigue]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    // Si l'équipe n'est pas trouvée
    if (is_bool($donnees))
    {
      return null;
    }
    else
    {
      return new Equipe($donnees);
    }
	}
}
