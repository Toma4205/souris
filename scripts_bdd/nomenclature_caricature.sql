CREATE TABLE nomenclature_caricature (`code` VARCHAR(20) NOT NULL , `libelle_court` VARCHAR(50) NOT NULL , `libelle` VARCHAR(100) NOT NULL , `date_debut` DATE NOT NULL , `date_fin` DATE NULL , PRIMARY KEY (`code`)) ENGINE = InnoDB;
INSERT INTO `nomenclature_caricature`(`code`, `libelle`, `date_debut`) 
VALUES ('SOURIS', 'La souris', 'Le dernier de la ligue.', '2017-01-01'),
('CHAMPION', 'Le champion', 'Le champion de la ligue', '2017-01-01'),
('VICTIME', 'La victime', 'Le coach qui a reçu le plus de Malus de la part de ses confrères.', '2017-01-01'),
('PEPITE', 'Le dénicheur de pépite', 'Le coach qui a un joueur dont le ratio M€/But est inférieur à X.', '2017-01-01'),
('PIGEON', 'Le pigeon', 'Le coach ayant l\'attaquant le plus cher qui n\'a pas marqué.', '2017-01-01'),
('AUC_TROPHEE', 'Le Coubertin', 'Ni premier, ni dernier et aucun trophée distinctif... Merci d\'avoir participé.', '2017-01-01'),
('PIRE_ATTAQUE','La pire attaque', 'Le coach qui a joué tous ses matchs à la Beaujoire.', '2017-01-01'),
('TONTON_PAT','Infériorité numérique', 'Le coach qui a eu le plus de Tonton Pat\'. Par manque de chance... ou de talent !', '2017-01-01'),
('DEPENSIER','Le dépensier', 'Le coach qui a le plus de joueurs qui n\'ont jamais été notés.'),
('ECONOME','L\'économe', 'Le coach qui a le plus gros budget restant.'),
('VOYANT','Le voyant', 'Le coach qui a trouvé un joueur au 3ème tour mercato (ou plus) et qui est dans le top 10 des buteurs.'),
('PIGNON','Mr PIGNON', 'Le coach qui a effectué le plus de remplacements tactiques sur des buteurs.', '2017-01-01'),
('BUT_REEL_ENC','Le Jean Marie pas de chance', 'Mauvais alignement des planètes... Le coach qui a encaissé le plus de buts réels.', '2017-01-01'),
('BUT_VIRTUEL_ENC','Le Jean Marie pas de défense', 'Le coach qui a encaissé le plus de buts virtuels.', '2017-01-01');