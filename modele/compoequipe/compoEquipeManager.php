<?php

require_once(__DIR__ . '/../managerBase.php');

class CompoEquipeManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findCompoByEquipeEtCalLigue($idEquipe, $idCalLigue)
  {
    $q = $this->_bdd->prepare('SELECT * FROM compo_equipe WHERE id_equipe = :idEquipe AND id_cal_ligue = :idCalLigue');
    $q->execute([':idEquipe' => $idEquipe, ':idCalLigue' => $idCalLigue]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    // Si la compo n'est pas trouvée
    if (is_bool($donnees))
    {
      return null;
    }
    else
    {
      return new CompoEquipe($donnees);
    }
	}

  public function findJoueurCompoByCompo($idCompo)
  {
    $joueurs = [];

    $q = $this->_bdd->prepare('SELECT * FROM joueur_compo_equipe WHERE id_compo = :id');
    $q->execute([':id' => $idCompo]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurCompoEquipe($donnees);
		}
		$q->closeCursor();

		return $joueurs;
  }

  public function creerOuMajCompoEquipe(CompoEquipe $compo, $idEquipe, $idCalLigue)
  {
    $q = $this->_bdd->prepare('DELETE FROM compo_equipe WHERE id_cal_ligue = :calLigue AND id_equipe = :equipe');
    $q->bindValue(':calLigue', $idCalLigue);
    $q->bindValue(':equipe', $idEquipe);
    $q->execute();

    $q = $this->_bdd->prepare('INSERT INTO compo_equipe(id_cal_ligue, id_equipe, code_tactique, code_bonus_malus)
        VALUES(:calLigue, :equipe, :tactique, :bonus)');
    $q->bindValue(':calLigue', $idCalLigue);
    $q->bindValue(':equipe', $idEquipe);
    $q->bindValue(':tactique', $compo->codeTactique());
    $q->bindValue(':bonus', $compo->codeBonusMalus());
    $q->execute();
	}

  public function purgerJoueurCompoEquipe($idCompo)
  {
    $q = $this->_bdd->prepare('DELETE FROM joueur_compo_equipe WHERE id_compo = :id');
    $q->bindValue(':id', $idCompo);

    $q->execute();
	}

  public function creerJoueurCompoEquipe($idCompo, $numero, $idJoueur, $capitaine, $bonus)
  {
    $q = $this->_bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine, code_bonus_malus)
        VALUES(:idCompo, :idJoueur, :num, :cap, :bonus)');
    $q->bindValue(':idCompo', $idCompo);
    $q->bindValue(':idJoueur', $idJoueur);
    $q->bindValue(':num', $numero);
    $q->bindValue(':cap', $capitaine);
    $q->bindValue(':bonus', $bonus);

    $q->execute();
	}

  public function creerJoueurCompoEquipeAvecRempl($idCompo, $numero, $idJoueur, $capitaine, $bonus, $numRempl, $idRempl, $note)
  {
    $q = $this->_bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine,
        code_bonus_malus, numero_remplacement, id_joueur_reel_remplacant, note_min_remplacement)
        VALUES(:idCompo, :idJoueur, :num, :cap, :bonus, :rempl, :idRempl, :note)');
    $q->bindValue(':idCompo', $idCompo);
    $q->bindValue(':idJoueur', $idJoueur);
    $q->bindValue(':num', $numero);
    $q->bindValue(':cap', $capitaine);
    $q->bindValue(':bonus', $bonus);
    $q->bindValue(':rempl', $numRempl);
    $q->bindValue(':idRempl', $idRempl);
    $q->bindValue(':note', $note);

    $q->execute();
  }
}
