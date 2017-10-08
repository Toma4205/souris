-- creation base
CREATE DATABASE souris CHARACTER SET 'utf8';

CREATE TABLE `coach` (`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT , `nom` VARCHAR(40) NOT NULL , `mot_de_passe` CHAR(32) NOT NULL , `mail` VARCHAR(50) NULL , `code_postal` CHAR(5) NULL , `date_creation` DATE NOT NULL , `date_maj` DATETIME NOT NULL , PRIMARY KEY (`id`), UNIQUE INDEX `ind_uni_nom` (`nom`(10)), UNIQUE `ind_uni_mail` (`mail`)) ENGINE = InnoDB;
CREATE TABLE `ligue` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `nom` VARCHAR(40) NOT NULL , `date_creation` DATETIME NOT NULL , `libelle_pari` TEXT NULL, `mode_expert` BOOLEAN NOT NULL , `nb_equipe` TINYINT UNSIGNED NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `coach_ligue` (`id_coach` MEDIUMINT UNSIGNED NOT NULL , `id_ligue` INT UNSIGNED NOT NULL , `createur` BOOLEAN NOT NULL , `date_validation` DATETIME NULL , PRIMARY KEY (`id_coach`, `id_ligue`)) ENGINE = InnoDB;
CREATE TABLE `equipe` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `id_ligue` INT UNSIGNED NOT NULL , `id_coach` MEDIUMINT UNSIGNED NOT NULL , `nom` VARCHAR(30) NOT NULL , `ville` VARCHAR(30) NOT NULL , `stade` VARCHAR(30) NOT NULL , `budget_restant` SMALLINT NOT NULL , `fin_mercato` BOOLEAN NOT NULL , `nb_match` TINYINT UNSIGNED NOT NULL , `nb_victoire` TINYINT UNSIGNED NOT NULL , `nb_nul` TINYINT UNSIGNED NOT NULL , `nb_defaite` TINYINT UNSIGNED NOT NULL ,
`nb_but_pour` TINYINT UNSIGNED NOT NULL , `nb_but_contre` TINYINT UNSIGNED NOT NULL , `code_caricature` VARCHAR(30) NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `confrere` (`id_coach` MEDIUMINT UNSIGNED NOT NULL , `id_coach_confrere` MEDIUMINT UNSIGNED NOT NULL , `date_debut` DATETIME NOT NULL , PRIMARY KEY (`id_coach`, `id_coach_confrere`)) ENGINE = InnoDB;

CREATE TABLE nomenclature_tactique (`code` VARCHAR(10) NOT NULL , `libelle` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_position (`code` VARCHAR(10) NOT NULL , `libelle` VARCHAR(100) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_caricature (`code` VARCHAR(30) NOT NULL , `libelle` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
CREATE TABLE nomenclature_bonus_malus (`code` VARCHAR(30) NOT NULL , `libelle` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;

ALTER TABLE `coach_ligue` ADD FOREIGN KEY (`id_coach`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `coach_ligue` ADD FOREIGN KEY (`id_ligue`) REFERENCES `ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `equipe` ADD FOREIGN KEY (`id_coach`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `equipe` ADD FOREIGN KEY (`id_ligue`) REFERENCES `ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `equipe` ADD FOREIGN KEY (`code_caricature`) REFERENCES `nomenclature_caricature`(`code`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `confrere` ADD FOREIGN KEY (`id_coach`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `confrere` ADD FOREIGN KEY (`id_coach_confrere`) REFERENCES `coach`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
