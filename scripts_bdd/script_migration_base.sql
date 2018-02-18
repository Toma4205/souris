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
