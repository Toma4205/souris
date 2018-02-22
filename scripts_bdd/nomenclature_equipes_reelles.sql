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
('PSG','Paris SG'),
('REN','Rennes'),
('STR','Strasbourg'),
('TOU','Toulouse'),
('TRO','Troyes');
