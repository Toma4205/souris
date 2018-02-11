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
