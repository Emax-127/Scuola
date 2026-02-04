# Confronto: Codice Originale vs Codice Corretto

## ❌ Codice Originale (con errori)

```php
<?php
// Dichiarazione
$servername = "local host";              // ❌ ERRORE: spazio in "local host"
$usarnme = "root";                       // ❌ ERRORE: typo "usarnme"
$password = " ";                         // ❌ ERRORE: spazio nella stringa vuota
$dbname = "agenda studente";             // ⚠️  ATTENZIONE: spazio nel nome database

//connesione al server
$conn= mysql.connect ($servername, $usarnme, $password);  // ❌ ERRORE: mysql.connect invece di mysqli_connect

//veriica connesione
if (!$conn) {
    die ("connesione fallita;" .mysql.connect.error());  // ❌ ERRORE: mysql.connect.error invece di mysqli_connect_error
    echo "connesione riuscita";          // ❌ ERRORE: questo echo non verrà mai eseguito (dopo die)
}

//connesione database e verifica
$conndb = mysql.select.db($conn, $dbname) or die ("connesione al db fallita" .$dbname);  // ❌ ERRORE: mysql.select.db invece di mysqli_select_db
echo ("connesione al" .dbname."riuscita" );  // ❌ ERRORE: concatenazione stringhe errata
?>
```

## ✅ Codice Corretto

### File: config.php
```php
<?php
// Configurazione del database
// Modifica questi valori in base alla tua configurazione di XAMPP/MySQL
define('DB_SERVER', 'localhost');        // ✅ CORRETTO: "localhost" senza spazi
define('DB_USERNAME', 'root');           // ✅ CORRETTO: nome variabile corretto
define('DB_PASSWORD', '');               // ✅ CORRETTO: stringa vuota corretta
define('DB_NAME', 'agenda_studente');    // ✅ CORRETTO: underscore invece di spazio
?>
```

### File: connessione.php
```php
<?php
// Includi il file di configurazione
require_once 'config.php';

// Dichiarazione variabili
$servername = DB_SERVER;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$dbname = DB_NAME;

// Connessione al server MySQL usando mysqli
$conn = mysqli_connect($servername, $username, $password);  // ✅ CORRETTO: mysqli_connect() con sintassi corretta

// Verifica connessione
if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());  // ✅ CORRETTO: mysqli_connect_error() e messaggio chiaro
}
echo "Connessione al server MySQL riuscita<br>";  // ✅ CORRETTO: posizionato correttamente

// Connessione al database e verifica
$conndb = mysqli_select_db($conn, $dbname);  // ✅ CORRETTO: mysqli_select_db() con sintassi corretta
if (!$conndb) {
    die("Connessione al database fallita: " . mysqli_error($conn));  // ✅ MIGLIORATO: gestione errori separata
}
echo "Connessione al database '" . $dbname . "' riuscita<br>";  // ✅ CORRETTO: concatenazione corretta

// Chiusura della connessione (opzionale, da fare alla fine dello script)
// mysqli_close($conn);
?>
```

## 📋 Elenco Completo delle Correzioni

| # | Errore Originale | Correzione | Tipo di Errore |
|---|------------------|------------|----------------|
| 1 | `"local host"` | `"localhost"` | Sintassi - spazio errato |
| 2 | `$usarnme` | `$username` | Typo nella variabile |
| 3 | `$password = " "` | `$password = ""` | Stringa vuota con spazio |
| 4 | `"agenda studente"` | `"agenda_studente"` | Nome database con spazio |
| 5 | `mysql.connect()` | `mysqli_connect()` | Sintassi funzione errata (punto invece di underscore) |
| 6 | `mysql.connect.error()` | `mysqli_connect_error()` | Sintassi funzione errata |
| 7 | `echo` dopo `die()` | Rimosso | Codice irraggiungibile |
| 8 | `mysql.select.db()` | `mysqli_select_db()` | Sintassi funzione errata |
| 9 | `.dbname.` | `'" . $dbname . "'` | Concatenazione stringhe errata |
| 10 | Mancanza gestione errori | Aggiunta verifica con if | Logica migliorata |

## 🔧 Miglioramenti Aggiunti

Oltre alla correzione degli errori, sono stati aggiunti:

1. **Separazione della configurazione**: File `config.php` separato
2. **Gestione errori migliorata**: Controlli separati per connessione server e database
3. **Messaggi chiari**: Output informativi per debug
4. **Codice commentato**: Commenti esplicativi in italiano
5. **Standard di codifica**: Utilizzo di `mysqli_` invece della vecchia estensione `mysql_`
6. **Sicurezza base**: Uso di costanti per le credenziali

## 📚 Perché mysqli invece di mysql?

L'estensione `mysql_*` è stata:
- **Deprecata** in PHP 5.5.0
- **Rimossa** completamente in PHP 7.0.0

L'estensione **mysqli** (MySQL Improved) offre:
- ✅ Supporto per prepared statements (prevenzione SQL injection)
- ✅ Supporto per transazioni
- ✅ API procedurale e orientata agli oggetti
- ✅ Prestazioni migliori
- ✅ Funzionalità avanzate

## 🎓 Risorse per Approfondire

- [PHP mysqli Documentation](https://www.php.net/manual/en/book.mysqli.php)
- [PHP PDO (alternativa moderna)](https://www.php.net/manual/en/book.pdo.php)
- [W3Schools MySQL Tutorial](https://www.w3schools.com/php/php_mysql_intro.asp)
- [XAMPP Documentation](https://www.apachefriends.org/index.html)

## ⚠️ Note di Sicurezza

Questo codice è adatto per:
- ✅ Ambiente di sviluppo locale
- ✅ Apprendimento e test
- ✅ Progetti scolastici

Per produzione, considera:
- 🔒 PDO con prepared statements
- 🔒 Password per utente database
- 🔒 Validazione e sanitizzazione input
- 🔒 HTTPS
- 🔒 Gestione errori senza esporre dettagli
