<?php

require_once(__DIR__ . '/../managerBase.php');

class EquipeManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
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
