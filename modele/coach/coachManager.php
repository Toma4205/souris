<?php

class CoachManager
{
  private $_db; // Instance de PDO.

  public function __construct($db)
  {
    $this->setDb($db);
  }

  public function add(Coach $coach)
  {
    $q = $this->_db->prepare('INSERT INTO coach(nom, mot_de_passe) VALUES(:nom, :motDePasse)');
    $q->bindValue(':nom', $coach->nom());
    $q->bindValue(':motDePasse', $coach->motDePasse());

    $q->execute();
  }

  public function delete(Coach $coach)
  {
    $q = $this->_db->prepare('DELETE FROM coach WHERE id = :id');
    $q->bindValue(':id', $coach->id());

    $q->execute();
  }

  public function get($id)
  {
    $id = (int) $id;

    $q = $this->_db->prepare('SELECT id, nom, mot_de_passe, mail, code_postal FROM coach WHERE id = :id');
    $q->bindValue(':id', $coach->id());

    $donnees = $q->fetch(PDO::FETCH_ASSOC);

    return new Coach($donnees);
  }

  public function findByNomMotDePasse(Coach $coach)
  {
    $q = $this->_db->prepare('SELECT id, nom, mot_de_passe, mail, code_postal FROM coach WHERE nom = :nom AND mot_de_passe = :motDePasse');
    $q->execute([':nom' => $coach->nom(), ':motDePasse' => $coach->motDePasse()]);

    $donnees = $q->fetch(PDO::FETCH_ASSOC);

    return new Coach($donnees);
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

  public function getAll()
  {
    $coachs = [];

    $q = $this->_db->query('SELECT id, nom, mot_de_passe, mail, code_postal FROM coach ORDER BY nom');

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
