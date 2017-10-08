INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (1, 'bob', 'bob', 'bob@yahoo.fr', '44555', NOW(), NOW());
INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (2, 'marc', 'marc', NULL, NULL, NOW(), NOW());
INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (3, 'jean', 'jean', NULL, NULL, NOW(), NOW());
INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (4, 'jason', 'jason', NULL, NULL, NOW(), NOW());
INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (5, 'xav', 'xav', NULL, NULL, NOW(), NOW());
INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (6, 'paul', 'paul', NULL, NULL, NOW(), NOW());
INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (7, 'jessica', 'jessica', NULL, NULL, NOW(), NOW());
INSERT INTO coach (id, nom, mot_de_passe, mail, code_postal, date_creation, date_maj)
  VALUES (8, 'ben', 'ben', NULL, NULL, NOW(), NOW());

INSERT INTO confrere (id_coach, id_coach_confrere, date_debut)
  VALUES (1, 2, NOW());
INSERT INTO confrere (id_coach, id_coach_confrere, date_debut)
  VALUES (1, 3, NOW());
INSERT INTO confrere (id_coach, id_coach_confrere, date_debut)
  VALUES (1, 6, NOW());
INSERT INTO confrere (id_coach, id_coach_confrere, date_debut)
  VALUES (1, 5, NOW());
INSERT INTO confrere (id_coach, id_coach_confrere, date_debut)
  VALUES (1, 7, NOW());
INSERT INTO confrere (id_coach, id_coach_confrere, date_debut)
  VALUES (5, 1, NOW());
INSERT INTO confrere (id_coach, id_coach_confrere, date_debut)
  VALUES (5, 7, NOW());
