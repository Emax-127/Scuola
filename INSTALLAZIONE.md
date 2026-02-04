# Guida all'Installazione

## Prerequisiti

Prima di iniziare, assicurarsi di avere installati:
- PHP 7.4 o superiore
- MySQL 5.7 / MariaDB 10.3 o superiore
- Apache o Nginx
- Composer (opzionale, per dipendenze future)

## Installazione Passo-Passo

### 1. Clonare il Repository

```bash
git clone https://github.com/Emax-127/Scuola.git
cd Scuola
```

### 2. Configurare il Database

#### 2.1 Creare il Database

Accedere a MySQL:
```bash
mysql -u root -p
```

Eseguire lo script di creazione:
```sql
source database/schema.sql
```

Oppure da linea di comando:
```bash
mysql -u root -p < database/schema.sql
```

Questo creerà:
- Il database `RiparazioniDB`
- Tutte le tabelle necessarie
- I trigger per validazione
- Dati di esempio per il testing

#### 2.2 Verificare la Creazione

```sql
USE RiparazioniDB;
SHOW TABLES;
SELECT COUNT(*) FROM TICKETS;
```

Dovresti vedere 5 ticket di esempio.

### 3. Configurare l'Applicazione Web

#### 3.1 Configurare la Connessione al Database

Modificare il file `web/config/database.php`:

```php
define('DB_HOST', 'localhost');      // Host del database
define('DB_NAME', 'RiparazioniDB');  // Nome del database
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL
```

**Importante**: Per ambienti di produzione, creare un utente MySQL dedicato con privilegi limitati:

```sql
CREATE USER 'riparazioni_user'@'localhost' IDENTIFIED BY 'password_sicura';
GRANT SELECT, INSERT, UPDATE, DELETE ON RiparazioniDB.* TO 'riparazioni_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Configurare il Web Server

#### Opzione A: Apache

Creare un Virtual Host in `/etc/apache2/sites-available/riparazioni.conf`:

```apache
<VirtualHost *:80>
    ServerName riparazioni.local
    DocumentRoot /path/to/Scuola/web
    
    <Directory /path/to/Scuola/web>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/riparazioni_error.log
    CustomLog ${APACHE_LOG_DIR}/riparazioni_access.log combined
</VirtualHost>
```

Abilitare il sito:
```bash
sudo a2ensite riparazioni.conf
sudo systemctl reload apache2
```

Aggiungere al file `/etc/hosts`:
```
127.0.0.1   riparazioni.local
```

#### Opzione B: PHP Built-in Server (Solo per sviluppo)

```bash
cd web
php -S localhost:8000
```

Accedere a: `http://localhost:8000`

#### Opzione C: Nginx

Configurazione in `/etc/nginx/sites-available/riparazioni`:

```nginx
server {
    listen 80;
    server_name riparazioni.local;
    root /path/to/Scuola/web;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Configurare le Password degli Utenti

Le password di test nel database sono placeholder. Per generare password corrette:

```php
<?php
// Script: generate_password.php
$password = 'password';  // Password desiderata
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash: $hash\n";
?>
```

Eseguire:
```bash
php generate_password.php
```

Aggiornare il database:
```sql
UPDATE USERS SET password_hash = 'hash_generato' WHERE username = 'pda_rossi';
UPDATE USERS SET password_hash = 'hash_generato' WHERE username = 'admin';
```

### 6. Verificare l'Installazione

#### 6.1 Testare la Connessione al Database

Creare un file `test_connection.php` in `web/`:

```php
<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Connessione al database riuscita!\n";
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM TICKETS");
    $result = $stmt->fetch();
    echo "Numero di ticket: " . $result['total'] . "\n";
    
} catch(Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
}
?>
```

Eseguire:
```bash
php web/test_connection.php
```

#### 6.2 Accedere all'Applicazione

Aprire il browser e andare a:
- `http://localhost:8000` (se usando PHP built-in server)
- `http://riparazioni.local` (se usando Apache/Nginx)

### 7. Credenziali di Accesso

Utenti di test predefiniti:

| Username | Password | Ruolo |
|----------|----------|-------|
| pda_rossi | password | PDA |
| pda_bianchi | password | PDA |
| riparatore1 | password | RIPARATORE |
| admin | password | ADMIN |

**Nota**: Cambiare queste password in produzione!

## Risoluzione Problemi Comuni

### Errore di Connessione al Database

**Problema**: "Errore di connessione al database"

**Soluzione**:
1. Verificare che MySQL sia in esecuzione: `sudo systemctl status mysql`
2. Verificare le credenziali in `config/database.php`
3. Verificare che il database esista: `SHOW DATABASES;`

### Errore "Headers already sent"

**Problema**: Errori relativi agli header

**Soluzione**:
1. Assicurarsi che non ci siano spazi o output prima di `<?php`
2. Verificare che il file non abbia BOM (Byte Order Mark)
3. Controllare che tutte le funzioni di redirect siano seguite da `exit()`

### Pagina Bianca / Errore 500

**Problema**: La pagina non viene visualizzata

**Soluzione**:
1. Abilitare la visualizzazione errori in PHP:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Controllare i log:
   - Apache: `/var/log/apache2/error.log`
   - Nginx: `/var/log/nginx/error.log`
   - PHP-FPM: `/var/log/php7.4-fpm.log`

### Errori di Sessione

**Problema**: "Session already started"

**Soluzione**:
1. Assicurarsi che `session_start()` sia chiamato solo una volta
2. Verificare i permessi sulla directory delle sessioni: `/var/lib/php/sessions`

## Test delle Query SQL

Per testare le query SQL:

```bash
mysql -u root -p RiparazioniDB < database/queries.sql
```

Oppure accedere a MySQL e testare singolarmente:

```sql
USE RiparazioniDB;

-- Query 1: Riparazioni display per mese
SELECT t.ticket_id, t.data_apertura, CONCAT(c.nome, ' ', c.cognome) AS cliente
FROM TICKETS t
JOIN CLIENTI c ON t.cliente_id = c.cliente_id
JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
WHERE td.nome_tipo = 'Telefono Cellulare'
  AND t.difetto_segnalato LIKE '%sostituzione display%'
  AND YEAR(t.data_apertura) = 2024
  AND MONTH(t.data_apertura) = 12;
```

## Manutenzione

### Backup del Database

```bash
mysqldump -u root -p RiparazioniDB > backup_$(date +%Y%m%d).sql
```

### Ripristino

```bash
mysql -u root -p RiparazioniDB < backup_20241201.sql
```

### Pulizia Dati di Test

```sql
DELETE FROM TICKETS WHERE ticket_id > 0;
DELETE FROM CLIENTI WHERE cliente_id > 0;
DELETE FROM PDA WHERE pda_id > 0;
DELETE FROM USERS WHERE user_id > 0;
```

## Supporto

Per problemi o domande:
1. Verificare la documentazione in `ARCHITETTURA.md` e `SCHEMA_DATABASE.md`
2. Controllare i log degli errori
3. Verificare che tutti i prerequisiti siano installati correttamente

## Sicurezza in Produzione

Prima di mettere in produzione:

1. ✅ Cambiare tutte le password di default
2. ✅ Usare HTTPS
3. ✅ Configurare un firewall
4. ✅ Limitare i privilegi del database
5. ✅ Disabilitare `display_errors` in PHP
6. ✅ Configurare backup automatici
7. ✅ Implementare rate limiting per il login
8. ✅ Aggiungere logging delle attività
9. ✅ Aggiornare regolarmente PHP e MySQL
10. ✅ Implementare CSP (Content Security Policy)
