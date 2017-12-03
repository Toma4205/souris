<?php

require_once(__DIR__ . '/../managerBase.php');

class CalendrierLigueManager extends ManagerBase
{
  public function __construct($bdd)
  {
    $this->setDb($bdd);
  }

  public function findProchaineJourneeByEquipe($idEquipe)
  {
      $q = $this->_bdd->prepare('SELECT c.*, dom.nom as nomEquipeDom, ext.nom as nomEquipeExt
        FROM calendrier_ligue c
        JOIN equipe dom ON dom.id = c.id_equipe_dom
        JOIN equipe ext ON ext.id = c.id_equipe_ext
        WHERE (c.id_equipe_dom = :idEquipe OR c.id_equipe_ext = :idEquipe)
        AND c.score_dom IS NULL
        ORDER BY c.num_journee ASC LIMIT 1');
      $q->execute([':idEquipe' => $idEquipe]);
      $donnees = $q->fetch(PDO::FETCH_ASSOC);
      $q->closeCursor();

      return new CalendrierLigue($donnees);
  }

  public function findProchaineJourneeByCalReel($idEquipe, $numJournee)
  {
      $q = $this->_bdd->prepare('SELECT c.*, dom.nom as nomEquipeDom, ext.nom as nomEquipeExt
        FROM calendrier_ligue c
        JOIN equipe dom ON dom.id = c.id_equipe_dom
        JOIN equipe ext ON ext.id = c.id_equipe_ext
        WHERE (c.id_equipe_dom = :idEquipe OR c.id_equipe_ext = :idEquipe)
        AND c.num_journee_cal_reel = :cal');
      $q->execute([':idEquipe' => $idEquipe, ':cal' => $numJournee]);
      $donnees = $q->fetch(PDO::FETCH_ASSOC);
      $q->closeCursor();

      // Si plus de calendrier
      if (is_bool($donnees))
      {
        return new CalendrierLigue([]);
      }
      else
      {
        return new CalendrierLigue($donnees);
      }
  }

  public function findCalendrierByLigue($idLigue)
  {
      $equipes = [];
      $q = $this->_bdd->prepare('SELECT c.*, dom.nom as nomEquipeDom, ext.nom as nomEquipeExt
        FROM calendrier_ligue c
        JOIN equipe dom ON dom.id = c.id_equipe_dom
        JOIN equipe ext ON ext.id = c.id_equipe_ext
        WHERE c.id_ligue = :idLigue
        ORDER BY c.num_journee ASC');
      $q->execute([':idLigue' => $idLigue]);

      while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
  		{
  			$equipes[] = new CalendrierLigue($donnees);
  		}

  		$q->closeCursor();

      return $equipes;
  }

  public function calculerCalendrier($idLigue, $tabIdEquipe, $numJourneeCalReel) {
      $nbEquipe = sizeof($tabIdEquipe);

      // Si impair, on ajoute un ghost
      $ghost = false;
      if ($nbEquipe % 2 == 1) {
          $nbEquipe++;
          $ghost = true;
          $tabIdEquipe[] = -1;
      }

      $totalJournees = $nbEquipe - 1;
      $nbMatchParJournee = $nbEquipe / 2;
      $tabJournees = array();
      $tabFinal = array();
      for ($i = 0; $i < $totalJournees; $i++) {
          $tabJournees[$i] = array();
          $tabFinal[$i] = array();
      }

      //echo '$ghost=' . $ghost . ', $totalJournees=' . $totalJournees . ', $nbMatchParJournee=' . $nbMatchParJournee . '<br/>';
      for ($journee = 0; $journee < $totalJournees; $journee++) {
          for ($match = 0; $match < $nbMatchParJournee; $match++) {
              $home = ($journee + $match) % ($nbEquipe - 1);
              $away = ($nbEquipe - 1 - $match + $journee) % ($nbEquipe - 1);
              // La dernière équipe ne change pas lors que les autres tournent
              if ($match == 0) {
                  $away = $nbEquipe - 1;
              }
              $tabJournees[$journee][$match] = $this->getIdEquipe($home + 1, $tabIdEquipe)
                  . "v" . $this->getIdEquipe($away + 1, $tabIdEquipe);
              //echo 'journee ' . $journee . ', match ' . $match . ' => ' . $tabJournees[$journee][$match] . '<br/>';
          }
      }

      // Permet d'alterner DOM/EXT
      $interleaved = array();
      for ($i = 0; $i < $totalJournees; $i++) {
          $interleaved[$i] = array();
      }

      $evn = 0;
      $odd = ($nbEquipe / 2);
      for ($i = 0; $i < sizeof($tabJournees); $i++) {
          if ($i % 2 == 0) {
              $interleaved[$i] = $tabJournees[$evn++];
          } else {
              $interleaved[$i] = $tabJournees[$odd++];
          }
      }

      $tabJournees = $interleaved;

      // Gestion de la dernière équipe qui est pour le moment toujours à l'EXT
      for ($journee = 0; $journee < sizeof($tabJournees); $journee++) {
          if ($journee % 2 == 1) {
              $tabJournees[$journee][0] = $this->inverserMatch($tabJournees[$journee][0]);
          }
      }

      if ($ghost) {
        $tabJournees = $this->supprimerMatchGhost($tabJournees, $tabFinal);
      }

      for ($i = 0; $i < sizeof($tabJournees); $i++) {
          foreach ($tabJournees[$i] as $match) {
              $equipes = explode('v', $match);

              $q = $this->_bdd->prepare('INSERT INTO calendrier_ligue(id_ligue, id_equipe_dom, id_equipe_ext,
                num_journee, num_journee_cal_reel)
                VALUES(:idLigue, :idEquipeDom, :idEquipeExt, :journee, :calReel)');
              $q->bindValue(':idLigue', $idLigue);
              $q->bindValue(':idEquipeDom', $equipes[0]);
              $q->bindValue(':idEquipeExt', $equipes[1]);
              $q->bindValue(':journee', ($i + 1));
              $q->bindValue(':calReel', $numJourneeCalReel);

              $q->execute();

              $q = $this->_bdd->prepare('INSERT INTO calendrier_ligue(id_ligue, id_equipe_dom, id_equipe_ext,
                num_journee, num_journee_cal_reel)
                VALUES(:idLigue, :idEquipeDom, :idEquipeExt, :journeeRetour, :calReel)');
              $q->bindValue(':idLigue', $idLigue);
              $q->bindValue(':idEquipeDom', $equipes[1]);
              $q->bindValue(':idEquipeExt', $equipes[0]);
              $q->bindValue(':journeeRetour', ($i + $nbEquipe));
              $q->bindValue(':calReel', ($numJourneeCalReel + ($nbEquipe - 1)));

              $q->execute();
          }
          $numJourneeCalReel++;
      }
  }

  function supprimerMatchGhost($tabJournees, $tabFinal) {
    for ($i = 0; $i < sizeof($tabJournees); $i++) {
      foreach ($tabJournees[$i] as $match) {
        $equipes = explode('v', $match);
        if ($equipes[0] != -1 && $equipes[1] != -1)
        {
          $tabFinal[$i][] = $match;
        }
      }
    }
    return $tabFinal;
  }

  function inverserMatch($match) {
      $components = explode('v', $match);
      return $components[1] . "v" . $components[0];
  }

  function getIdEquipe($num, $tabIdEquipe) {
      $i = $num - 1;
      if (sizeof($tabIdEquipe) > $i) {
          return $tabIdEquipe[$i];
      } else {
          return $num;
      }
  }
}
