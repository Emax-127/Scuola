-- ============================================================================
-- QUERY SQL RICHIESTE
-- Sistema Gestione Ticket Riparazione
-- ============================================================================

USE RiparazioniDB;

-- ============================================================================
-- QUERY 1: Visualizzare l'elenco, in ordine cronologico, di tutti gli 
--          interventi di riparazione di telefoni cellulari del tipo 
--          "sostituzione display" effettuati nell'arco di un mese
-- ============================================================================

-- Parametri: @anno (es. 2024), @mese (es. 12)
-- Esempio: visualizzare riparazioni di dicembre 2024

SELECT 
    t.ticket_id AS 'Codice Ticket',
    t.data_apertura AS 'Data Apertura',
    t.data_chiusura AS 'Data Chiusura',
    CONCAT(c.nome, ' ', c.cognome) AS 'Cliente',
    c.telefono AS 'Telefono',
    m.nome_marca AS 'Marca',
    t.modello AS 'Modello',
    t.difetto_segnalato AS 'Difetto',
    t.stato AS 'Stato',
    t.esito_riparazione AS 'Esito',
    DATEDIFF(COALESCE(t.data_chiusura, CURRENT_DATE), t.data_apertura) AS 'Giorni Lavorazione'
FROM TICKETS t
JOIN CLIENTI c ON t.cliente_id = c.cliente_id
JOIN MARCHE m ON t.marca_id = m.marca_id
JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
WHERE td.nome_tipo = 'Telefono Cellulare'
    AND t.difetto_segnalato LIKE '%sostituzione display%'
    AND YEAR(t.data_apertura) = 2024  -- Parametro @anno
    AND MONTH(t.data_apertura) = 12   -- Parametro @mese
ORDER BY t.data_apertura ASC, t.ticket_id ASC;

-- Versione con parametri preparati (per prepared statements)
/*
SELECT 
    t.ticket_id, t.data_apertura, t.data_chiusura,
    CONCAT(c.nome, ' ', c.cognome) AS cliente,
    c.telefono, m.nome_marca, t.modello, t.difetto_segnalato,
    t.stato, t.esito_riparazione
FROM TICKETS t
JOIN CLIENTI c ON t.cliente_id = c.cliente_id
JOIN MARCHE m ON t.marca_id = m.marca_id
JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
WHERE td.nome_tipo = 'Telefono Cellulare'
    AND t.difetto_segnalato LIKE '%sostituzione display%'
    AND t.data_apertura >= ? AND t.data_apertura < ?
ORDER BY t.data_apertura ASC;
-- ? = '2024-12-01', ? = '2025-01-01'
*/


-- ============================================================================
-- QUERY 2: Visualizzare i dati dei clienti ai quali, dopo trenta giorni, 
--          non è stato ancora riparato l'articolo consegnato
-- ============================================================================

SELECT DISTINCT
    c.cliente_id AS 'ID Cliente',
    c.nome AS 'Nome',
    c.cognome AS 'Cognome',
    c.telefono AS 'Telefono',
    c.email AS 'Email',
    t.ticket_id AS 'Ticket',
    t.data_apertura AS 'Data Apertura',
    DATEDIFF(CURRENT_DATE, t.data_apertura) AS 'Giorni Trascorsi',
    m.nome_marca AS 'Marca',
    t.modello AS 'Modello',
    td.nome_tipo AS 'Tipo Dispositivo',
    t.stato AS 'Stato'
FROM CLIENTI c
JOIN TICKETS t ON c.cliente_id = t.cliente_id
JOIN MARCHE m ON t.marca_id = m.marca_id
JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
WHERE t.stato != 'CHIUSO'  -- Ticket non ancora chiuso
    AND DATEDIFF(CURRENT_DATE, t.data_apertura) > 30  -- Più di 30 giorni
ORDER BY DATEDIFF(CURRENT_DATE, t.data_apertura) DESC, c.cognome ASC;


-- ============================================================================
-- QUERY 3: Data una marca ed un modello, visualizzare la durata media 
--          degli interventi di riparazione per i prodotti di tale marca e modello
-- ============================================================================

-- Parametri: @nome_marca (es. 'Samsung'), @modello (es. 'Galaxy S21')

SELECT 
    m.nome_marca AS 'Marca',
    t.modello AS 'Modello',
    COUNT(t.ticket_id) AS 'Numero Riparazioni',
    AVG(DATEDIFF(t.data_chiusura, t.data_apertura)) AS 'Durata Media (giorni)',
    MIN(DATEDIFF(t.data_chiusura, t.data_apertura)) AS 'Durata Minima (giorni)',
    MAX(DATEDIFF(t.data_chiusura, t.data_apertura)) AS 'Durata Massima (giorni)',
    SUM(CASE WHEN t.esito_riparazione = 'RIPARATO' THEN 1 ELSE 0 END) AS 'Riparazioni Riuscite',
    SUM(CASE WHEN t.esito_riparazione = 'NON_RIPARATO' THEN 1 ELSE 0 END) AS 'Riparazioni Fallite'
FROM TICKETS t
JOIN MARCHE m ON t.marca_id = m.marca_id
WHERE m.nome_marca = 'Samsung'  -- Parametro @nome_marca
    AND t.modello = 'Galaxy S21'  -- Parametro @modello
    AND t.stato = 'CHIUSO'  -- Solo ticket chiusi hanno durata effettiva
    AND t.data_chiusura IS NOT NULL
GROUP BY m.nome_marca, t.modello;

-- Versione senza filtro specifico (mostra tutti i modelli di una marca)
/*
SELECT 
    m.nome_marca, t.modello,
    COUNT(t.ticket_id) AS num_riparazioni,
    ROUND(AVG(DATEDIFF(t.data_chiusura, t.data_apertura)), 1) AS durata_media_giorni
FROM TICKETS t
JOIN MARCHE m ON t.marca_id = m.marca_id
WHERE m.nome_marca = ?  -- Parametro marca
    AND t.stato = 'CHIUSO'
GROUP BY m.nome_marca, t.modello
ORDER BY num_riparazioni DESC;
*/


-- ============================================================================
-- QUERY 4: Calcolare e visualizzare quanti interventi di riparazione, 
--          andati a buon fine, sono stati effettuati, suddivisi per marca, 
--          nell'arco di un anno
-- ============================================================================

-- Parametri: @anno (es. 2024)

SELECT 
    m.nome_marca AS 'Marca',
    td.nome_tipo AS 'Tipo Dispositivo',
    COUNT(t.ticket_id) AS 'Riparazioni Riuscite',
    ROUND(AVG(DATEDIFF(t.data_chiusura, t.data_apertura)), 1) AS 'Durata Media (giorni)',
    MIN(t.data_apertura) AS 'Prima Riparazione',
    MAX(t.data_chiusura) AS 'Ultima Riparazione'
FROM TICKETS t
JOIN MARCHE m ON t.marca_id = m.marca_id
JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
WHERE YEAR(t.data_apertura) = 2024  -- Parametro @anno
    AND t.stato = 'CHIUSO'
    AND t.esito_riparazione = 'RIPARATO'  -- Solo riparazioni andate a buon fine
GROUP BY m.nome_marca, td.nome_tipo
ORDER BY COUNT(t.ticket_id) DESC, m.nome_marca ASC;

-- Versione semplificata (solo totale per marca)
/*
SELECT 
    m.nome_marca,
    COUNT(t.ticket_id) AS riparazioni_riuscite
FROM TICKETS t
JOIN MARCHE m ON t.marca_id = m.marca_id
WHERE YEAR(t.data_apertura) = ?
    AND t.stato = 'CHIUSO'
    AND t.esito_riparazione = 'RIPARATO'
GROUP BY m.nome_marca
ORDER BY riparazioni_riuscite DESC;
*/


-- ============================================================================
-- QUERY 5: Calcolare e visualizzare quanti TICKET sono stati compilati 
--          da ciascun PDA e la durata media di lavorazione dei TICKET
-- ============================================================================

SELECT 
    p.codice_pda AS 'Codice PDA',
    p.nome_negozio AS 'Nome Negozio',
    p.telefono AS 'Telefono',
    p.email AS 'Email',
    COUNT(t.ticket_id) AS 'Totale Ticket',
    SUM(CASE WHEN t.stato = 'APERTO' THEN 1 ELSE 0 END) AS 'Ticket Aperti',
    SUM(CASE WHEN t.stato = 'IN_RIPARAZIONE' THEN 1 ELSE 0 END) AS 'Ticket In Riparazione',
    SUM(CASE WHEN t.stato = 'CHIUSO' THEN 1 ELSE 0 END) AS 'Ticket Chiusi',
    ROUND(AVG(DATEDIFF(COALESCE(t.data_chiusura, CURRENT_DATE), t.data_apertura)), 1) AS 'Durata Media (giorni)',
    ROUND(AVG(CASE 
        WHEN t.stato = 'CHIUSO' 
        THEN DATEDIFF(t.data_chiusura, t.data_apertura) 
        ELSE NULL 
    END), 1) AS 'Durata Media Chiusi (giorni)',
    MIN(t.data_apertura) AS 'Primo Ticket',
    MAX(t.data_apertura) AS 'Ultimo Ticket'
FROM PDA p
LEFT JOIN TICKETS t ON p.pda_id = t.pda_id
GROUP BY p.pda_id, p.codice_pda, p.nome_negozio, p.telefono, p.email
ORDER BY COUNT(t.ticket_id) DESC, p.codice_pda ASC;


-- ============================================================================
-- QUERY 6: Dato il codice identificativo di un intervento, visualizzarne lo stato
-- ============================================================================

-- Parametri: @ticket_id (es. 1)

SELECT 
    t.ticket_id AS 'Codice Ticket',
    t.stato AS 'Stato',
    t.data_apertura AS 'Data Apertura',
    t.data_chiusura AS 'Data Chiusura',
    DATEDIFF(COALESCE(t.data_chiusura, CURRENT_DATE), t.data_apertura) AS 'Giorni Lavorazione',
    t.tempo_stimato AS 'Tempo Stimato (giorni)',
    CASE 
        WHEN t.stato = 'CHIUSO' THEN 0
        WHEN t.tempo_stimato IS NULL THEN NULL
        ELSE GREATEST(0, t.tempo_stimato - DATEDIFF(CURRENT_DATE, t.data_apertura))
    END AS 'Giorni Rimanenti Stimati',
    p.codice_pda AS 'Codice PDA',
    p.nome_negozio AS 'Negozio',
    CONCAT(c.nome, ' ', c.cognome) AS 'Cliente',
    c.telefono AS 'Telefono Cliente',
    c.email AS 'Email Cliente',
    td.nome_tipo AS 'Tipo Dispositivo',
    m.nome_marca AS 'Marca',
    t.modello AS 'Modello',
    t.difetto_segnalato AS 'Difetto Segnalato',
    t.note_riparatore AS 'Note Riparatore',
    t.esito_riparazione AS 'Esito',
    t.data_ultima_modifica AS 'Ultima Modifica'
FROM TICKETS t
JOIN PDA p ON t.pda_id = p.pda_id
JOIN CLIENTI c ON t.cliente_id = c.cliente_id
JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
JOIN MARCHE m ON t.marca_id = m.marca_id
WHERE t.ticket_id = 1;  -- Parametro @ticket_id

-- Versione semplificata (solo stato)
/*
SELECT 
    ticket_id,
    stato,
    CASE 
        WHEN stato = 'APERTO' THEN 'Il riparatore ha preso in carico la richiesta'
        WHEN stato = 'IN_RIPARAZIONE' THEN 'L''articolo è in riparazione'
        WHEN stato = 'CHIUSO' THEN 'L''articolo è pronto per la riconsegna'
    END AS descrizione_stato
FROM TICKETS
WHERE ticket_id = ?;
*/


-- ============================================================================
-- QUERY AGGIUNTIVE UTILI
-- ============================================================================

-- Statistiche generali sistema
SELECT 
    COUNT(*) AS totale_ticket,
    SUM(CASE WHEN stato = 'APERTO' THEN 1 ELSE 0 END) AS aperti,
    SUM(CASE WHEN stato = 'IN_RIPARAZIONE' THEN 1 ELSE 0 END) AS in_riparazione,
    SUM(CASE WHEN stato = 'CHIUSO' THEN 1 ELSE 0 END) AS chiusi,
    ROUND(AVG(CASE WHEN stato = 'CHIUSO' 
        THEN DATEDIFF(data_chiusura, data_apertura) END), 1) AS durata_media_giorni
FROM TICKETS;

-- Ticket urgenti (oltre il tempo stimato)
SELECT 
    t.ticket_id,
    CONCAT(c.nome, ' ', c.cognome) AS cliente,
    m.nome_marca,
    t.modello,
    t.stato,
    t.data_apertura,
    DATEDIFF(CURRENT_DATE, t.data_apertura) AS giorni_trascorsi,
    t.tempo_stimato AS giorni_stimati
FROM TICKETS t
JOIN CLIENTI c ON t.cliente_id = c.cliente_id
JOIN MARCHE m ON t.marca_id = m.marca_id
WHERE t.stato != 'CHIUSO'
    AND t.tempo_stimato IS NOT NULL
    AND DATEDIFF(CURRENT_DATE, t.data_apertura) > t.tempo_stimato
ORDER BY DATEDIFF(CURRENT_DATE, t.data_apertura) - t.tempo_stimato DESC;

-- ============================================================================
-- END OF QUERIES
-- ============================================================================
