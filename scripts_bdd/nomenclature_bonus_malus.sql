INSERT INTO `nomenclature_bonus_malus`(`code`, `libelle_court`, `libelle`, `select_joueur`, `date_debut`) VALUES
('FUMIGENE', 'Fumigènes','Les stadiers étaient au café. Résultat, des fumigènes viennent gêner le gardien adverse (-1 note GB)', FALSE ,'2017-01-01'),
('DIN_ARB', 'Dîner','Un petit dîner avec l\'arbitre la veille et il oubliera de compter le premier but adverse (-1 but)', FALSE ,'2017-01-01'),
('FAM_STA', 'Famille','Quand sa famille est au stade, on se donne à fond (note de +1 pour un joueur)', TRUE ,'2017-01-01'),
('BUS', 'Bus','Le FCN de Claudio t\'inspire ! (pas de but virtuel)', FALSE ,'2017-01-01'),
('MAU_CRA', 'Mauvais crampons','Tu vires discrètement les crampons d\'un adversaire (note de -1 pour un joueur)', TRUE ,'2017-01-01'),
('BOUCHER', 'Boucher','Réincarnation de Canto sur le terrain ! Ton joueur prend un rouge et tu blesses un adversaire (les 2 joueurs ont la note de 0)', TRUE ,'2017-01-01'),
('CHA_GB', 'Changement GB','Parce que ton gardien a passé plus de temps avec Ahamada que Neuer (remplacement tactique possible sur ton GB)', TRUE ,'2017-01-01'),
('CON_ZZ', 'Conseils ZZ','Les conseils de Zizou profitent à tout footballeur (+0.5 équipe)', FALSE ,'2017-01-01');

INSERT INTO `quantite_bonus_malus`(`code`, `nb_joueur`, `nb_pack_classique`, `nb_pack_folie`) VALUES
('FAM_STA','2' ,'1', '1'),('FAM_STA','3' ,'1', '1'),('FAM_STA','4' ,'1', '1'),('FAM_STA','5' ,'2', '2'),('FAM_STA','6' ,'2', '2'),
('FAM_STA','7' ,'3', '2'),('FAM_STA','8' ,'3', '2'),('FAM_STA','9' ,'3', '2'),('FAM_STA','10' ,'3', '2'),
('DIN_ARB','2' ,'0', '0'),('DIN_ARB','3' ,'1', '1'),('DIN_ARB','4' ,'1', '1'),('DIN_ARB','5' ,'1', '1'),('DIN_ARB','6' ,'1', '1'),
('DIN_ARB','7' ,'1', '1'),('DIN_ARB','8' ,'1', '1'),('DIN_ARB','9' ,'2', '1'),('DIN_ARB','10' ,'2', '2'),
('FUMIGENE','2' ,'0', '0'),('FUMIGENE','3' ,'0', '0'),('FUMIGENE','4' ,'1', '1'),('FUMIGENE','5' ,'1', '1'),('FUMIGENE','6' ,'1', '1'),
('FUMIGENE','7' ,'1', '1'),('FUMIGENE','8' ,'2', '1'),('FUMIGENE','9' ,'2', '1'),('FUMIGENE','10' ,'2', '1'),
('BUS','2' ,'0', '0'),('BUS','3' ,'0', '0'),('BUS','4' ,'0', '0'),('BUS','5' ,'0', '0'),('BUS','6' ,'1', '1'),
('BUS','7' ,'1', '1'),('BUS','8' ,'1', '1'),('BUS','9' ,'1', '1'),('BUS','10' ,'2', '1'),
('CON_ZZ','2' ,'0', '0'),('CON_ZZ','3' ,'0', '0'),('CON_ZZ','4' ,'0', '0'),('CON_ZZ','5' ,'0', '0'),('CON_ZZ','6' ,'0', '0'),
('CON_ZZ','7' ,'0', '1'),('CON_ZZ','8' ,'0', '1'),('CON_ZZ','9' ,'0', '1'),('CON_ZZ','10' ,'0', '1'),
('BOUCHER','2' ,'0', '0'),('BOUCHER','3' ,'0', '0'),('BOUCHER','4' ,'0', '0'),('BOUCHER','5' ,'0', '0'),('BOUCHER','6' ,'0', '0'),
('BOUCHER','7' ,'0', '0'),('BOUCHER','8' ,'0', '1'),('BOUCHER','9' ,'0', '1'),('BOUCHER','10' ,'0', '1'),
('MAU_CRA','2' ,'0', '0'),('MAU_CRA','3' ,'0', '0'),('MAU_CRA','4' ,'0', '0'),('MAU_CRA','5' ,'0', '0'),('MAU_CRA','6' ,'0', '0'),
('MAU_CRA','7' ,'0', '0'),('MAU_CRA','8' ,'0', '0'),('MAU_CRA','9' ,'0', '1'),('MAU_CRA','10' ,'0', '1'),
('CHA_GB','2' ,'0', '0'),('CHA_GB','3' ,'0', '0'),('CHA_GB','4' ,'0', '0'),('CHA_GB','5' ,'0', '0'),('CHA_GB','6' ,'0', '0'),
('CHA_GB','7' ,'0', '0'),('CHA_GB','8' ,'0', '0'),('CHA_GB','9' ,'0', '1'),('CHA_GB','10' ,'0', '1');
