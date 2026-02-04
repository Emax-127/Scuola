# Sistema Gestione Ticket di Riparazione

Sistema informativo per la gestione delle richieste di riparazione di apparecchi telefonici e televisivi, sviluppato come soluzione per l'esame di stato M070 - Informatica.

## 📋 Descrizione del Progetto

Il sistema permette la gestione completa del processo di riparazione:
- Apertura ticket da parte dei Punti Di Accettazione (PDA)
- Tracciamento dello stato delle riparazioni (APERTO, IN_RIPARAZIONE, CHIUSO)
- Gestione anagrafica clienti e dispositivi
- Monitoraggio tempi di lavorazione
- Sistema di autenticazione per accesso sicuro

## 🏗️ Architettura

Il sistema è basato su un'architettura a 3 livelli:

1. **Livello Presentazione**: Interfaccia web HTML/CSS/JavaScript con Bootstrap
2. **Livello Applicazione**: Server-side PHP con gestione business logic
3. **Livello Dati**: Database MySQL/MariaDB

### Documentazione Tecnica

- `ARCHITETTURA.md` - Schema funzionale dell'architettura proposta
- `SCHEMA_DATABASE.md` - Schema concettuale (E-R) e schema logico del database

## 🗄️ Database

### Schema del Database

Il database `RiparazioniDB` include le seguenti tabelle principali:

- **USERS**: Utenti del sistema con autenticazione
- **PDA**: Punti Di Accettazione (negozi/riparatori)
- **CLIENTI**: Anagrafica clienti
- **TICKETS**: Richieste di riparazione
- **TIPI_DISPOSITIVO**: Catalogo tipi di dispositivi
- **MARCHE**: Catalogo marche

### Query SQL Implementate

File: `database/queries.sql`

Tutte le 6 query richieste dall'esame sono state implementate:

1. ✅ Elenco riparazioni "sostituzione display" per telefoni cellulari in un mese
2. ✅ Clienti con articoli non riparati dopo 30 giorni
3. ✅ Durata media riparazioni per marca e modello
4. ✅ Interventi riusciti per marca nell'anno
5. ✅ Numero ticket e durata media per PDA
6. ✅ Visualizzazione stato per codice ticket

## 🌐 Applicazione Web

### Struttura Directory

```
web/
├── config/
│   └── database.php         # Configurazione database
├── includes/
│   ├── auth.php            # Sistema autenticazione
│   ├── header.php          # Header comune
│   └── footer.php          # Footer comune
├── css/
│   └── style.css           # Stili personalizzati
├── js/
│   └── main.js             # JavaScript comune
├── index.php               # Dashboard
├── login.php               # Pagina login
├── logout.php              # Logout
├── nuovo_ticket.php        # Form creazione ticket
├── elenco_ticket.php       # Lista ticket con filtri
└── dettaglio_ticket.php    # Dettaglio e aggiornamento ticket
```

### Funzionalità Implementate

#### Autenticazione
- Login con username e password
- Gestione sessioni con timeout
- Ruoli utente (PDA, RIPARATORE, ADMIN)

#### Gestione Ticket
- **Creazione**: Form completo per apertura nuovo ticket
- **Visualizzazione**: Dashboard con statistiche e lista ticket
- **Ricerca/Filtro**: Filtri per stato, PDA, e ricerca libera
- **Dettaglio**: Visualizzazione completa informazioni ticket
- **Aggiornamento**: Modifica stato e note del riparatore

#### Stati del Ticket
- **APERTO**: Il riparatore ha preso in carico la richiesta
- **IN_RIPARAZIONE**: L'articolo è in riparazione
- **CHIUSO**: L'articolo è pronto per la riconsegna

## 🚀 Installazione

### Prerequisiti
- PHP 7.4 o superiore
- MySQL 5.7 o MariaDB 10.3 o superiore
- Web server (Apache/Nginx)

### Passi per l'Installazione

1. **Clonare il repository**
   ```bash
   git clone https://github.com/Emax-127/Scuola.git
   cd Scuola
   ```

2. **Creare il database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Configurare la connessione al database**
   
   Modificare `web/config/database.php` con le credenziali corrette:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'RiparazioniDB');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Configurare il web server**
   
   Puntare la document root a `web/`

5. **Accedere all'applicazione**
   
   Aprire nel browser: `http://localhost/`

### Utenti di Test

Il database viene popolato con utenti di test:

- **PDA**: `pda_rossi` / `password`
- **Admin**: `admin` / `password`

**Nota**: Le password devono essere generate con `password_hash()` in PHP per l'ambiente di produzione.

## 📱 Interfaccia Utente

L'interfaccia è stata sviluppata con:
- **Bootstrap 5.1.3** per UI responsive
- **Bootstrap Icons** per iconografia
- Design moderno e intuitivo
- Supporto per dispositivi mobile

### Schermate Principali

1. **Login**: Autenticazione sicura
2. **Dashboard**: Panoramica con statistiche e ultimi ticket
3. **Nuovo Ticket**: Form completo per apertura richiesta
4. **Elenco Ticket**: Tabella con filtri e ricerca
5. **Dettaglio Ticket**: Vista completa e aggiornamento stato

## 🔒 Sicurezza

Misure di sicurezza implementate:
- Password hash con algoritmo bcrypt
- Prepared statements per prevenire SQL injection
- Validazione input lato server
- Gestione sessioni con timeout
- Controllo accessi basato su ruoli

## 📊 Query e Report

Il sistema supporta diverse query SQL per analisi e reporting:

- Riparazioni per tipologia e periodo
- Monitoraggio SLA (30 giorni)
- Statistiche per marca/modello
- Performance PDA
- Analisi tempi di lavorazione

## 🛠️ Tecnologie Utilizzate

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.1.3
- **Icons**: Bootstrap Icons

## 📝 Note per l'Esame

Questo progetto soddisfa tutti i requisiti dell'esame M070:

✅ Analisi della realtà di riferimento  
✅ Schema funzionale dell'architettura  
✅ Schema concettuale (E-R) del database  
✅ Schema logico del database  
✅ Definizione delle relazioni  
✅ 6 interrogazioni SQL richieste  
✅ Codifica in linguaggio web (PHP)  

## 📄 Licenza

Progetto didattico per esame di stato - Istituto Tecnico Industriale Informatica

## 👥 Autori

Sviluppato come soluzione per l'esame di stato M070 - INFORMATICA GENERALE, APPLICAZIONI TECNICO SCIENTIFICHE

---

© 2024 Sistema Gestione Ticket Riparazione