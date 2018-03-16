<?php

require_once(__DIR__ . '/../managerBase.php');

class BonusMalusManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findBonusMalusByEquipe($idEquipe, $idCalLigue)
  {
    $bonusMalus = [];
    $q = $this->_bdd->prepare('SELECT DISTINCT(b.code), n.libelle as libelle, n.select_joueur
      FROM bonus_malus b
      JOIN nomenclature_bonus_malus n ON n.code = b.code
      WHERE b.id_equipe = :idEquipe
      AND (b.id_cal_ligue IS NULL OR b.id_cal_ligue = :idCalLigue)
      ORDER BY libelle DESC');
    $q->execute([':idEquipe' => $idEquipe, 'idCalLigue' => $idCalLigue]);

    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $bonusMalus[] = new BonusMalus($donnees);
    }

    $q->closeCursor();

    return $bonusMalus;
  }

  public function findBonusMalusByEquipeEtCalLigue($idEquipe, $idCalLigue)
  {
    $q = $this->_bdd->prepare('SELECT b.*, n.libelle as libelle, n.select_joueur
      FROM bonus_malus b
      JOIN nomenclature_bonus_malus n ON n.code = b.code
      WHERE b.id_equipe = :idEquipe
      AND b.id_cal_ligue = :idCalLigue');
    $q->execute([':idEquipe' => $idEquipe, ':idCalLigue' => $idCalLigue]);
    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    // Si le bonusMalus n'est pas trouvé
    if (is_bool($donnees))
    {
      return null;
    }
    else
    {
      return new BonusMalus($donnees);
    }
  }

  public function creerBonusMalusPersoEquipe($idEquipe, $idLigue)
  {
    $bonus = [];

    $q = $this->_bdd->prepare('SELECT * FROM bonus_perso_ligue WHERE id_ligue = :id');
    $q->execute([':id' => $idLigue]);
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $bonus[] = new BonusPersoLigue($donnees);
    }
    $q->closeCursor();

    foreach($bonus as $value)
    {
      for ($index = 1; $index <= $value->nb(); $index++)
      {
        $q = $this->_bdd->prepare('INSERT INTO bonus_malus(code, id_equipe) VALUES(:code, :idEquipe)');
        $q->bindValue(':code', $value->code());
        $q->bindValue(':idEquipe', $idEquipe);
        $q->execute();
      }
    }
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

  public function reinitBonusMalusCompoEquipe($idEquipe, $idCalLigue)
  {
    $q = $this->_bdd->prepare('UPDATE bonus_malus SET id_cal_ligue = NULL,
      id_joueur_reel_equipe = NULL, id_joueur_reel_adverse = NULL, mi_temps = NULL
      WHERE id_cal_ligue = :calLigue AND id_equipe = :equipe');
    $q->bindValue(':calLigue', $idCalLigue);
    $q->bindValue(':equipe', $idEquipe);
    $q->execute();
  }

  public function creerOuMajBonusMalusCompoEquipe(BonusMalus $bonus, $idEquipe, $idCalLigue)
  {
    // Réinitialisation éventuelle d'un bonusMalus déjà enregistré
    $this->reinitBonusMalusCompoEquipe($idEquipe, $idCalLigue);

    // Sélection de l'id du bonusMalus à mettre à jour
    $q = $this->_bdd->prepare('SELECT * FROM bonus_malus
      WHERE code = :code AND id_equipe = :equipe AND id_cal_ligue IS NULL LIMIT 1');
    $q->execute([':code' => $bonus->code(), ':equipe' => $idEquipe]);
    $donnees = $q->fetch(PDO::FETCH_ASSOC);
    $q->closeCursor();

    $bonusBase = new BonusMalus($donnees);

    // Mise à jour du bonusMalus en BDD
    $q = $this->_bdd->prepare('UPDATE bonus_malus SET id_cal_ligue = :calLigue,
      id_joueur_reel_equipe = :idJoueurEquipe, id_joueur_reel_adverse = :idJoueurAdv,
      mi_temps = :miTemps
      WHERE id = :id');
    $q->bindValue(':calLigue', $idCalLigue);
    $q->bindValue(':idJoueurEquipe', $bonus->idJoueurReelEquipe());
    $q->bindValue(':idJoueurAdv', $bonus->idJoueurReelAdverse());
    $q->bindValue(':miTemps', $bonus->miTemps());
    $q->bindValue(':id', $bonusBase->id());
    $q->execute();
	}
}
