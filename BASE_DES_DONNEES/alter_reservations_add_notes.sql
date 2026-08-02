-- Migration pour ajouter la colonne notes aux réservations existantes
USE travelia2_safari_rdc;

ALTER TABLE reservations
    ADD COLUMN notes TEXT NULL AFTER type_sejour;
