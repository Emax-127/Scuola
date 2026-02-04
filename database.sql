-- Script SQL per creare il database e una tabella di esempio
-- Esegui questo script in phpMyAdmin o dalla console MySQL

-- Crea il database
CREATE DATABASE IF NOT EXISTS agenda_studente CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usa il database
USE agenda_studente;

-- Crea una tabella di esempio per l'agenda studente
CREATE TABLE IF NOT EXISTS compiti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia VARCHAR(100) NOT NULL,
    descrizione TEXT NOT NULL,
    data_consegna DATE NOT NULL,
    completato BOOLEAN DEFAULT FALSE,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserisci alcuni dati di esempio
INSERT INTO compiti (materia, descrizione, data_consegna, completato) VALUES
('Matematica', 'Esercizi pagina 45-47', '2026-02-10', FALSE),
('Italiano', 'Leggere capitolo 3 del libro', '2026-02-08', FALSE),
('Storia', 'Ricerca sulla Rivoluzione Francese', '2026-02-15', FALSE);
