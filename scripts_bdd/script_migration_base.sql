/*Script passé en "PROD" le 20/03/2018*/
ALTER TABLE `joueur_reel` ADD `etat` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `position_secondaire`;
ALTER TABLE `nomenclature_equipes_reelles` ADD `ville_roto` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `ville_maxi`;
ALTER TABLE `resultatsl1_reel` CHANGE `butDomicile` `butDomicile` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `resultatsl1_reel` CHANGE `penaltyDomicile` `penaltyDomicile` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `resultatsl1_reel` CHANGE `butVisiteur` `butVisiteur` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `resultatsl1_reel` CHANGE `penaltyVisiteur` `penaltyVisiteur` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `resultatsl1_reel` CHANGE `statut` `statut` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `nomenclature_equipes_reelles` CHANGE `trigramme` `trigramme` CHAR(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `nomenclature_equipes_reelles` CHANGE `ville_maxi` `ville_maxi` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

/*Script passé en "PROD" le 16/03/2018 */
ALTER TABLE `coach` CHANGE `mot_de_passe` `mot_de_passe` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
CREATE TABLE `bonus_perso_ligue` ( `code` VARCHAR(30) NOT NULL , `nb` TINYINT UNSIGNED NOT NULL , `id_ligue` INT UNSIGNED NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `bonus_perso_ligue` CHANGE `code` `code` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `bonus_perso_ligue` ADD PRIMARY KEY (`code`, `id_ligue`);
ALTER TABLE `bonus_perso_ligue` ADD FOREIGN KEY (`code`) REFERENCES `nomenclature_bonus_malus`(`code`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `bonus_perso_ligue` ADD FOREIGN KEY (`id_ligue`) REFERENCES `ligue`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


/*Script passé en "PROD" le 12/03/2018 */
ALTER TABLE `calendrier_reel` CHANGE `date_heure_debut` `date_heure_debut` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

/*Script passé en "PROD" le 11/03/2018 */
ALTER TABLE `calendrier_reel` ADD `date_heure_fin` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `date_heure_debut`;

/*Script passé en "PROD" le 04/03/2018 */
UPDATE joueur_reel SET cle_roto_secondaire = 'NkunkuChristopherPSG' WHERE id = 456;


/*Script passé en "PROD" le 02/03/2018 */
CREATE TABLE `buteur_live_journee` (
`id_buteur_live` INT UNSIGNED NOT NULL AUTO_INCREMENT,
`journee` TINYINT UNSIGNED NOT NULL,
`id_joueur_reel` MEDIUMINT UNSIGNED,
`id_joueur_maxi` VARCHAR(100) NOT NULL,
`sur_penalty` TINYINT UNSIGNED NOT NULL,
`sur_csc` TINYINT UNSIGNED NOT NULL,
PRIMARY KEY (`id_buteur_live`)
) ENGINE = InnoDB;
ALTER TABLE `buteur_live_journee` ADD `ville_maxi` VARCHAR(100) NOT NULL AFTER `id_joueur_maxi`;

/*Script passé en "PROD" le 01/03/2018 */
ALTER TABLE `joueur_equipe` ADD `moy_note` DECIMAL(3,2) NULL AFTER `nb_match`;

/*Script passé en "PROD" le 28/02/2018 */
ALTER TABLE `resultatsl1_reel` ADD `statut` TINYINT NOT NULL DEFAULT '0' AFTER `penaltyVisiteur`;

/*Script passé en "PROD" le 27/02/2018 */
ALTER TABLE `joueur_equipe` ADD `nb_csc` TINYINT(1) NULL AFTER `nb_but_reel`;

/*Script passé en "PROD" le 24/02/2018 */
ALTER TABLE `joueur_compo_equipe` ADD `nb_csc` TINYINT(1) NULL AFTER `nb_but_reel`;
ALTER TABLE `coach` DROP INDEX `ind_uni_nom`;
UPDATE coach SET mail = nom WHERE mail IS NULL;
ALTER TABLE `coach` MODIFY mail VARCHAR(50) NOT NULL;
INSERT INTO `nomenclature_tactique`(`code`, `date_debut`, `nb_def`, `nb_mil`, `nb_att`, `nb_dc`, `nb_dlg`, `nb_dld`,
`nb_mdef`, `nb_mc`, `nb_mg`, `nb_md`, `nb_mo`, `nb_ailg`, `nb_aild`, `nb_but`) VALUES
('4-3-3','2017-01-01',4,3,3,2,1,1,0,3,0,0,0,1,1,1),
('4-5-1','2017-01-01',4,5,1,2,1,1,1,3,1,0,0,0,0,1);

/*Script passé en "PROD" le 22/02/2018 */
UPDATE `nomenclature_equipes_reelles` SET `trigramme` = 'PSG' WHERE `nomenclature_equipes_reelles`.`trigramme` = 'PAR';
/*Script passé en "PROD" le 21/02/2018 */
CREATE TABLE `nomenclature_equipes_reelles` (
`trigramme` CHAR(3) NOT NULL,
`ville_maxi` VARCHAR(100) NOT NULL,
PRIMARY KEY (`trigramme`)
) ENGINE = InnoDB;
INSERT INTO `nomenclature_equipes_reelles` (`trigramme`, `ville_maxi`) VALUES
('AMN','Amiens'),
('ANG','Angers'),
('BDX','Bordeaux'),
('CAE','Caen'),
('DIJ','Dijon'),
('ETI','St Etienne'),
('GUI','Guingamp'),
('LIL','Lille'),
('LYO','Lyon'),
('MAR','Marseille'),
('MET','Metz'),
('MON','Monaco'),
('MTP','Montpellier'),
('NIC','Nice'),
('NTE','Nantes'),
('PAR','Paris SG'),
('REN','Rennes'),
('STR','Strasbourg'),
('TOU','Toulouse'),
('TRO','Troyes');

/*Script passé en "PROD" le 20/02/2018 */
ALTER TABLE `coach_ligue` ADD `masquee` BOOLEAN NOT NULL DEFAULT FALSE AFTER `date_validation`;
ALTER TABLE `coach` ADD `aff_ligue_masquee` BOOLEAN NOT NULL DEFAULT FALSE AFTER `date_maj`;
ALTER TABLE `calendrier_ligue` ADD `selectionneur` BOOLEAN NOT NULL DEFAULT FALSE AFTER `score_ext`;

/*Script passé en "PROD" le 18/02/2018 */
CREATE TABLE `nomenclature_questions_presse` (`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT , `categorie` VARCHAR(40) NOT NULL , `libelle` VARCHAR(255) NOT NULL, `date_debut` DATE NOT NULL , `date_fin` DATE NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `nomenclature_questions_presse`(`categorie`, `libelle`, `date_debut`) VALUES
('victoire','C\'est passé de justesse ce weekend mais les trois points sont pour vous, quel était l\'élément décisif de cette victoire ?','2017-01-01'),
('victoire','Votre superbe stratégie vous fait gagner ce weekend, comment vous sentez vous après cette belle victoire ?','2017-01-01'),
('victoire','Vous étiez chaud ce weekend, qu\'avez vous dit à vos joueurs avant d\'entrer sur le terrain ?','2017-01-01'),
('victoire','Quelle large victoire, ne pensez vous pas avoir manqué de respect à votre adversaire ?','2017-01-01'),
('victoire','Après une telle domination ce weekend, votre adversaire serait heureux de recevoir quelques conseils. Que voulez vous lui dire ?','2017-01-01'),
('nul','Vous terminez avec 1 seul point ce weekend, dans quel état d\'esprit êtes vous ?','2017-01-01'),
('nul','Le match se termine sur un match nul, quel élément vous a manqué pour aller chercher la victoire ?','2017-01-01'),
('nul','Ce match nul semble injuste au regard de votre prestation, comment allez vous motiver vos joueurs ?','2017-01-01'),
('defaite','Quelle injustice, le score ne reflète pas la réalité de votre prestation, qui est selon vous le fautif ?','2017-01-01'),
('defaite','Zéro point ce weekend, quelle déception, comment allez vous remotiver vos joueurs ?','2017-01-01'),
('defaite','Un match à oublier, que souhaitez vous dire à votre adversaire ?','2017-01-01'),
('avant match','C\'est un match clé pour vous ce weekend, quelle stratégie comptez vous adopter ?','2017-01-01'),
('avant match','Vous nous avez confié que c\'était interdit de perdre ce weekend, pouvez-vous nous expliquer pourquoi ?','2017-01-01'),
('avant match','Des rumeurs disent que vous êtes prêt à tout pour gagner ce weekend, pouvez-vous nous en dire plus ?','2017-01-01'),
('chambre','Après une telle leçon de football, vos adversaires ont étété trouvés en PLS dans les vestiaires, regrettez vous cette démonstration de force ?','2017-01-01'),
('chambre','Le coaching de votre adversaire a été risible et non professionnel, auriez vous des conseils à lui prodiguer ?','2017-01-01');

/* Script passée en "PROD" le 18/02/2017 */
ALTER TABLE `calendrier_reel` ADD `statut` TINYINT(1) NOT NULL DEFAULT '0' AFTER `date_heure_debut`;
UPDATE `calendrier_reel` SET statut = 2 WHERE date_heure_debut < NOW() AND num_journee != 26;

/* Script passée en "PROD" le 16/02/2017 */
ALTER TABLE `nomenclature_bonus_malus` ADD `libelle_court` VARCHAR(30) NOT NULL AFTER `code`;
/* ATTENTION : supprime toutes les tables ayant le code référencé et valorisé (compo, joueur_compo, bonus_malus) */
DELETE FROM nomenclature_bonus_malus;
INSERT INTO `nomenclature_bonus_malus`(`code`, `libelle_court`, `libelle`, `select_joueur`, `date_debut`) VALUES
('SEL_TRI', 'Sélectionneur','La Dech\' est dans les tribunes (+0.5 aux français)', FALSE ,'2017-01-01'),
('FUMIGENE', 'Fumigènes','Les stadiers étaient au café. Résultat, des fumigènes viennent gêner le gardien adverse (-1 note GB)', FALSE ,'2017-01-01'),
('DIN_ARB', 'Dîner','Un petit dîner avec l\'arbitre la veille et il oubliera de compter le premier but adverse (-1 but)', FALSE ,'2017-01-01'),
('FAM_STA', 'Famille','Quand sa famille est au stade, on se donne à fond (note de +1 pour un joueur)', TRUE ,'2017-01-01'),
('BUS', 'Bus','Le FCN de Claudio t\'inspire ! (pas de but virtuel)', FALSE ,'2017-01-01'),
('MAU_CRA', 'Mauvais crampons','Tu vires discrètement les crampons d\'un adversaire (note de -1 pour un joueur)', TRUE ,'2017-01-01'),
('BOUCHER', 'Boucher','Réincarnation de Canto sur le terrain ! Ton joueur prend un rouge et tu blesses un adversaire (les 2 joueurs ont 0)', TRUE ,'2017-01-01'),
('CHA_GB', 'Changement GB','Parce que ton gardien a passé plus de temps avec Ahamada que Neuer (remplacement tactique possible sur ton GB)', TRUE ,'2017-01-01'),
('PAR_TRU', 'Pari truqué','Pari truqué (buts doublés pour un joueur sur une mi-temps)', TRUE ,'2017-01-01'),
('CON_ZZ', 'Conseils ZZ','Les conseils de Zizou profitent à tout footballeur (+0.5 équipe)', FALSE ,'2017-01-01');


/* Script passée en "PROD" le 11/02/2017 */
CREATE TABLE nomenclature_style_coach (`code` VARCHAR(30) NOT NULL , `libelle` VARCHAR(255) NOT NULL, `description` VARCHAR(255) NOT NULL, `nom_image` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
INSERT INTO `nomenclature_style_coach`(`code`, `libelle`, `description`, `nom_image`, `date_debut`) VALUES
('CATENACCIO','Catenaccio', 'Si seulement on pouvait mettre 2 gardiens.', 'coach_ecole_italienne.png' ,'2017-01-01'),
('DEGLINGO','Le déglingo', 'Quand tu préfères que les scores des matchs ressemblent à des sets de tennis.', 'coach_ecole_italienne.png' ,'2017-01-01'),
('FOOT_TOTAL','Football total', 'Tout le monde attaque, tout le monde défend.', 'coach_ecole_italienne.png' ,'2017-01-01'),
('TIKI_TAKA','Tiki-Taka', 'Quand tu crois que faire 300 passes éqjivaut à 1 but.', 'coach_ecole_italienne.png' ,'2017-01-01'),
('GUEULARD','Le gueulard', '...', 'coach_ecole_italienne.png' ,'2017-01-01'),
('GENIE','Le génie', 'Quand tu gagnes toujours tout du premier coup.', 'coach_ecole_italienne.png' ,'2017-01-01'),
('ORATEUR','L\'orateur', 'Quand t\'es meilleur en conf. de presse qu\'en match.', 'coach_ecole_italienne.png' ,'2017-01-01'),
('OBSTINE','L\'obstiné', 'Quand tu joues toujours avec la même tactique.', 'coach_ecole_italienne.png' ,'2017-01-01');

ALTER TABLE `equipe` ADD `code_style_coach` VARCHAR(30) AFTER `stade`;
ALTER TABLE `equipe` ADD FOREIGN KEY (`code_style_coach`) REFERENCES `nomenclature_style_coach`(`code`) ON DELETE NO ACTION ON UPDATE NO ACTION;
UPDATE equipe SET code_style_coach = 'CATENACCIO';
ALTER TABLE `equipe` CHANGE `code_style_coach` `code_style_coach` VARCHAR(30) NOT NULL;

ALTER TABLE joueur_compo_equipe DROP FOREIGN KEY joueur_compo_equipe_ibfk_1;
ALTER TABLE `joueur_compo_equipe` DROP `code_bonus_malus`;
