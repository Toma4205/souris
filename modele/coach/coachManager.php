<?php

class CoachManager
{
  private $_db; // Instance de PDO.

  public function __construct($db)
  {
    $this->setDb($db);
  }

  public function creerCoach(Coach $coach)
  {
    $q = $this->_db->prepare('INSERT INTO coach(nom, mot_de_passe, date_creation) VALUES(:nom, :motDePasse, NOW())');
    $q->bindValue(':nom', $coach->nom());
    $q->bindValue(':motDePasse', $coach->motDePasse());

    $q->execute();
  }

  public function get($id)
  {
    $id = (int) $id;

    $q = $this->_db->prepare('SELECT * FROM coach WHERE id = :id');
    $q->bindValue(':id', $id);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);

    return new Coach($donnees);
  }

  public function findByNomMotDePasse(Coach $coach)
  {
    //$q = $this->_db->prepare('SELECT c.*, a.*
    //        FROM coach c
    //        LEFT JOIN ami a ON a.id_coach = c.id
    //        WHERE nom = :nom AND mot_de_passe = :motDePasse');
    $q = $this->_db->prepare('SELECT *
            FROM coach
            WHERE nom = :nom AND mot_de_passe = :motDePasse');
    $q->execute([':nom' => $coach->nom(), ':motDePasse' => $coach->motDePasse()]);

    // TODO MPL récupération du fil actu, des amis et des ligues en même temps

    $donnees = $q->fetch(PDO::FETCH_ASSOC);

    return new Coach($donnees);
  }

  public function findCoachAmiById($idCoach)
  {
    $coachs = [];

    $q = $this->_db->prepare('SELECT id, nom, code_postal
            FROM coach
            WHERE id IN (SELECT id_coach_ami FROM ami WHERE id_coach = :id)');
    $q->execute([':id' => $idCoach]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $coachs[] = new Coach($donnees);
    }

    return $coachs;
  }

  public function existeByNom($nom)
  {
      $q = $this->_db->prepare('SELECT COUNT(*) FROM coach WHERE nom = :nom');
      $q->execute([':nom' => $nom]);

      return (bool) $q->fetchColumn();
  }

  public function existeByNomMotDePasse(Coach $coach)
  {
        $q = $this->_db->prepare('SELECT COUNT(*) FROM coach WHERE nom = :nom AND mot_de_passe = :motDePasse');
        $q->execute([':nom' => $coach->nom(), ':motDePasse' => $coach->motDePasse()]);

        return (bool) $q->fetchColumn();
  }

  public function findByNom($nom, $idCoach)
  {
      $coachs = [];

      $q = $this->_db->prepare('SELECT id, nom, code_postal
            FROM coach
            WHERE nom LIKE :nom
            AND id != :id
            ORDER BY nom');
      $q->execute([':nom' => '%' . $nom . '%', ':id' => $idCoach]);

      while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
      {
        $coachs[] = new Coach($donnees);
      }

      return $coachs;
  }

  public function count()
  {
    return $this->_db->query('SELECT COUNT(*) FROM coach')->fetchColumn();
  }

  public function setDb(PDO $db)
  {
    $this->_db = $db;
  }
}
