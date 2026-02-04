# Architettura del Sistema Informativo per la Gestione Ticket di Riparazione

## 1. Analisi della Realtà di Riferimento

### 1.1 Descrizione del Sistema
Il sistema informativo è progettato per gestire le richieste di riparazione di apparecchi telefonici e televisivi da parte di una società di riparazione. Il sistema collega i Punti Di Accettazione (PDA) - rivendite o riparatori - con il centro di riparazione attraverso una piattaforma web.

### 1.2 Attori del Sistema
- **PDA (Punti Di Accettazione)**: Negozi/riparatori che ricevono apparecchi dai clienti e aprono ticket di riparazione
- **Clienti**: Proprietari degli apparecchi da riparare
- **Riparatori**: Tecnici che gestiscono le riparazioni
- **Amministratori**: Gestiscono il sistema e gli accessi

### 1.3 Processi Principali
1. **Registrazione Ticket**: Il PDA apre un nuovo ticket inserendo tutti i dati del cliente e dell'apparecchio
2. **Gestione Riparazione**: I riparatori aggiornano lo stato del ticket durante il processo
3. **Monitoraggio**: PDA e clienti possono visualizzare lo stato dei ticket
4. **Chiusura Ticket**: Il ticket viene chiuso quando l'apparecchio è pronto per la riconsegna

## 2. Schema Funzionale dell'Architettura

### 2.1 Architettura a 3 Livelli (Three-Tier Architecture)

```
┌─────────────────────────────────────────────────────────────┐
│                    LIVELLO PRESENTAZIONE                     │
│                    (Presentation Layer)                      │
├─────────────────────────────────────────────────────────────┤
│  - Interfaccia Web (HTML/CSS/JavaScript)                    │
│  - Form di Login                                             │
│  - Form Creazione Ticket                                     │
│  - Dashboard Visualizzazione Ticket                          │
│  - Pagine di Monitoraggio Stato                             │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                     LIVELLO APPLICAZIONE                     │
│                    (Application Layer)                       │
├─────────────────────────────────────────────────────────────┤
│  - Server Web (Apache/Nginx)                                │
│  - Application Server (PHP/Python/Node.js)                  │
│  - Moduli Funzionali:                                       │
│    • Autenticazione e Autorizzazione                        │
│    • Gestione Ticket                                        │
│    • Gestione Utenti e PDA                                  │
│    • Business Logic                                         │
│    • API REST per comunicazione client-server               │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                       LIVELLO DATI                           │
│                      (Data Layer)                            │
├─────────────────────────────────────────────────────────────┤
│  - Database Management System (MySQL/PostgreSQL)            │
│  - Database "RiparazioniDB"                                 │
│  - Tabelle: Users, PDA, Tickets, DeviceTypes, etc.         │
│  - Stored Procedures e Trigger                              │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Componenti del Sistema

#### 2.2.1 Frontend (Web Interface)
- **Tecnologia**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap per UI responsive
- **Funzionalità**:
  - Login/Logout
  - Dashboard con elenco ticket
  - Form per creazione nuovo ticket
  - Visualizzazione dettagli ticket
  - Filtri e ricerche

#### 2.2.2 Backend (Application Server)
- **Tecnologia**: PHP (alternativa: Python/Flask, Node.js/Express)
- **Funzionalità**:
  - Gestione sessioni utente
  - Validazione input
  - CRUD operations sui ticket
  - API REST per comunicazione con frontend
  - Generazione report

#### 2.2.3 Database
- **DBMS**: MySQL/MariaDB
- **Funzionalità**:
  - Persistenza dati
  - Gestione transazioni ACID
  - Integrità referenziale
  - Backup e recovery

### 2.3 Flusso di Dati

```
1. APERTURA TICKET:
   PDA → Login → Form Ticket → Validazione → DB Insert → Conferma

2. AGGIORNAMENTO STATO:
   Riparatore → Selezione Ticket → Modifica Stato → DB Update → Notifica

3. CONSULTAZIONE:
   Utente → Dashboard → Query DB → Visualizzazione Risultati
```

### 2.4 Stati dei Ticket

- **APERTO**: Il riparatore ha preso in carico la richiesta
- **IN_RIPARAZIONE**: L'articolo è attualmente in riparazione
- **CHIUSO**: L'articolo è pronto per la riconsegna (riparato o non riparato)

### 2.5 Requisiti di Sicurezza

- **Autenticazione**: Sistema username/password con hash delle password
- **Autorizzazione**: Controllo degli accessi basato sui ruoli
- **Protezione SQL Injection**: Prepared statements
- **HTTPS**: Comunicazione criptata
- **Session Management**: Gestione sicura delle sessioni

### 2.6 Requisiti Non Funzionali

- **Scalabilità**: Architettura modulare per future espansioni
- **Affidabilità**: Backup automatici del database
- **Usabilità**: Interfaccia intuitiva e responsive
- **Performance**: Ottimizzazione query e indexing del database
- **Manutenibilità**: Codice ben documentato e strutturato
