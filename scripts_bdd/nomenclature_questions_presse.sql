
CREATE TABLE `nomenclature_questions_presse` (`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT , `categorie` VARCHAR(40) NOT NULL , `libelle` VARCHAR(255) NOT NULL, date_debut` DATE NOT NULL , `date_fin` DATE NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB;

INSERT INTO `nomenclature_questions_presse`(`id`, `categorie`, `libelle`, `date_debut`) VALUES
('', 'victoire','C\'est passé de justesse ce weekend mais les trois points sont pour vous, quel était l\'élément décisif de cette victoire ?','2017-01-01'),
('', 'victoire','Votre superbe stratégie vous fait gagner ce weekend, comment vous sentez vous après cette belle victoire ?','2017-01-01'),
('', 'victoire','Vous étiez chaud ce weekend, qu\'avez vous dit à vos joueurs avant d\'entrer sur le terrain ?','2017-01-01'),
('', 'victoire','Quelle large victoire, ne pensez vous pas avoir manqué de respect à votre adversaire ?','2017-01-01'),
('', 'victoire','Après une telle domination ce weekend, votre adversaire serait heureux de recevoir quelques conseils. Que voulez vous lui dire ?','2017-01-01'),
('', 'nul','Vous terminez avec 1 seul point ce weekend, dans quel état d\'esprit êtes vous ?','2017-01-01'),
('', 'nul','Le match se termine sur un match nul, quel élément vous a manqué pour aller chercher la victoire ?','2017-01-01'),
('', 'nul','Ce match nul semble injuste au regard de votre prestation, comment allez vous motiver vos joueurs ?','2017-01-01'),
('', 'defaite','Quelle injustice, le score ne reflète pas la réalité de votre prestation, qui est selon vous le fautif ?','2017-01-01'),
('', 'defaite','Zéro point ce weekend, quelle déception, comment allez vous remotiver vos joueurs ?','2017-01-01');
('', 'defaite','Un match à oublier, que souhaitez vous dire à votre adversaire ?','2017-01-01');
('', 'avant match','C\'est un match clé pour vous ce weekend, quelle stratégie comptez vous adopter ?','2017-01-01');
('', 'avant match','Vous nous avez confié que c\'était interdit de perdre ce weekend, pouvez-vous nous expliquer pourquoi ?','2017-01-01');
('', 'avant match','Des rumeurs disent que vous êtes prêt à tout pour gagner ce weekend, pouvez-vous nous en dire plus ?','2017-01-01');
('', 'chambre','Après une telle leçon de football, vos adversaires ont étété trouvés en PLS dans les vestiaires, regrettez vous cette démonstration de force ?','2017-01-01');
('', 'chambre','Le coaching de votre adversaire a été risible et non professionnel, auriez vous des conseils à lui prodiguer ?','2017-01-01');