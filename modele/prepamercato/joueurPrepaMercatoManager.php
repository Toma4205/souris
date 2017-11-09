<?php

require_once(__DIR__ . '/../managerBase.php');

class JoueurPrepaMercatoManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function purgerMercatoCoach($idCoach)
  {
    $q = $this->_bdd->prepare('DELETE FROM prepa_mercato WHERE id_coach = :idCoach');
    $q->bindValue(':idCoach', $idCoach);

    $q->execute();
  }

  public function creerJoueurPrepaMercato($idCoach, $idJoueur, int $prix)
  {
    $q = $this->_bdd->prepare('INSERT INTO prepa_mercato(id_coach, id_joueur_reel, prix) VALUES(:idCoach, :idJoueur, :prix)');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idJoueur', $idJoueur);
    $q->bindValue(':prix', $prix);

    $q->execute();
  }

  public function findByCoach($idCoach)
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT p.prix as prixAchat, j.id,
        j.nom, j.prenom, j.equipe, j.position, j.prix as prixOrigine FROM prepa_mercato p
        JOIN joueur_reel j ON p.id_joueur_reel = j.id
        WHERE p.id_coach = :id');
    $q->execute([':id' => $idCoach]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurPrepaMercato($donnees);
		}

		$q->closeCursor();

		return $joueurs;
	}
}
