<?php

require_once(__DIR__ . '/../managerBase.php');

class EquipeManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function majBudgetRestant($idLigue)
  {
    $q = $this->_bdd->prepare('SELECT SUM(je.prix) as somme, je.id_equipe
      FROM joueur_equipe je
      JOIN equipe e ON e.id = je.id_equipe
      WHERE e.id_ligue = :id AND je.date_validation IS NOT NULL
      GROUP BY je.id_equipe');
    $q->execute(['id' => $idLigue]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $q2 = $this->_bdd->prepare('UPDATE equipe SET budget_restant = :budget WHERE id = :id');
      $q2->bindValue(':id', $donnees['id_equipe']);
      $q2->bindValue(':budget', ConstantesAppli::BUDGET_INIT - $donnees['somme']);
      $q2->execute();
    }
  }

  public function findEquipeEnAttenteMercato($idLigue, $tour)
  {
    $equipes = [];
    $q = $this->_bdd->prepare('SELECT c.nom as nom_coach, e.nom
      FROM coach c
      JOIN coach_ligue cl ON cl.id_coach = c.id AND cl.id_ligue = :id
      LEFT JOIN equipe e ON e.id_coach = c.id AND e.id_ligue = :id
      WHERE e.nom IS NULL OR (e.fin_mercato = FALSE AND e.id NOT IN (
        SELECT DISTINCT(je.id_equipe)
        FROM joueur_equipe je
        WHERE je.id_ligue = :id
        AND je.tour_mercato = :tour
      ))');
    $q->execute([':id' => $idLigue, ':tour' => $tour]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $equipes[] = new Equipe($donnees);
    }

    $q->closeCursor();

    return $equipes;
  }

  public function creerEquipe(Equipe $equipe, $idCoach, $idLigue, $bonusMalus, $nbEquipe)
  {
		$q = $this->_bdd->prepare('INSERT INTO equipe(id_coach, id_ligue, nom, ville, stade, code_style_coach, budget_restant,
        fin_mercato, nb_match, nb_victoire, nb_nul, nb_defaite, nb_but_pour, nb_but_contre, nb_bonus, nb_malus)
        VALUES(:idCoach, :idLigue, :nom, :ville, :stade, :styleCoach, :budget, 0, 0, 0, 0, 0, 0, 0, 0, 0)');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);
    $q->bindValue(':nom', $equipe->nom());
    $q->bindValue(':ville', $equipe->ville());
    $q->bindValue(':stade', $equipe->stade());
    $q->bindValue(':styleCoach', $equipe->codeStyleCoach());
    $q->bindValue(':budget', ConstantesAppli::BUDGET_INIT);

    $q->execute();

    if (ConstantesAppli::BONUS_MALUS_CLASSIQUE == $bonusMalus
      || ConstantesAppli::BONUS_MALUS_FOLIE == $bonusMalus) {
      // récupération de l'id
      $idEquipe = $this->_bdd->lastInsertId();

      $bonusManager = new BonusMalusManager($this->db());
      $bonusManager->creerBonusMalusEquipe($idEquipe, $bonusMalus, $nbEquipe);
    } else if (ConstantesAppli::BONUS_MALUS_PERSO == $bonusMalus) {
      // récupération de l'id
      $idEquipe = $this->_bdd->lastInsertId();

      $bonusManager = new BonusMalusManager($this->db());
      $bonusManager->creerBonusMalusPersoEquipe($idEquipe, $idLigue);
    }
	}

  public function fermerMercato($id)
  {
    $q = $this->_bdd->prepare('UPDATE equipe SET fin_mercato = TRUE WHERE id = :id');
    $q->bindValue(':id', $id);

    $q->execute();
  }

  public function isTousMercatoFerme($idLigue)
  {
    $q = $this->_bdd->prepare('SELECT * FROM equipe WHERE id_ligue = :idLigue AND fin_mercato = FALSE');
    $q->execute([':idLigue' => $idLigue]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    // Si toutes les équipes ont fermé leur mercato
    if (is_bool($donnees))
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  public function findEquipeById($idEquipe)
  {
    $q = $this->_bdd->prepare('SELECT e.*, c.nom as nom_coach
      FROM equipe e
      JOIN coach c ON c.id = e.id_coach
      WHERE e.id = :id');
    $q->execute([':id' => $idEquipe]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    return new Equipe($donnees);
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

  public function findIdEquipeByLigue($idLigue)
  {
      $equipes = [];
      $q = $this->_bdd->prepare('SELECT id FROM equipe WHERE id_ligue = :idLigue');
      $q->execute([':idLigue' => $idLigue]);

      while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  		{
  			$equipes[] = $donnees['id'];
  		}

  		$q->closeCursor();

      return $equipes;
  }

  public function findEquipeByLigue($idLigue)
  {
      $equipes = [];
      $q = $this->_bdd->prepare('SELECT * FROM equipe WHERE id_ligue = :idLigue ORDER BY classement ASC');
      $q->execute([':idLigue' => $idLigue]);

      while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    	{
    		$equipes[$donnees['id']] = new Equipe($donnees);
    	}

    	$q->closeCursor();

      return $equipes;
  }
}
