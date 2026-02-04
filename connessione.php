<?php
// Includi il file di configurazione
require_once 'config.php';

// Dichiarazione variabili
$servername = DB_SERVER;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$dbname = DB_NAME;

// Connessione al server MySQL usando mysqli
$conn = mysqli_connect($servername, $username, $password);

// Verifica connessione
if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}
echo "Connessione al server MySQL riuscita<br>";

// Connessione al database e verifica
$conndb = mysqli_select_db($conn, $dbname);
if (!$conndb) {
    die("Connessione al database fallita: " . mysqli_error($conn));
}
echo "Connessione al database '" . $dbname . "' riuscita<br>";

// Chiusura della connessione (opzionale, da fare alla fine dello script)
// mysqli_close($conn);
?>
