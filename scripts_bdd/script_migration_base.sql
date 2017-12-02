ALTER TABLE `calendrier_ligue` ADD `num_journee_cal_reel` TINYINT(3) UNSIGNED NOT NULL AFTER `id_ligue`;
UPDATE calendrier_ligue SET num_journee_cal_reel = (num_journee + 13);
ALTER TABLE `calendrier_ligue` ADD FOREIGN KEY (`num_journee_cal_reel`) REFERENCES `calendrier_reel`(`num_journee`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `joueur_compo_equipe` CHANGE `note_min` `note_min_remplacement` DECIMAL(3,1);
ALTER TABLE `joueur_compo_equipe` CHANGE `numero_remplacant` `id_joueur_reel_remplacant` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `joueur_compo_equipe` ADD FOREIGN KEY (`id_joueur_reel_remplacant`) REFERENCES `joueur_reel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `joueur_compo_equipe` ADD `nb_but_reel` TINYINT(3) UNSIGNED AFTER `note`;
ALTER TABLE `joueur_compo_equipe` ADD `nb_but_virtuel` TINYINT(3) UNSIGNED AFTER `nb_but_reel`;
ALTER TABLE `joueur_compo_equipe` ADD `numero_remplacement` TINYINT(3) UNSIGNED AFTER `id_joueur_reel_remplacant`;
