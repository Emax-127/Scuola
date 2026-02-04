#!/usr/bin/env php
<?php
/**
 * Test Script for SQL Queries
 * Sistema Gestione Ticket Riparazione
 */

// Change to web directory to load includes
chdir(__DIR__ . '/../web');

require_once 'config/database.php';

echo "==========================================================\n";
echo "TEST QUERY SQL - Sistema Gestione Ticket Riparazione\n";
echo "==========================================================\n\n";

$db = new Database();
$conn = $db->getConnection();

// Test 1: Query 1 - Riparazioni display per mese
echo "TEST 1: Riparazioni 'sostituzione display' per telefoni cellulari\n";
echo "-------------------------------------------------------------------\n";
try {
    $stmt = $conn->query("
        SELECT 
            t.ticket_id,
            t.data_apertura,
            CONCAT(c.nome, ' ', c.cognome) AS cliente,
            m.nome_marca,
            t.modello
        FROM TICKETS t
        JOIN CLIENTI c ON t.cliente_id = c.cliente_id
        JOIN MARCHE m ON t.marca_id = m.marca_id
        JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
        WHERE td.nome_tipo = 'Telefono Cellulare'
            AND t.difetto_segnalato LIKE '%sostituzione display%'
            AND YEAR(t.data_apertura) = 2024
            AND MONTH(t.data_apertura) = 12
        ORDER BY t.data_apertura ASC
    ");
    $results = $stmt->fetchAll();
    
    if (count($results) > 0) {
        echo "✓ Query eseguita con successo! Trovati " . count($results) . " risultati\n";
        foreach ($results as $row) {
            echo "  - Ticket #{$row['ticket_id']}: {$row['cliente']} - {$row['nome_marca']} {$row['modello']} ({$row['data_apertura']})\n";
        }
    } else {
        echo "⚠ Nessun risultato trovato (normale se non ci sono dati per dicembre 2024)\n";
    }
} catch(Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Query 2 - Clienti con articoli non riparati dopo 30 giorni
echo "TEST 2: Clienti con articoli non riparati dopo 30 giorni\n";
echo "-------------------------------------------------------------------\n";
try {
    $stmt = $conn->query("
        SELECT DISTINCT
            c.nome,
            c.cognome,
            c.telefono,
            t.ticket_id,
            t.data_apertura,
            DATEDIFF(CURRENT_DATE, t.data_apertura) AS giorni_trascorsi
        FROM CLIENTI c
        JOIN TICKETS t ON c.cliente_id = t.cliente_id
        WHERE t.stato != 'CHIUSO'
            AND DATEDIFF(CURRENT_DATE, t.data_apertura) > 30
        ORDER BY giorni_trascorsi DESC
    ");
    $results = $stmt->fetchAll();
    
    if (count($results) > 0) {
        echo "✓ Query eseguita con successo! Trovati " . count($results) . " risultati\n";
        foreach ($results as $row) {
            echo "  - {$row['nome']} {$row['cognome']} (Tel: {$row['telefono']}) - Ticket #{$row['ticket_id']} - {$row['giorni_trascorsi']} giorni\n";
        }
    } else {
        echo "✓ Nessun ticket in ritardo (tutti i ticket sono stati riparati in tempo)\n";
    }
} catch(Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Query 3 - Durata media per marca/modello
echo "TEST 3: Durata media riparazioni per marca e modello\n";
echo "-------------------------------------------------------------------\n";
try {
    $stmt = $conn->query("
        SELECT 
            m.nome_marca,
            t.modello,
            COUNT(t.ticket_id) AS num_riparazioni,
            ROUND(AVG(DATEDIFF(t.data_chiusura, t.data_apertura)), 1) AS durata_media_giorni
        FROM TICKETS t
        JOIN MARCHE m ON t.marca_id = m.marca_id
        WHERE m.nome_marca = 'Samsung'
            AND t.modello = 'Galaxy S21'
            AND t.stato = 'CHIUSO'
        GROUP BY m.nome_marca, t.modello
    ");
    $results = $stmt->fetchAll();
    
    if (count($results) > 0) {
        echo "✓ Query eseguita con successo!\n";
        foreach ($results as $row) {
            echo "  - {$row['nome_marca']} {$row['modello']}: {$row['num_riparazioni']} riparazioni, media {$row['durata_media_giorni']} giorni\n";
        }
    } else {
        echo "⚠ Nessun dato per Samsung Galaxy S21\n";
    }
} catch(Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Query 4 - Riparazioni riuscite per marca nell'anno
echo "TEST 4: Riparazioni riuscite per marca (anno 2024)\n";
echo "-------------------------------------------------------------------\n";
try {
    $stmt = $conn->query("
        SELECT 
            m.nome_marca,
            COUNT(t.ticket_id) AS riparazioni_riuscite
        FROM TICKETS t
        JOIN MARCHE m ON t.marca_id = m.marca_id
        WHERE YEAR(t.data_apertura) = 2024
            AND t.stato = 'CHIUSO'
            AND t.esito_riparazione = 'RIPARATO'
        GROUP BY m.nome_marca
        ORDER BY riparazioni_riuscite DESC
    ");
    $results = $stmt->fetchAll();
    
    if (count($results) > 0) {
        echo "✓ Query eseguita con successo! Trovate " . count($results) . " marche\n";
        foreach ($results as $row) {
            echo "  - {$row['nome_marca']}: {$row['riparazioni_riuscite']} riparazioni riuscite\n";
        }
    } else {
        echo "⚠ Nessuna riparazione riuscita nel 2024\n";
    }
} catch(Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Query 5 - Ticket per PDA e durata media
echo "TEST 5: Ticket compilati per PDA e durata media lavorazione\n";
echo "-------------------------------------------------------------------\n";
try {
    $stmt = $conn->query("
        SELECT 
            p.codice_pda,
            p.nome_negozio,
            COUNT(t.ticket_id) AS totale_ticket,
            ROUND(AVG(DATEDIFF(COALESCE(t.data_chiusura, CURRENT_DATE), t.data_apertura)), 1) AS durata_media_giorni
        FROM PDA p
        LEFT JOIN TICKETS t ON p.pda_id = t.pda_id
        GROUP BY p.pda_id, p.codice_pda, p.nome_negozio
        ORDER BY totale_ticket DESC
    ");
    $results = $stmt->fetchAll();
    
    if (count($results) > 0) {
        echo "✓ Query eseguita con successo! Trovati " . count($results) . " PDA\n";
        foreach ($results as $row) {
            echo "  - {$row['codice_pda']} ({$row['nome_negozio']}): {$row['totale_ticket']} ticket, media {$row['durata_media_giorni']} giorni\n";
        }
    } else {
        echo "⚠ Nessun PDA trovato\n";
    }
} catch(Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Query 6 - Stato di un ticket specifico
echo "TEST 6: Visualizzazione stato ticket specifico (ID=1)\n";
echo "-------------------------------------------------------------------\n";
try {
    $stmt = $conn->prepare("
        SELECT 
            t.ticket_id,
            t.stato,
            t.data_apertura,
            t.data_chiusura,
            CONCAT(c.nome, ' ', c.cognome) AS cliente,
            m.nome_marca,
            t.modello
        FROM TICKETS t
        JOIN CLIENTI c ON t.cliente_id = c.cliente_id
        JOIN MARCHE m ON t.marca_id = m.marca_id
        WHERE t.ticket_id = ?
    ");
    $stmt->execute([1]);
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✓ Query eseguita con successo!\n";
        echo "  - Ticket #{$result['ticket_id']}\n";
        echo "  - Stato: {$result['stato']}\n";
        echo "  - Cliente: {$result['cliente']}\n";
        echo "  - Dispositivo: {$result['nome_marca']} {$result['modello']}\n";
        echo "  - Data apertura: {$result['data_apertura']}\n";
        echo "  - Data chiusura: " . ($result['data_chiusura'] ?: 'In corso') . "\n";
    } else {
        echo "⚠ Ticket #1 non trovato\n";
    }
} catch(Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n";

// Test statistiche generali
echo "STATISTICHE GENERALI\n";
echo "-------------------------------------------------------------------\n";
try {
    $stmt = $conn->query("
        SELECT 
            COUNT(*) AS totale_ticket,
            SUM(CASE WHEN stato = 'APERTO' THEN 1 ELSE 0 END) AS aperti,
            SUM(CASE WHEN stato = 'IN_RIPARAZIONE' THEN 1 ELSE 0 END) AS in_riparazione,
            SUM(CASE WHEN stato = 'CHIUSO' THEN 1 ELSE 0 END) AS chiusi
        FROM TICKETS
    ");
    $stats = $stmt->fetch();
    
    echo "✓ Database popolato correttamente!\n";
    echo "  - Totale ticket: {$stats['totale_ticket']}\n";
    echo "  - Aperti: {$stats['aperti']}\n";
    echo "  - In riparazione: {$stats['in_riparazione']}\n";
    echo "  - Chiusi: {$stats['chiusi']}\n";
} catch(Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n";

echo "==========================================================\n";
echo "TEST COMPLETATI\n";
echo "==========================================================\n";
?>
