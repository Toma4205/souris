<?php

require_once(__DIR__ . '/../managerBase.php');

class BonusMalusManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
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
