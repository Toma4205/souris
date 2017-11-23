<?php

require_once(__DIR__ . '/../managerBase.php');

class CoachManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function creerCoach(Coach $coach)
  {
    $q = $this->_bdd->prepare('INSERT INTO coach(nom, mot_de_passe, date_creation, date_maj) VALUES(:nom, :motDePasse, NOW(), NOW())');
    $q->bindValue(':nom', $coach->nom());
    $q->bindValue(':motDePasse', $coach->motDePasse());

    $q->execute();
  }

  public function majCoach(Coach $coach)
  {
    $q = $this->_bdd->prepare('UPDATE coach SET nom = :nom, mail = :mail, code_postal = :code, date_maj = NOW()
        WHERE id = :id');
    $q->bindValue(':nom', $coach->nom());
    $q->bindValue(':mail', $coach->mail());
    $q->bindValue(':code', $coach->codePostal());
    $q->bindValue(':id', $coach->id());

    $q->execute();
  }

  public function findByNomMotDePasse(Coach $coach)
  {
    $q = $this->_bdd->prepare('SELECT *
            FROM coach
            WHERE nom = :nom AND mot_de_passe = :motDePasse');
    $q->execute([':nom' => $coach->nom(), ':motDePasse' => $coach->motDePasse()]);

    // TODO MPL récupération du fil actu et des ligues en même temps

    $donnees = $q->fetch(PDO::FETCH_ASSOC);

    $q->closeCursor();

    // Si le coach n'est pas trouvé
    if (is_bool($donnees))
    {
      return $coach;
    }
    else
    {
      return new Coach($donnees);
    }
  }

  public function existeByNom($nom)
  {
      $q = $this->_bdd->prepare('SELECT COUNT(*) FROM coach WHERE nom = :nom');
      $q->execute([':nom' => $nom]);

      return (bool) $q->fetchColumn();
  }

  public function findByNom($nom, $idCoach)
  {
      $coachs = [];

      $q = $this->_bdd->prepare('SELECT id, nom, code_postal
            FROM coach
            WHERE nom LIKE :nom
            AND id != :id
            AND id NOT IN (SELECT id_coach_confrere FROM confrere WHERE id_coach = :id2)
            ORDER BY nom');
      $q->execute([':nom' => '%' . $nom . '%', ':id' => $idCoach, ':id2' => $idCoach]);

      while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
      {
        $coachs[] = new Coach($donnees);
      }
      $q->closeCursor();

      return $coachs;
  }

  public function findCoachsInvitesByIdLigue($idLigue)
  {
    $coachsInvites = [];

    // Ligues du coach
    $q = $this->_bdd->prepare('SELECT cl.date_validation as date_validation_ligue, c.id, c.nom, c.code_postal
            FROM coach_ligue cl
            JOIN coach c ON cl.id_coach = c.id
            WHERE id_ligue = :id AND createur = FALSE');
    $q->execute([':id' => $idLigue]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $coachsInvites[] = new Coach($donnees);
    }
    $q->closeCursor();

    return $coachsInvites;
  }

  public function compterCoachByLigue($idLigue)
  {
    $q = $this->_bdd->prepare('SELECT COUNT(*) FROM coach_ligue WHERE id_ligue = :id');
    $q->execute([':id' => $idLigue]);

    return $q->fetchColumn();
  }

  public function count()
  {
    return $this->_bdd->query('SELECT COUNT(*) FROM coach')->fetchColumn();
  }
}
