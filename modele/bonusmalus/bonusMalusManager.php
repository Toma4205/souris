<?php

require_once(__DIR__ . '/../managerBase.php');

class BonusMalusManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findBonusMalusByEquipe($idEquipe)
  {
    $bonusMalus = [];
    $q = $this->_bdd->prepare('SELECT DISTINCT(b.code), n.libelle as libelle
      FROM bonus_malus b
      JOIN nomenclature_bonus_malus n ON n.code = b.code
      WHERE b.id_equipe = :idEquipe
      AND b.id_cal_ligue IS NULL
      ORDER BY libelle DESC');
    $q->execute([':idEquipe' => $idEquipe]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $bonusMalus[] = new BonusMalus($donnees);
    }

    $q->closeCursor();

    return $bonusMalus;
  }

  public function creerBonusMalusEquipe($idEquipe, $bonusMalus, int $nbEquipe)
  {
    $bonus = [];

    $q = $this->_bdd->prepare('SELECT * FROM quantite_bonus_malus WHERE nb_joueur = :nb');
    $q->execute([':nb' => $nbEquipe]);
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $bonus[] = new QuantiteBonusMalus($donnees);
    }
    $q->closeCursor();

    if (ConstantesAppli::BONUS_MALUS_CLASSIQUE == $bonusMalus) {
      foreach($bonus as $value)
      {
        for ($index = 1; $index <= $value->nbPackClassique(); $index++)
        {
          $q = $this->_bdd->prepare('INSERT INTO bonus_malus(code, id_equipe) VALUES(:code, :idEquipe)');
          $q->bindValue(':code', $value->code());
          $q->bindValue(':idEquipe', $idEquipe);
          $q->execute();
        }
      }
    } elseif (ConstantesAppli::BONUS_MALUS_FOLIE == $bonusMalus) {
      foreach($bonus as $value)
      {
        for ($index = 1; $index <= $value->nbPackFolie(); $index++)
        {
          $q = $this->_bdd->prepare('INSERT INTO bonus_malus(code, id_equipe) VALUES(:code, :idEquipe)');
          $q->bindValue(':code', $value->code());
          $q->bindValue(':idEquipe', $idEquipe);
          $q->execute();
        }
      }
    }
  }
}
