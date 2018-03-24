<?php

require_once(__DIR__ . '/../managerBase.php');

class CaricatureEquipeManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findByLigue($idLigue)
  {
    $caracs = [];
    $q = $this->_bdd->prepare('SELECT ce.*, n.libelle_court as libelleCourtCaricature,
      n.libelle as libelleCaricature, e.nom as nomEquipe, jr.nom
      FROM caricature_equipe ce
      JOIN nomenclature_caricature n ON n.code = ce.code
      JOIN equipe e ON e.id = ce.id_equipe
      LEFT JOIN joueur_reel jr ON jr.id = ce.id_joueur_reel
      WHERE e.id_ligue = :id');
    $q->execute([':id' => $idLigue]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $caracs[] = new CaricatureEquipe($donnees);
    }

    $q->closeCursor();

    return $caracs;
  }
}
