-- creation base
CREATE DATABASE souris CHARACTER SET 'utf8';
GRANT ALL PRIVILEGES ON souris.* TO 'souris'@'localhost' IDENTIFIED BY 'souris';

CREATE TABLE nomenclature_scoretonote (`ScoreObtenu` DECIMAL(5,3) NOT NULL , `Position` VARCHAR(100) NOT NULL, `Note` DECIMAL(3,1), PRIMARY KEY (`ScoreObtenu`,`Position`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_reglescalculnote (`StatName` VARCHAR(100) NOT NULL , `Position` VARCHAR(100) NOT NULL, `Ponderation` DECIMAL(5,3), PRIMARY KEY (`StatName`,`Position`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_equipe (`code` VARCHAR(3) NOT NULL , `libelle` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_tactique (`code` VARCHAR(10) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , `nb_def` TINYINT NOT NULL , `nb_mil` TINYINT NOT NULL , `nb_att` TINYINT NOT NULL , `nb_dc` TINYINT , `nb_dlg` TINYINT , `nb_dld` TINYINT ,
`nb_mdef` TINYINT , `nb_mc` TINYINT , `nb_mg` TINYINT , `nb_md` TINYINT , `nb_mo` TINYINT , `nb_ailg` TINYINT , `nb_aild` TINYINT , `nb_but` TINYINT , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_position (`code` VARCHAR(10) NOT NULL , `libelle` VARCHAR(100) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_caricature (`code` VARCHAR(30) NOT NULL , `libelle` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_bonus_malus (`code` VARCHAR(30) NOT NULL , `libelle` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE quantite_bonus_malus (`code` VARCHAR(30) NOT NULL , `nb_joueur` TINYINT UNSIGNED NOT NULL , `nb_pack_classique` TINYINT UNSIGNED NOT NULL , `nb_pack_folie` TINYINT UNSIGNED NOT NULL , PRIMARY KEY (`code`, `nb_joueur`)) ENGINE = InnoDB;
ALTER TABLE `quantite_bonus_malus` ADD FOREIGN KEY (`code`) REFERENCES `nomenclature_bonus_malus`(`code`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `calendrier_reel` (
`num_journee` TINYINT UNSIGNED NOT NULL,
`date_heure_debut` TIMESTAMP NOT NULL,
PRIMARY KEY (`num_journee`)
) ENGINE = InnoDB;

CREATE TABLE `joueur_reel` (
`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
`cle_roto_primaire` VARCHAR(100) NOT NULL,
`prenom` VARCHAR(100),
`nom` VARCHAR(100),
`equipe` CHAR(3),
`position` VARCHAR(20),
`prix` TINYINT(3) UNSIGNED NOT NULL,
`cle_roto_secondaire` VARCHAR(100),
PRIMARY KEY (`id`)
) ENGINE = InnoDB;

--LOAD DATA LOCAL INFILE 'C:\\Bitnami\\ListeJoueursReelsNouvelleTable.csv' INTO TABLE joueur_reel FIELDS TERMINATED BY ';' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;

CREATE TABLE `coach` (`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT , `nom` VARCHAR(40) NOT NULL , `mot_de_passe` CHAR(32) NOT NULL , `mail` VARCHAR(50) NULL , `code_postal` CHAR(5) NULL , `date_creation` DATE NOT NULL , `date_maj` DATETIME NOT NULL , PRIMARY KEY (`id`), UNIQUE INDEX `ind_uni_nom` (`nom`(10)), UNIQUE `ind_uni_mail` (`mail`)) ENGINE = InnoDB;

CREATE TABLE `ligue` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `nom` VARCHAR(40) NOT NULL , `etat` INT UNSIGNED NOT NULL , `date_creation` DATETIME NOT NULL , `libelle_pari` TEXT NULL, `mode_expert` BOOLEAN NOT NULL , `bonus_malus` CHAR(1) NOT NULL, `mode_mercato` CHAR(1) NOT NULL, `tour_mercato` TINYINT UNSIGNED NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

CREATE TABLE `coach_ligue` (`id_coach` MEDIUMINT UNSIGNED NOT NULL , `id_ligue` INT UNSIGNED NOT NULL , `createur` BOOLEAN NOT NULL , `date_validation` DATETIME NULL , PRIMARY KEY (`id_coach`, `id_ligue`)) ENGINE = InnoDB;
ALTER TABLE `coach_ligue` ADD FOREIGN KEY (`id_coach`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `coach_ligue` ADD FOREIGN KEY (`id_ligue`) REFERENCES `ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `confrere` (`id_coach` MEDIUMINT UNSIGNED NOT NULL , `id_coach_confrere` MEDIUMINT UNSIGNED NOT NULL , `date_debut` DATETIME NOT NULL , PRIMARY KEY (`id_coach`, `id_coach_confrere`)) ENGINE = InnoDB;
ALTER TABLE `confrere` ADD FOREIGN KEY (`id_coach`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `confrere` ADD FOREIGN KEY (`id_coach_confrere`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `equipe` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `id_ligue` INT UNSIGNED NOT NULL ,
  `id_coach` MEDIUMINT UNSIGNED NOT NULL ,
  `nom` VARCHAR(30) NOT NULL ,
  `ville` VARCHAR(30) NOT NULL ,
  `stade` VARCHAR(30) NOT NULL ,
  `budget_restant` SMALLINT NOT NULL ,
  `fin_mercato` BOOLEAN NOT NULL ,
  `classement` TINYINT UNSIGNED ,
  `nb_match` TINYINT UNSIGNED NOT NULL ,
  `nb_victoire` TINYINT UNSIGNED NOT NULL ,
  `nb_nul` TINYINT UNSIGNED NOT NULL ,
  `nb_defaite` TINYINT UNSIGNED NOT NULL ,
  `nb_but_pour` TINYINT UNSIGNED NOT NULL ,
  `nb_but_contre` TINYINT UNSIGNED NOT NULL ,
  `nb_bonus` TINYINT UNSIGNED NOT NULL ,
  `nb_malus` TINYINT UNSIGNED NOT NULL ,
  `code_caricature` VARCHAR(30) NULL ,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;
ALTER TABLE `equipe` ADD UNIQUE(`id_ligue`, `id_coach`);
ALTER TABLE `equipe` ADD FOREIGN KEY (`id_coach`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `equipe` ADD FOREIGN KEY (`id_ligue`) REFERENCES `ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `equipe` ADD FOREIGN KEY (`code_caricature`) REFERENCES `nomenclature_caricature`(`code`) ON DELETE NO ACTION ON UPDATE NO ACTION;

CREATE TABLE `joueur_equipe` (
`id_ligue` INT UNSIGNED NOT NULL,
`id_equipe` INT UNSIGNED NOT NULL ,
`id_joueur_reel` MEDIUMINT UNSIGNED NOT NULL,
`prix` MEDIUMINT UNSIGNED NOT NULL,
`tour_mercato`  TINYINT UNSIGNED NOT NULL,
`date_offre` DATETIME NOT NULL,
`date_validation` DATETIME,
`nb_but_reel` TINYINT UNSIGNED NOT NULL ,
`nb_but_virtuel` TINYINT UNSIGNED NOT NULL ,
`nb_match` TINYINT UNSIGNED NOT NULL ,
PRIMARY KEY (`id_equipe`, `id_joueur_reel`)
) ENGINE = InnoDB;
ALTER TABLE `joueur_equipe` ADD FOREIGN KEY (`id_ligue`) REFERENCES `ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `joueur_equipe` ADD FOREIGN KEY (`id_equipe`) REFERENCES `equipe`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `joueur_equipe` ADD FOREIGN KEY (`id_joueur_reel`) REFERENCES `joueur_reel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `calendrier_ligue` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_ligue` INT UNSIGNED NOT NULL,
  `id_equipe_dom` INT UNSIGNED NOT NULL ,
  `id_equipe_ext` INT UNSIGNED NOT NULL ,
  `num_journee` TINYINT UNSIGNED NOT NULL ,
  `score_dom` TINYINT UNSIGNED ,
  `score_ext` TINYINT UNSIGNED ,
PRIMARY KEY (`id`)
) ENGINE = InnoDB;
ALTER TABLE `calendrier_ligue` ADD FOREIGN KEY (`id_ligue`) REFERENCES `ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `calendrier_ligue` ADD FOREIGN KEY (`id_equipe_dom`) REFERENCES `equipe`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `calendrier_ligue` ADD FOREIGN KEY (`id_equipe_ext`) REFERENCES `equipe`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `bonus_malus` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`code` VARCHAR(30) NOT NULL,
`id_equipe` INT UNSIGNED NOT NULL,
`id_cal_ligue` INT UNSIGNED,
`id_joueur_reel_equipe` MEDIUMINT UNSIGNED,
`id_joueur_reel_adverse` MEDIUMINT UNSIGNED,
`mi_temps` TINYINT UNSIGNED,
PRIMARY KEY (`id`)
) ENGINE = InnoDB;
ALTER TABLE `bonus_malus` ADD FOREIGN KEY (`code`) REFERENCES `nomenclature_bonus_malus`(`code`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bonus_malus` ADD FOREIGN KEY (`id_equipe`) REFERENCES `equipe`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bonus_malus` ADD FOREIGN KEY (`id_cal_ligue`) REFERENCES `calendrier_ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bonus_malus` ADD FOREIGN KEY (`id_joueur_reel_equipe`) REFERENCES `joueur_reel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bonus_malus` ADD FOREIGN KEY (`id_joueur_reel_adverse`) REFERENCES `joueur_reel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `compo_equipe` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`id_cal_ligue` INT UNSIGNED NOT NULL,
`id_equipe` INT UNSIGNED NOT NULL,
`code_tactique` VARCHAR(10) NOT NULL,
`code_bonus_malus` VARCHAR(30),
PRIMARY KEY (`id`)
) ENGINE = InnoDB;
ALTER TABLE `compo_equipe` ADD FOREIGN KEY (`code_bonus_malus`) REFERENCES `nomenclature_bonus_malus`(`code`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `compo_equipe` ADD FOREIGN KEY (`code_tactique`) REFERENCES `nomenclature_tactique`(`code`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `compo_equipe` ADD FOREIGN KEY (`id_equipe`) REFERENCES `equipe`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `compo_equipe` ADD FOREIGN KEY (`id_cal_ligue`) REFERENCES `calendrier_ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `resultatsL1_reel` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`journee` INT UNSIGNED NOT NULL,
`equipeDomicile` VARCHAR(30),
`homeDomicile` VARCHAR(30),
`butDomicile` INT UNSIGNED NOT NULL,
`winOrLoseDomicile` VARCHAR(30),
`penaltyDomicile` INT UNSIGNED NOT NULL,
`equipeVisiteur` VARCHAR(30),
`homeVisiteur` VARCHAR(30),
`butVisiteur` INT UNSIGNED NOT NULL,
`winOrLoseVisiteur` VARCHAR(30),
`penaltyVisiteur` INT UNSIGNED NOT NULL,
PRIMARY KEY (`id`)
) ENGINE = InnoDB;

--LOAD DATA LOCAL INFILE 'C:\\Bitnami\\resultatsL1.csv' INTO TABLE resultatsL1_reel FIELDS TERMINATED BY ';' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;


CREATE TABLE `prepa_mercato` (
`id_coach` MEDIUMINT UNSIGNED NOT NULL ,
`id_joueur_reel` MEDIUMINT UNSIGNED NOT NULL,
`prix` MEDIUMINT UNSIGNED NOT NULL,
PRIMARY KEY (`id_coach`, `id_joueur_reel`)
) ENGINE = InnoDB;
ALTER TABLE `prepa_mercato` ADD FOREIGN KEY (`id_coach`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `prepa_mercato` ADD FOREIGN KEY (`id_joueur_reel`) REFERENCES `joueur_reel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


CREATE TABLE `joueur_stats` (
`id` VARCHAR(100) NOT NULL ,
`journee` CHAR(6) NOT NULL,
`a_joue` SMALLINT UNSIGNED NOT NULL,
`minutes` SMALLINT UNSIGNED NOT NULL,
`titulaire` SMALLINT UNSIGNED NOT NULL,
`est_rentre` SMALLINT UNSIGNED NOT NULL,
`est_sorti` SMALLINT UNSIGNED NOT NULL,
`jaune` SMALLINT UNSIGNED NOT NULL,
`jaune_rouge` SMALLINT UNSIGNED NOT NULL,
`rouge` SMALLINT UNSIGNED NOT NULL,
`but` SMALLINT UNSIGNED NOT NULL,
`passe_d` SMALLINT UNSIGNED NOT NULL,
`second_passe_d` SMALLINT UNSIGNED NOT NULL,
`tir` SMALLINT UNSIGNED NOT NULL,
`tir_cadre` SMALLINT UNSIGNED NOT NULL,
`interception` SMALLINT UNSIGNED NOT NULL,
`centre` SMALLINT UNSIGNED NOT NULL,
`centre_reussi` SMALLINT UNSIGNED NOT NULL,
`occasion_creee` SMALLINT UNSIGNED NOT NULL,
`contre` SMALLINT UNSIGNED NOT NULL,
`total_tacle` SMALLINT UNSIGNED NOT NULL,
`tacle_reussi` SMALLINT UNSIGNED NOT NULL,
`faute_commise` SMALLINT UNSIGNED NOT NULL,
`faute_subie` SMALLINT UNSIGNED NOT NULL,
`passe` SMALLINT UNSIGNED NOT NULL,
`passe_tentee` SMALLINT UNSIGNED NOT NULL,
`centre_reussi_dans_le_jeu` SMALLINT UNSIGNED NOT NULL,
`duel_aerien_gagne` SMALLINT UNSIGNED NOT NULL,
`grosse_occasion_creee` SMALLINT UNSIGNED NOT NULL,
`ballon_recupere` SMALLINT UNSIGNED NOT NULL,
`dribble` SMALLINT UNSIGNED NOT NULL,
`duel_gagne` SMALLINT UNSIGNED NOT NULL,
`ballon_touche` SMALLINT UNSIGNED NOT NULL,
`ballon_touche_int_surface` SMALLINT UNSIGNED NOT NULL,
`tir_int_surface` SMALLINT UNSIGNED NOT NULL,
`tir_ext_surface` SMALLINT UNSIGNED NOT NULL,
`tir_cadre_int_surface` SMALLINT UNSIGNED NOT NULL,
`tir_cadre_ext_surface` SMALLINT UNSIGNED NOT NULL,
`but_int_surface` SMALLINT UNSIGNED NOT NULL,
`but_ext_surface` SMALLINT UNSIGNED NOT NULL,
`ballon_perdu` SMALLINT UNSIGNED NOT NULL,
`csc` SMALLINT UNSIGNED NOT NULL,
`penalty_tire` SMALLINT UNSIGNED NOT NULL,
`penalty_marque` SMALLINT UNSIGNED NOT NULL,
`penalty_rate` SMALLINT UNSIGNED NOT NULL,
`penalty_arrete` SMALLINT UNSIGNED NOT NULL,
`corner_tire` SMALLINT UNSIGNED NOT NULL,
`corner_centre` SMALLINT UNSIGNED NOT NULL,
`corner_gagne` SMALLINT UNSIGNED NOT NULL,
`coup_franc_centre` SMALLINT UNSIGNED NOT NULL,
`coup_franc_centre_reussi` SMALLINT UNSIGNED NOT NULL,
`coup_franc_tire` SMALLINT UNSIGNED NOT NULL,
`coup_franc_cadre` SMALLINT UNSIGNED NOT NULL,
`coup_franc_marque` SMALLINT UNSIGNED NOT NULL,
`but_concede` SMALLINT UNSIGNED NOT NULL,
`cleansheet` SMALLINT UNSIGNED NOT NULL,
`arret` SMALLINT UNSIGNED NOT NULL,
`arret_tir_int_surface` SMALLINT UNSIGNED NOT NULL,
`arret_tir_ext_surface` SMALLINT UNSIGNED NOT NULL,
`sortie_ext_surface_reussie` SMALLINT UNSIGNED NOT NULL,
`penalty_concede` SMALLINT UNSIGNED NOT NULL,
`penalty_subi_gb` SMALLINT UNSIGNED NOT NULL,
`penalty_arrete_gb` SMALLINT UNSIGNED NOT NULL,
`degagement` SMALLINT UNSIGNED NOT NULL,
`degagement_reussi` SMALLINT UNSIGNED NOT NULL,
`degagement_poing` SMALLINT UNSIGNED NOT NULL,
`6_buts_ou_plus_pris_sans_penalty` SMALLINT UNSIGNED NOT NULL,
`5_buts_pris_sans_penalty` SMALLINT UNSIGNED NOT NULL,
`4_buts_pris_sans_penalty` SMALLINT UNSIGNED NOT NULL,
`3_buts_pris_sans_penalty` SMALLINT UNSIGNED NOT NULL,
`2_buts_pris_sans_penalty` SMALLINT UNSIGNED NOT NULL,
`1_but_pris_sans_penalty` SMALLINT UNSIGNED NOT NULL,
`rouge_60` SMALLINT UNSIGNED NOT NULL,
`rouge_75` SMALLINT UNSIGNED NOT NULL,
`rouge_80` SMALLINT UNSIGNED NOT NULL,
`rouge_85` SMALLINT UNSIGNED NOT NULL,
`centre_rate` SMALLINT UNSIGNED NOT NULL,
`clean_60` SMALLINT UNSIGNED NOT NULL,
`clean_60D` SMALLINT UNSIGNED NOT NULL,
`ecart_moins_5` SMALLINT UNSIGNED NOT NULL,
`ecart_moins_4` SMALLINT UNSIGNED NOT NULL,
`ecart_moins_3` SMALLINT UNSIGNED NOT NULL,
`ecart_moins_2` SMALLINT UNSIGNED NOT NULL,
`ecart_plus_2` SMALLINT UNSIGNED NOT NULL,
`ecart_plus_3` SMALLINT UNSIGNED NOT NULL,
`ecart_plus_4` SMALLINT UNSIGNED NOT NULL,
`grosse_occasion_ratee` SMALLINT UNSIGNED NOT NULL,
`malus_defaite` SMALLINT UNSIGNED NOT NULL,
`15_passes_OK_30` SMALLINT UNSIGNED NOT NULL,
`15_passes_OK_40` SMALLINT UNSIGNED NOT NULL,
`15_passes_OK_50` SMALLINT UNSIGNED NOT NULL,
`15_passes_OK_90` SMALLINT UNSIGNED NOT NULL,
`15_passes_OK_95` SMALLINT UNSIGNED NOT NULL,
`15_passes_OK_100` SMALLINT UNSIGNED NOT NULL,
`25_passes_OK_30` SMALLINT UNSIGNED NOT NULL,
`25_passes_OK_40` SMALLINT UNSIGNED NOT NULL,
`25_passes_OK_50` SMALLINT UNSIGNED NOT NULL,
`25_passes_OK_90` SMALLINT UNSIGNED NOT NULL,
`25_passes_OK_95` SMALLINT UNSIGNED NOT NULL,
`25_passes_OK_100` SMALLINT UNSIGNED NOT NULL,
`tacle_rate` SMALLINT UNSIGNED NOT NULL,
`tir_non_cadre` SMALLINT UNSIGNED NOT NULL,
`80_ballons_touches` SMALLINT UNSIGNED NOT NULL,
`90_ballons_touches` SMALLINT UNSIGNED NOT NULL,
`100_ballons_touches` SMALLINT UNSIGNED NOT NULL,
`bonus_victoire` SMALLINT UNSIGNED NOT NULL,
`coup_franc_rate` SMALLINT UNSIGNED NOT NULL,
`note` SMALLINT UNSIGNED NOT NULL,
PRIMARY KEY (`id`,`journee`)
) ENGINE = InnoDB;

--LOAD DATA LOCAL INFILE 'C:\\Bitnami\\fichierJ9.csv' INTO TABLE joueur_temp FIELDS TERMINATED BY ';' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;
