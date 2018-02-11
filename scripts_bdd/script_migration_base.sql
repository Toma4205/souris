/* Script passée en "PROD" le ... */

ALTER TABLE `equipe` ADD `code_style_coach` VARCHAR(30) AFTER `stade`;
ALTER TABLE `equipe` ADD FOREIGN KEY (`code_style_coach`) REFERENCES `nomenclature_style_coach`(`code`) ON DELETE NO ACTION ON UPDATE NO ACTION;
UPDATE equipe SET code_style_coach = 'CATENACCIO';
ALTER TABLE `equipe` CHANGE `code_style_coach` `code_style_coach` VARCHAR(30) NOT NULL;
