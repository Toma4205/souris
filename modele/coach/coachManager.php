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
    // Hachage du mot de passe
    $pass_hache = password_hash($coach->motDePasse(), PASSWORD_DEFAULT);

    $q = $this->_bdd->prepare('INSERT INTO coach(nom, mot_de_passe, mail, date_creation, date_maj)
      VALUES(:nom, :motDePasse, :mail, NOW(), NOW())');
    $q->bindValue(':nom', $coach->nom());
    $q->bindValue(':motDePasse', $pass_hache);
    $q->bindValue(':mail', $coach->mail());

    $q->execute();
  }

  public function majMdpCoach(int $id, $mdp)
  {
    // Hachage du mot de passe
    $pass_hache = password_hash($mdp, PASSWORD_DEFAULT);

    $q = $this->_bdd->prepare('UPDATE coach SET mot_de_passe = :mdp, date_maj = NOW()
        WHERE id = :id');
    $q->bindValue(':mdp',  $pass_hache);
    $q->bindValue(':id', $id);

    $q->execute();
  }

  public function majCoach(Coach $coach)
  {
    $q = $this->_bdd->prepare('UPDATE coach SET nom = :nom, mail = :mail, code_postal = :code,
        date_maj = NOW(), aff_ligue_masquee = :affLigueMasquee
        WHERE id = :id');
    $q->bindValue(':nom', $coach->nom());
    $q->bindValue(':mail', $coach->mail());
    $q->bindValue(':code', $coach->codePostal());
    $q->bindValue(':affLigueMasquee', $coach->affLigueMasquee());
    $q->bindValue(':id', $coach->id());

    $q->execute();
  }

  public function findByMailMotDePasse(Coach $coach)
  {
    
    //  Récupération de l'utilisateur et de son pass hashé
    $req = $this->_bdd->prepare('SELECT * FROM coach WHERE mail = :mail');
    $req->execute(array(
        'mail' => $coach->mail()));
    $resultat = $req->fetch();

    // Comparaison du pass envoyé via le formulaire avec la base
    $isPasswordCorrect = password_verify($coach->motDePasse(), $resultat['mot_de_passe']);
    $req->closeCursor();

    if (!$resultat)
    {
        return $coach;
    }
    else
    {
        if ($isPasswordCorrect) {
          return new Coach($resultat);
        }else {
            return $coach;
        }
    }



    /* ANCIEN ALGO NON SECURISE
    // Hachage du mot de passe
    $pass_hache = password_hash($coach->motDePasse(), PASSWORD_DEFAULT);
    echo $pass_hache;

    $q = $this->_bdd->prepare('SELECT *
            FROM coach
            WHERE mail = :mail AND mot_de_passe = :motDePasse');
    $q->execute([':mail' => $coach->mail(), ':motDePasse' =>  $pass_hache]);

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
    }*/
  }

  public function existeByMail($mail)
  {
      $q = $this->_bdd->prepare('SELECT COUNT(*) FROM coach WHERE mail = :mail');
      $q->execute([':mail' => $mail]);

      return (bool) $q->fetchColumn();
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
