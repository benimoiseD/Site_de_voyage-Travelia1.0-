-- Migration pour ajouter le pays de résidence aux utilisateurs existants
USE travelia2_safari_rdc;

ALTER TABLE users
    ADD COLUMN pays_residence VARCHAR(100) NOT NULL DEFAULT '' AFTER Email;
