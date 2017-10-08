<?php

require_once('/../managerBase.php');

class LigueManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function creerLigue($idCoach, Ligue $ligue)
  {
    // création de la ligue
    $q = $this->_bdd->prepare('INSERT INTO ligue(nom, libelle_pari, mode_expert, nb_equipe, date_creation)
      VALUES(:nom, :libellePari, :modeExpert, :nbEquipe, NOW())');
    $q->bindValue(':nom', $ligue->nom());
    $q->bindValue(':libellePari', $ligue->libellePari());
    $q->bindValue(':modeExpert', $ligue->modeExpert());
    $q->bindValue(':nbEquipe', $ligue->nbEquipe());

    $q->execute();

    // récupération de l'id
    $idLigue = $this->_bdd->lastInsertId();

    // création du lien avec le coach
    $q = $this->_bdd->prepare('INSERT INTO coach_ligue(id_coach, id_ligue, createur, date_validation)
      VALUES(:idCoach, :idLigue, TRUE, NOW())');
    $q->bindValue(':idCoach', $idCoach);
    $q->bindValue(':idLigue', $idLigue);

    $q->execute();

    return $idLigue;
  }
}
