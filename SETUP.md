# Agenda Studente - Progetto PHP

Questo progetto contiene un sistema di connessione al database MySQL per gestire un'agenda studente.

## Requisiti

- XAMPP (o LAMP/WAMP/MAMP) con:
  - PHP 7.0 o superiore
  - MySQL 5.6 o superiore
  - Apache Web Server

## Installazione e Configurazione

### 1. Installare XAMPP

1. Scarica XAMPP da: https://www.apachefriends.org/
2. Installa XAMPP seguendo le istruzioni per il tuo sistema operativo
3. Avvia XAMPP Control Panel

### 2. Avviare i Servizi

Nel XAMPP Control Panel, avvia:
- **Apache** (web server)
- **MySQL** (database server)

### 3. Configurare il Database

#### Opzione A: Usando phpMyAdmin (Interfaccia Grafica)

1. Apri il browser e vai su: `http://localhost/phpmyadmin`
2. Clicca su "Importa" nel menu superiore
3. Clicca su "Scegli file" e seleziona il file `database.sql`
4. Clicca su "Esegui" in fondo alla pagina

#### Opzione B: Usando la Console MySQL

1. Apri il terminale/prompt dei comandi
2. Vai nella directory bin di MySQL di XAMPP:
   - Windows: `cd C:\xampp\mysql\bin`
   - Mac/Linux: `cd /Applications/XAMPP/bin` o `/opt/lampp/bin`
3. Esegui:
   ```bash
   mysql -u root -p < percorso/del/file/database.sql
   ```
   (Premi Invio quando chiede la password, se non hai impostato una password)

### 4. Copiare i File del Progetto

1. Copia tutti i file di questo progetto nella cartella `htdocs` di XAMPP:
   - Windows: `C:\xampp\htdocs\agenda_studente\`
   - Mac: `/Applications/XAMPP/htdocs/agenda_studente/`
   - Linux: `/opt/lampp/htdocs/agenda_studente/`

### 5. Configurare la Connessione

Il file `config.php` contiene le impostazioni di connessione predefinite:
- **Server**: localhost
- **Username**: root
- **Password**: (vuota)
- **Database**: agenda_studente

Se hai modificato questi parametri in XAMPP, aggiorna il file `config.php` di conseguenza.

### 6. Testare la Connessione

1. Apri il browser
2. Vai su: `http://localhost/agenda_studente/connessione.php`
3. Dovresti vedere:
   ```
   Connessione al server MySQL riuscita
   Connessione al database 'agenda_studente' riuscita
   ```

## Struttura dei File

```
.
├── README.md              # Questo file
├── SETUP.md              # Istruzioni dettagliate di setup
├── config.php            # Configurazione del database
├── connessione.php       # Script di test della connessione
├── database.sql          # Schema del database e dati di esempio
└── esempio_utilizzo.php  # Esempio di come usare la connessione
```

## Errori Comuni e Soluzioni

### "Connessione fallita: Can't connect to MySQL server"
- **Soluzione**: Assicurati che MySQL sia avviato in XAMPP Control Panel

### "Access denied for user 'root'@'localhost'"
- **Soluzione**: Verifica username e password nel file `config.php`

### "Unknown database 'agenda_studente'"
- **Soluzione**: Esegui lo script `database.sql` per creare il database

### "Call to undefined function mysqli_connect()"
- **Soluzione**: Assicurati che l'estensione mysqli sia abilitata in `php.ini`

## Codice Originale vs Codice Corretto

### Errori Corretti:

1. ✅ "local host" → "localhost" (rimosso spazio)
2. ✅ "$usarnme" → "$username" (corretto typo)
3. ✅ `mysql.connect()` → `mysqli_connect()` (sintassi corretta)
4. ✅ `mysql.connect.error()` → `mysqli_connect_error()` (sintassi corretta)
5. ✅ `mysql.select.db()` → `mysqli_select_db()` (sintassi corretta)
6. ✅ "agenda studente" → "agenda_studente" (rimosso spazio nel nome database)
7. ✅ Rimosso echo dopo die() (codice irraggiungibile)
8. ✅ Corretta concatenazione stringhe negli echo
9. ✅ Aggiunta gestione errori corretta
10. ✅ Separata configurazione dal codice di connessione

## Sicurezza

⚠️ **IMPORTANTE**: Questo codice è solo per scopi didattici/sviluppo locale.

Per un ambiente di produzione:
- Non salvare mai le credenziali del database direttamente nel codice
- Usa variabili d'ambiente
- Imposta una password per l'utente MySQL
- Usa prepared statements per prevenire SQL injection
- Abilita HTTPS

## Prossimi Passi

Dopo aver verificato che la connessione funziona, puoi:
1. Creare pagine PHP per visualizzare i dati
2. Aggiungere form per inserire nuovi compiti
3. Implementare funzionalità di modifica ed eliminazione
4. Migliorare l'interfaccia utente con CSS

## Supporto

Se riscontri problemi:
1. Controlla i log di errore di Apache in XAMPP
2. Verifica che tutti i servizi XAMPP siano avviati
3. Assicurati di aver eseguito correttamente lo script SQL

## Licenza

Progetto educativo - Libero uso per scopi didattici
