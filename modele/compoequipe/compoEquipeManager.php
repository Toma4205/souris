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
    $q = $this->_bdd->prepare('SELECT ce.*, bm.id_joueur_reel_equipe as idJoueurReelEquipe,
      bm.id_joueur_reel_adverse as idJoueurReelAdverse, jr.nom as nomJoueurReelEquipe,
      jr2.nom as nomJoueurReelAdverse, n.libelle_court as libCourtBonusMalus,
      n.libelle as libLongBonusMalus
      FROM compo_equipe ce
      LEFT JOIN bonus_malus bm ON bm.id_equipe = ce.id_equipe AND bm.id_cal_ligue = ce.id_cal_ligue
      LEFT JOIN nomenclature_bonus_malus n ON n.code = ce.code_bonus_malus
      LEFT JOIN joueur_reel jr ON jr.id = bm.id_joueur_reel_equipe
      LEFT JOIN joueur_reel jr2 ON jr2.id = bm.id_joueur_reel_adverse
      WHERE ce.id_equipe = :idEquipe AND ce.id_cal_ligue = :idCalLigue');
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

    $q = $this->_bdd->prepare('SELECT jce.*, jr.nom, jr.position, jr2.nom as nom_remplacant,
      jr2.prenom as prenom_remplacant, jr.equipe as code_equipe
      FROM joueur_compo_equipe  jce
      JOIN joueur_reel jr ON jr.id = jce.id_joueur_reel
      LEFT JOIN joueur_reel jr2 ON jr2.id = jce.id_joueur_reel_remplacant
      WHERE jce.id_compo = :id ORDER BY jce.numero');
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

    $q = $this->_bdd->prepare('INSERT INTO compo_equipe(id_cal_ligue, id_equipe, code_tactique,
        code_bonus_malus, pari_dom, pari_ext)
        VALUES(:calLigue, :equipe, :tactique, :bonus, :dom, :ext)');
    $q->bindValue(':calLigue', $idCalLigue);
    $q->bindValue(':equipe', $idEquipe);
    $q->bindValue(':tactique', $compo->codeTactique());
    $q->bindValue(':bonus', $compo->codeBonusMalus());
    $q->bindValue(':dom', $compo->pariDom());
    $q->bindValue(':ext', $compo->pariExt());
    $q->execute();
	}

  public function purgerJoueurCompoEquipe($idCompo)
  {
    $q = $this->_bdd->prepare('DELETE FROM joueur_compo_equipe WHERE id_compo = :id');
    $q->bindValue(':id', $idCompo);

    $q->execute();
	}

  public function creerJoueurCompoEquipe($idCompo, $numero, $idJoueur, $capitaine)
  {
    $q = $this->_bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine)
        VALUES(:idCompo, :idJoueur, :num, :cap)');
    $q->bindValue(':idCompo', $idCompo);
    $q->bindValue(':idJoueur', $idJoueur);
    $q->bindValue(':num', $numero);
    $q->bindValue(':cap', $capitaine);

    $q->execute();
	}

  public function creerJoueurCompoEquipeAvecRempl($idCompo, $numero, $idJoueur, $capitaine, $numRempl, $idRempl, $note)
  {
    $q = $this->_bdd->prepare('INSERT INTO joueur_compo_equipe(id_compo, id_joueur_reel, numero, capitaine,
        numero_remplacement, id_joueur_reel_remplacant, note_min_remplacement)
        VALUES(:idCompo, :idJoueur, :num, :cap, :rempl, :idRempl, :note)');
    $q->bindValue(':idCompo', $idCompo);
    $q->bindValue(':idJoueur', $idJoueur);
    $q->bindValue(':num', $numero);
    $q->bindValue(':cap', $capitaine);
    $q->bindValue(':rempl', $numRempl);
    $q->bindValue(':idRempl', $idRempl);
    $q->bindValue(':note', $note);

    $q->execute();
  }
}
