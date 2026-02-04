<?php
// Esempio di utilizzo della connessione al database
// Questo file mostra come recuperare e visualizzare i dati dalla tabella

require_once 'config.php';

// Connessione al database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica connessione
if (!$conn) {
    die("Errore di connessione: " . mysqli_connect_error());
}

// Query per recuperare tutti i compiti
$sql = "SELECT * FROM compiti ORDER BY data_consegna ASC";
$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Studente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .completato {
            color: green;
            font-weight: bold;
        }
        .non-completato {
            color: orange;
            font-weight: bold;
        }
        .info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 12px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>📚 Agenda Studente</h1>
    
    <div class="success">
        ✅ Connessione al database riuscita!
    </div>

    <?php
    if (mysqli_num_rows($result) > 0) {
        echo '<table>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Materia</th>';
        echo '<th>Descrizione</th>';
        echo '<th>Data Consegna</th>';
        echo '<th>Stato</th>';
        echo '</tr>';
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars($row['materia']) . '</td>';
            echo '<td>' . htmlspecialchars($row['descrizione']) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($row['data_consegna'])) . '</td>';
            
            if ($row['completato']) {
                echo '<td class="completato">✓ Completato</td>';
            } else {
                echo '<td class="non-completato">⏳ Da fare</td>';
            }
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<div class="info">Nessun compito trovato nel database.</div>';
    }
    
    // Chiudi la connessione
    mysqli_close($conn);
    ?>
    
    <div class="info">
        <strong>Nota:</strong> Questa è una pagina di esempio che mostra come utilizzare la connessione al database 
        per recuperare e visualizzare i dati. Puoi espandere questo progetto aggiungendo funzionalità per 
        inserire, modificare ed eliminare compiti.
    </div>
</body>
</html>
