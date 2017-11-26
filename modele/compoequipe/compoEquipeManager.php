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

  public function creerOuMajCompoEquipe(CompoEquipe $compo, $idEquipe, $idCalLigue)
  {
    if ($compo->id() != null) {
      $q = $this->_bdd->prepare('UPDATE compo_equipe SET code_tactique = :tactique, code_bonus_malus = :bonus
          WHERE id = :id');
      $q->bindValue(':tactique', $compo->codeTactique());
      $q->bindValue(':bonus', $compo->codeBonusMalus());
      $q->bindValue(':id', $compo->id());
    } else {
      $q = $this->_bdd->prepare('INSERT INTO compo_equipe(id_cal_ligue, id_equipe, code_tactique, code_bonus_malus)
          VALUES(:calLigue, :equipe, :tactique, :bonus)');
      $q->bindValue(':calLigue', $idCalLigue);
      $q->bindValue(':equipe', $idEquipe);
      $q->bindValue(':tactique', $compo->codeTactique());
      $q->bindValue(':bonus', $compo->codeBonusMalus());
    }

    $q->execute();
	}
}
