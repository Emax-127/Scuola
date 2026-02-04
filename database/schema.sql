-- ============================================================================
-- DATABASE CREATION SCRIPT
-- Sistema Gestione Ticket Riparazione
-- ============================================================================

-- Create database
CREATE DATABASE IF NOT EXISTS RiparazioniDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE RiparazioniDB;

-- ============================================================================
-- TABLE: USERS
-- Gestione utenti del sistema con autenticazione
-- ============================================================================
CREATE TABLE USERS (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    ruolo ENUM('PDA', 'RIPARATORE', 'ADMIN') NOT NULL,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attivo BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_ruolo (ruolo)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE: PDA (Punti Di Accettazione)
-- Negozi/riparatori che aprono ticket
-- ============================================================================
CREATE TABLE PDA (
    pda_id INT AUTO_INCREMENT PRIMARY KEY,
    codice_pda VARCHAR(20) NOT NULL UNIQUE,
    nome_negozio VARCHAR(100) NOT NULL,
    indirizzo VARCHAR(200),
    telefono VARCHAR(20),
    email VARCHAR(100),
    user_id INT NOT NULL UNIQUE,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_codice_pda (codice_pda)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE: CLIENTI
-- Clienti finali che portano apparecchi da riparare
-- ============================================================================
CREATE TABLE CLIENTI (
    cliente_id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cognome (cognome),
    INDEX idx_telefono (telefono),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE: TIPI_DISPOSITIVO
-- Catalogo tipi di dispositivi riparabili
-- ============================================================================
CREATE TABLE TIPI_DISPOSITIVO (
    tipo_id INT AUTO_INCREMENT PRIMARY KEY,
    nome_tipo VARCHAR(50) NOT NULL UNIQUE,
    descrizione VARCHAR(200),
    INDEX idx_nome_tipo (nome_tipo)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE: MARCHE
-- Catalogo marche dei dispositivi
-- ============================================================================
CREATE TABLE MARCHE (
    marca_id INT AUTO_INCREMENT PRIMARY KEY,
    nome_marca VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_nome_marca (nome_marca)
) ENGINE=InnoDB;

-- ============================================================================
-- TABLE: TICKETS
-- Richieste di riparazione (ticket)
-- ============================================================================
CREATE TABLE TICKETS (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    pda_id INT NOT NULL,
    cliente_id INT NOT NULL,
    tipo_id INT NOT NULL,
    marca_id INT NOT NULL,
    modello VARCHAR(100) NOT NULL,
    data_apertura DATE NOT NULL DEFAULT (CURRENT_DATE),
    data_chiusura DATE NULL,
    stato ENUM('APERTO', 'IN_RIPARAZIONE', 'CHIUSO') NOT NULL DEFAULT 'APERTO',
    tempo_stimato INT NULL COMMENT 'Tempo stimato in giorni',
    difetto_segnalato TEXT NOT NULL,
    note_riparatore TEXT NULL,
    esito_riparazione ENUM('RIPARATO', 'NON_RIPARATO') NULL,
    data_ultima_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (pda_id) REFERENCES PDA(pda_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (cliente_id) REFERENCES CLIENTI(cliente_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (tipo_id) REFERENCES TIPI_DISPOSITIVO(tipo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (marca_id) REFERENCES MARCHE(marca_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    
    -- Indexes per ottimizzazione query
    INDEX idx_stato (stato),
    INDEX idx_data_apertura (data_apertura),
    INDEX idx_data_chiusura (data_chiusura),
    INDEX idx_pda (pda_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_marca_modello (marca_id, modello),
    INDEX idx_tipo (tipo_id),
    INDEX idx_difetto (difetto_segnalato(100)),
    
    -- Business Logic Constraints
    CONSTRAINT chk_data_chiusura CHECK (data_chiusura IS NULL OR data_chiusura >= data_apertura),
    CONSTRAINT chk_tempo_stimato CHECK (tempo_stimato IS NULL OR tempo_stimato > 0)
) ENGINE=InnoDB;

-- ============================================================================
-- TRIGGERS per vincoli di integrità business logic
-- ============================================================================

DELIMITER //

-- Trigger: verifica che se stato=CHIUSO allora data_chiusura e esito siano valorizzati
CREATE TRIGGER trg_tickets_before_insert
BEFORE INSERT ON TICKETS
FOR EACH ROW
BEGIN
    IF NEW.stato = 'CHIUSO' THEN
        IF NEW.data_chiusura IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'data_chiusura deve essere valorizzata se stato è CHIUSO';
        END IF;
        IF NEW.esito_riparazione IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'esito_riparazione deve essere valorizzato se stato è CHIUSO';
        END IF;
    END IF;
    
    IF NEW.stato != 'CHIUSO' AND NEW.data_chiusura IS NOT NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'data_chiusura deve essere NULL se stato non è CHIUSO';
    END IF;
END//

CREATE TRIGGER trg_tickets_before_update
BEFORE UPDATE ON TICKETS
FOR EACH ROW
BEGIN
    IF NEW.stato = 'CHIUSO' THEN
        IF NEW.data_chiusura IS NULL THEN
            SET NEW.data_chiusura = CURRENT_DATE;
        END IF;
        IF NEW.esito_riparazione IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'esito_riparazione deve essere valorizzato se stato è CHIUSO';
        END IF;
    END IF;
    
    IF NEW.stato != 'CHIUSO' THEN
        SET NEW.data_chiusura = NULL;
        SET NEW.esito_riparazione = NULL;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- SAMPLE DATA - Dati di esempio per testing
-- ============================================================================

-- Insert sample users
INSERT INTO USERS (username, password_hash, email, ruolo) VALUES
('pda_rossi', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789', 'rossi@pda.it', 'PDA'),
('pda_bianchi', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789', 'bianchi@pda.it', 'PDA'),
('riparatore1', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789', 'riparatore1@azienda.it', 'RIPARATORE'),
('admin', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789', 'admin@azienda.it', 'ADMIN');

-- Insert sample PDA
INSERT INTO PDA (codice_pda, nome_negozio, indirizzo, telefono, email, user_id) VALUES
('PDA001', 'Elettronica Rossi', 'Via Roma 123, Milano', '02-12345678', 'rossi@pda.it', 1),
('PDA002', 'TecnoService Bianchi', 'Corso Italia 45, Roma', '06-87654321', 'bianchi@pda.it', 2);

-- Insert sample clients
INSERT INTO CLIENTI (nome, cognome, telefono, email) VALUES
('Mario', 'Verdi', '333-1234567', 'mario.verdi@email.it'),
('Luigi', 'Neri', '340-7654321', 'luigi.neri@email.it'),
('Anna', 'Gialli', '348-9876543', 'anna.gialli@email.it'),
('Paolo', 'Blu', '339-5551234', 'paolo.blu@email.it');

-- Insert device types
INSERT INTO TIPI_DISPOSITIVO (nome_tipo, descrizione) VALUES
('Telefono Cellulare', 'Smartphone e cellulari'),
('Televisore', 'TV LCD, LED, OLED, Plasma'),
('Tablet', 'Tablet Android e iOS');

-- Insert brands
INSERT INTO MARCHE (nome_marca) VALUES
('Samsung'),
('Apple'),
('LG'),
('Sony'),
('Huawei'),
('Xiaomi');

-- Insert sample tickets
INSERT INTO TICKETS (pda_id, cliente_id, tipo_id, marca_id, modello, data_apertura, stato, tempo_stimato, difetto_segnalato) VALUES
(1, 1, 1, 1, 'Galaxy S21', '2024-12-01', 'CHIUSO', 5, 'sostituzione display'),
(1, 2, 1, 2, 'iPhone 13', '2024-12-15', 'IN_RIPARAZIONE', 7, 'Batteria scarica rapidamente'),
(2, 3, 2, 3, 'OLED 55"', '2024-11-20', 'CHIUSO', 10, 'Nessuna immagine'),
(2, 4, 1, 1, 'Galaxy A52', '2025-01-05', 'APERTO', 5, 'sostituzione display'),
(1, 1, 1, 4, 'Xperia 5', '2025-01-10', 'IN_RIPARAZIONE', 3, 'Non si accende');

-- Update closed tickets with closure date and outcome
UPDATE TICKETS SET data_chiusura = '2024-12-06', esito_riparazione = 'RIPARATO' WHERE ticket_id = 1;
UPDATE TICKETS SET data_chiusura = '2024-11-30', esito_riparazione = 'RIPARATO' WHERE ticket_id = 3;

-- ============================================================================
-- VIEWS - Viste utili per query frequenti
-- ============================================================================

-- Vista completa tickets con informazioni aggregate
CREATE VIEW V_TICKETS_COMPLETI AS
SELECT 
    t.ticket_id,
    t.data_apertura,
    t.data_chiusura,
    t.stato,
    t.tempo_stimato,
    t.difetto_segnalato,
    t.note_riparatore,
    t.esito_riparazione,
    p.codice_pda,
    p.nome_negozio,
    CONCAT(c.nome, ' ', c.cognome) AS nome_cliente,
    c.telefono AS telefono_cliente,
    c.email AS email_cliente,
    td.nome_tipo AS tipo_dispositivo,
    m.nome_marca AS marca,
    t.modello,
    DATEDIFF(COALESCE(t.data_chiusura, CURRENT_DATE), t.data_apertura) AS giorni_lavorazione
FROM TICKETS t
JOIN PDA p ON t.pda_id = p.pda_id
JOIN CLIENTI c ON t.cliente_id = c.cliente_id
JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
JOIN MARCHE m ON t.marca_id = m.marca_id;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
