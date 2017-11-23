<?php

require_once(__DIR__ . '/../managerBase.php');

class JoueurEquipeManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function isTourMercatoValide($idEquipe, int $tourMercato)
  {
    $q = $this->_bdd->prepare('SELECT COUNT(*) FROM joueur_equipe
      WHERE id_equipe = :id AND tour_mercato = :tour');
    $q->execute([':id' => $idEquipe, ':tour' => $tourMercato]);

    return ((int) $q->fetchColumn()) > 0;
  }

  public function purgerJoueurNonAchete($idEquipe)
  {
    $q = $this->_bdd->prepare('DELETE FROM joueur_equipe WHERE id_equipe = :idEquipe');
    $q->bindValue(':idEquipe', $idEquipe);

    $q->execute();
  }

  public function creerJoueurEquipe($idLigue, $idEquipe, $idJoueur, int $prix, int $tourMercato)
  {
    $q = $this->_bdd->prepare('INSERT INTO joueur_equipe(id_ligue, id_equipe, id_joueur_reel, prix,
      tour_mercato, date_offre, nb_but_reel, nb_but_virtuel, nb_match)
      VALUES(:idLigue, :idEquipe, :idJoueur, :prix, :tour, NOW(), 0, 0, 0)');
    $q->bindValue(':idLigue', $idLigue);
    $q->bindValue(':idEquipe', $idEquipe);
    $q->bindValue(':idJoueur', $idJoueur);
    $q->bindValue(':prix', $prix);
    $q->bindValue(':tour', $tourMercato);

    $q->execute();
  }

  public function findByEquipe($idEquipe)
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT je.prix as prixAchat, j.id,
        j.nom, j.prenom, j.position, j.prix as prixOrigine, n.libelle as libelleEquipe
        FROM joueur_equipe je
        JOIN joueur_reel j ON je.id_joueur_reel = j.id
        JOIN nomenclature_equipe n ON j.equipe = n.code
        WHERE je.id_equipe = :id
        AND je.date_validation IS NOT NULL
        ORDER BY j.nom, j.prenom');
    $q->execute([':id' => $idEquipe]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurEquipe($donnees);
		}

		$q->closeCursor();

		return $joueurs;
	}

  public function findMercatoEnCoursByEquipe($idEquipe, $tourMercato)
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT je.prix as prixAchat, j.id,
        j.nom, j.prenom, j.position, j.prix as prixOrigine, n.libelle as libelleEquipe
        FROM joueur_equipe je
        JOIN joueur_reel j ON je.id_joueur_reel = j.id
        JOIN nomenclature_equipe n ON j.equipe = n.code
        WHERE je.id_equipe = :id
        AND tour_mercato = :tour
        ORDER BY j.nom, j.prenom');
    $q->execute([':id' => $idEquipe, ':tour' => $tourMercato]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurEquipe($donnees);
		}

		$q->closeCursor();

		return $joueurs;
	}

  public function isTourMercatoTermine($idLigue, $tourMercato)
  {
    $q = $this->_bdd->prepare('SELECT cl.* FROM coach_ligue cl
      WHERE cl.id_ligue = :id
      AND NOT EXISTS (
        SELECT e.id FROM equipe e WHERE e.id_coach = cl.id_coach AND e.id_ligue = :id)');
    $q->execute([':id' => $idLigue]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    // Si tous les coachs ont créé leur équipe
    if (is_bool($donnees)) {
      $q = $this->_bdd->prepare('SELECT e.* FROM equipe e
        JOIN joueur_equipe j ON e.id_ligue = j.id_ligue
        WHERE e.id_ligue = :id
        AND e.fin_mercato = FALSE
        AND e.id NOT IN (SELECT DISTINCT(j.id_equipe)
          FROM joueur_equipe j
          WHERE j.id_ligue = e.id_ligue
          AND j.tour_mercato = :tour)');
      $q->execute([':id' => $idLigue, ':tour' => $tourMercato]);

      $donnees = $q->fetch(PDO::FETCH_ASSOC);
      $q->closeCursor();

      // Si aucune équipe trouvée
      if (is_bool($donnees)) {
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return FALSE;
    }
  }

  public function affecterJoueurAEquipe($idLigue, $tourMercato)
  {
    $q = $this->_bdd->prepare('UPDATE joueur_equipe j6
      INNER JOIN (SELECT j0.id_joueur_reel, j0.id_equipe FROM joueur_equipe j0
        INNER JOIN (SELECT j1.id_joueur_reel, MIN(j1.date_offre) as date_offre, j1.prix as prix
          FROM joueur_equipe j1
          INNER JOIN (SELECT j2.id_joueur_reel, MAX(j2.prix) as prix
            FROM joueur_equipe j2
            WHERE j2.id_ligue = :id
            AND j2.tour_mercato = :tour
            GROUP BY j2.id_joueur_reel ) AS j3
          ON j3.id_joueur_reel = j1.id_joueur_reel AND j3.prix = j1.prix
          WHERE j1.id_ligue = :id
          AND j1.tour_mercato = :tour
          GROUP BY j1.id_joueur_reel) as j4
        ON j0.id_joueur_reel = j4.id_joueur_reel
        AND j0.date_offre = j4.date_offre
        AND j0.prix = j4.prix
        WHERE j0.id_ligue = :id
        AND j0.tour_mercato = :tour) as j5
      ON j5.id_joueur_reel = j6.id_joueur_reel
      AND j5.id_equipe = j6.id_equipe
      SET j6.date_validation = NOW()');
    $q->bindValue(':id', $idLigue);
    $q->bindValue(':tour', $tourMercato);

    $q->execute();

    $q = $this->_bdd->prepare('UPDATE ligue SET tour_mercato = tour_mercato + 1
      WHERE id = :id');
    $q->bindValue(':id', $idLigue);

    $q->execute();
  }

  public function findJoueurTourMercatoTermine($idLigue, $tourMercato)
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT je.prix as prixAchat, je.tour_mercato,
        je.date_offre, je.date_validation, j.id, j.nom, j.prenom, j.position,
        n.libelle as libelleEquipe, e.nom as nomEquipe
        FROM joueur_equipe je
        JOIN equipe e ON je.id_equipe = e.id
        JOIN joueur_reel j ON je.id_joueur_reel = j.id
        JOIN nomenclature_equipe n ON j.equipe = n.code
        WHERE je.id_ligue = :id
        AND je.tour_mercato < :tour
        ORDER BY je.prix DESC');
    $q->execute([':id' => $idLigue, ':tour' => $tourMercato]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurEquipe($donnees);
		}

		$q->closeCursor();

		return $joueurs;
  }

  public function findButeurByLigue($idLigue)
  {
    $joueurs = [];

		$q = $this->_bdd->prepare('SELECT je.prix as prixAchat, je.tour_mercato,
        (je.nb_but_reel + je.nb_but_virtuel) as totalBut,
        je.nb_but_reel, je.nb_but_virtuel, je.nb_match, j.nom, j.prenom,
        e.nom as nomEquipe
        FROM joueur_equipe je
        JOIN equipe e ON je.id_equipe = e.id
        JOIN joueur_reel j ON je.id_joueur_reel = j.id
        WHERE je.id_ligue = :id
        AND (je.nb_but_reel > 0 OR je.nb_but_virtuel > 0)
        ORDER BY totalBut DESC, je.nb_but_reel DESC, je.nb_but_virtuel DESC');
    $q->execute([':id' => $idLigue]);

		while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
		{
			$joueurs[] = new JoueurEquipe($donnees);
		}

		$q->closeCursor();

		return $joueurs;
  }
}
