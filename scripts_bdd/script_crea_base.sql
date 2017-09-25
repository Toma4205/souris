-- creation base
CREATE DATABASE souris CHARACTER SET 'utf8';

CREATE TABLE Coach (
    id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(40) NOT NULL,
    code_postal CHAR(5),
    mail VARCHAR(50) NOT NULL,
    mot_de_passe CHAR(32) NOT NULL,
    INDEX ind_nom (nom(10)),
    --UNIQUE ind_uni_mail (mail) 
);

CREATE TABLE Ligue (
    id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(40) NOT NULL,
    ...
);

CREATE TABLE Coach_Ligue (
    id_coach MEDIUMINT UNSIGNED NOT NULL,
    id_ligue MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id_coach, id_ligue),
    FOREIGN KEY (id_coach) REFERENCES Coach (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_ligue) REFERENCES Ligue (id) ON DELETE CASCADE ON UPDATE CASCADE
);
