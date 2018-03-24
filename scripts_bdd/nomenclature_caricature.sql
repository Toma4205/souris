CREATE TABLE nomenclature_caricature (`code` VARCHAR(30) NOT NULL , `libelle_court` VARCHAR(50) NOT NULL , `libelle` VARCHAR(255) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
INSERT INTO `nomenclature_caricature`(`code`, `libelle_court`, `libelle`, `date_debut`)
VALUES ('SOURIS', 'La souris', 'Dernier de la ligue.', '2017-01-01'),
('CHAMPION', 'Le champion', 'Champion de la ligue.', '2017-01-01'),
('VICTIME', 'La victime', 'Coach qui a subi le plus de Malus : %T.', '2017-01-01'),
('PEPITE', 'Le dénicheur de pépite', 'Joueur dont le ratio M€/But est inférieur à 1 :<br/> %J.', '2017-01-01'),
('PIGEON', 'Le pigeon', 'Attaquant le plus cher de la ligue qui n\'a pas marqué :<br/> %J (%T M€).', '2017-01-01'),
('AUC_TROPHEE', 'Le Coubertin', 'Ni premier, ni dernier et aucun trophée distinctif... Merci d\'avoir participé.', '2017-01-01'),
('PIRE_ATTAQUE','La pire attaque', 'Coach qui a joué tous ses matchs à la Beaujoire : %T but(s).', '2017-01-01'),
('TONTON_PAT','Infériorité numérique', 'Coach qui a eu le plus de Tonton Pat\' : %T.<br/>Par manque de chance... ou de talent !', '2017-01-01'),
('DEPENSIER','Le dépensier', 'Coach qui a dépensé le plus pour des joueurs<br/> qui n\'ont jamais joués : %T M€.', '2017-01-01'),
('ECONOME','L\'économe', 'Coach qui a le plus gros budget restant : %T M€.', '2017-01-01'),
('VOYANT','Le voyant', 'Joueur acheté au 3ème tour mercato (ou plus) et<br/> qui est dans le top 10 des buteurs : %J (%T but(s)).', '2017-01-01'),
('PIGNON','Mr PIGNON', 'Coach qui a effectué le plus de remplacements tactiques sur des buteurs : %T.', '2017-01-01'),
('BUT_REEL_ENC','Le Jean Marie pas de chance', 'Mauvais alignement des planètes... Coach qui a encaissé le plus de buts réels : %T.', '2017-01-01'),
('BUT_VIRTUEL_ENC','Le Jean Marie pas de défense', 'Coach qui a encaissé le plus de buts virtuels : %T.', '2017-01-01');
