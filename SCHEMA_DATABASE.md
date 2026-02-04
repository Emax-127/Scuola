# Schema del Database - Sistema Gestione Ticket Riparazione

## 1. Schema Concettuale (Modello E-R)

### 1.1 Entità Principali

#### USERS (Utenti)
Rappresenta tutti gli utenti del sistema (PDA, riparatori, amministratori)
- **Attributi**: user_id, username, password_hash, email, ruolo, data_registrazione

#### PDA (Punti Di Accettazione)
Rappresenta i negozi/riparatori che aprono ticket
- **Attributi**: pda_id, codice_pda, nome_negozio, indirizzo, telefono, email, user_id

#### CLIENTI
Rappresenta i clienti finali che portano apparecchi da riparare
- **Attributi**: cliente_id, nome, cognome, telefono, email

#### TICKETS (Richieste di Riparazione)
Rappresenta le richieste di riparazione
- **Attributi**: ticket_id, pda_id, cliente_id, data_apertura, data_chiusura, stato, tempo_stimato, tipo_dispositivo, marca, modello, difetto_segnalato, note_riparatore, esito_riparazione

#### TIPI_DISPOSITIVO
Catalogo dei tipi di dispositivi riparabili
- **Attributi**: tipo_id, nome_tipo (telefono cellulare, televisore, etc.)

#### MARCHE
Catalogo delle marche
- **Attributi**: marca_id, nome_marca

### 1.2 Relazioni

- **PDA** → APPARTIENE → **USERS** (1:1)
  - Un PDA è associato a un utente del sistema

- **TICKETS** → APERTO_DA → **PDA** (N:1)
  - Un ticket è aperto da un solo PDA
  - Un PDA può aprire molti ticket

- **TICKETS** → RIFERITO_A → **CLIENTI** (N:1)
  - Un ticket è riferito a un solo cliente
  - Un cliente può avere molti ticket

- **TICKETS** → HA_TIPO → **TIPI_DISPOSITIVO** (N:1)
  - Un ticket riguarda un tipo di dispositivo
  - Un tipo di dispositivo può essere presente in molti ticket

- **TICKETS** → HA_MARCA → **MARCHE** (N:1)
  - Un ticket riguarda una marca
  - Una marca può essere presente in molti ticket

### 1.3 Diagramma E-R (Rappresentazione Testuale)

```
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│    USERS    │1      1 │     PDA     │1      N │   TICKETS   │
│─────────────│◄────────│─────────────│◄────────│─────────────│
│ user_id (PK)│         │ pda_id (PK) │         │ticket_id(PK)│
│ username    │         │ codice_pda  │         │ pda_id (FK) │
│ password    │         │ nome_negozio│         │cliente_id FK│
│ email       │         │ user_id (FK)│         │ tipo_id (FK)│
│ ruolo       │         │ indirizzo   │         │ marca_id(FK)│
└─────────────┘         │ telefono    │         │ data_apertura│
                        └─────────────┘         │ data_chiusura│
                                                │ stato       │
                                                │ tempo_stimato│
┌─────────────┐                                 │ modello     │
│   CLIENTI   │1                              N │ difetto     │
│─────────────│◄────────────────────────────────│ note        │
│cliente_id PK│                                 │ esito       │
│ nome        │                                 └─────────────┘
│ cognome     │                                        │
│ telefono    │                                        │N
│ email       │                                        │
└─────────────┘                                        │
                                                       │
┌──────────────────┐                    ┌─────────────┴────┐
│ TIPI_DISPOSITIVO │1                 N │     MARCHE       │
│──────────────────│◄───────────────────│──────────────────│
│ tipo_id (PK)     │                    │ marca_id (PK)    │
│ nome_tipo        │                    │ nome_marca       │
└──────────────────┘                    └──────────────────┘
```

## 2. Schema Logico

### 2.1 Modello Relazionale

#### USERS
```
USERS(user_id, username, password_hash, email, ruolo, data_registrazione)
  PK: user_id
  UNIQUE: username, email
  CHECK: ruolo IN ('PDA', 'RIPARATORE', 'ADMIN')
```

#### PDA
```
PDA(pda_id, codice_pda, nome_negozio, indirizzo, telefono, email, user_id)
  PK: pda_id
  FK: user_id → USERS(user_id)
  UNIQUE: codice_pda, user_id
```

#### CLIENTI
```
CLIENTI(cliente_id, nome, cognome, telefono, email)
  PK: cliente_id
```

#### TIPI_DISPOSITIVO
```
TIPI_DISPOSITIVO(tipo_id, nome_tipo)
  PK: tipo_id
  UNIQUE: nome_tipo
```

#### MARCHE
```
MARCHE(marca_id, nome_marca)
  PK: marca_id
  UNIQUE: nome_marca
```

#### TICKETS
```
TICKETS(ticket_id, pda_id, cliente_id, tipo_id, marca_id, 
        modello, data_apertura, data_chiusura, stato, 
        tempo_stimato, difetto_segnalato, note_riparatore, 
        esito_riparazione)
  PK: ticket_id
  FK: pda_id → PDA(pda_id)
  FK: cliente_id → CLIENTI(cliente_id)
  FK: tipo_id → TIPI_DISPOSITIVO(tipo_id)
  FK: marca_id → MARCHE(marca_id)
  CHECK: stato IN ('APERTO', 'IN_RIPARAZIONE', 'CHIUSO')
  CHECK: esito_riparazione IN ('RIPARATO', 'NON_RIPARATO', NULL)
```

### 2.2 Vincoli di Integrità

#### Vincoli di Dominio
- `ticket_id`: AUTO_INCREMENT, NOT NULL
- `stato`: ENUM('APERTO', 'IN_RIPARAZIONE', 'CHIUSO')
- `data_apertura`: DATE, NOT NULL, DEFAULT CURRENT_DATE
- `data_chiusura`: DATE, NULL (solo quando stato='CHIUSO')
- `tempo_stimato`: INTEGER (giorni), NULL
- `esito_riparazione`: ENUM('RIPARATO', 'NON_RIPARATO'), NULL

#### Vincoli di Integrità Referenziale
- ON DELETE RESTRICT per PDA, CLIENTI (non si possono eliminare se hanno ticket associati)
- ON UPDATE CASCADE per tutte le FK

#### Vincoli di Business Logic
- `data_chiusura >= data_apertura`
- Se `stato = 'CHIUSO'` allora `data_chiusura IS NOT NULL`
- Se `stato = 'CHIUSO'` allora `esito_riparazione IS NOT NULL`
- Se `stato != 'CHIUSO'` allora `data_chiusura IS NULL`

### 2.3 Indici

Per ottimizzare le query, vengono creati i seguenti indici:

```sql
-- Indici per ricerche frequenti
INDEX idx_tickets_stato ON TICKETS(stato);
INDEX idx_tickets_data_apertura ON TICKETS(data_apertura);
INDEX idx_tickets_pda ON TICKETS(pda_id);
INDEX idx_tickets_cliente ON TICKETS(cliente_id);
INDEX idx_tickets_marca_modello ON TICKETS(marca_id, modello);
INDEX idx_tickets_tipo_difetto ON TICKETS(tipo_id, difetto_segnalato);
```

## 3. Normalizzazione

Il database è in **Terza Forma Normale (3NF)**:

- **1NF**: Tutti gli attributi sono atomici (non ci sono attributi multivalore)
- **2NF**: Non ci sono dipendenze parziali dalla chiave primaria
- **3NF**: Non ci sono dipendenze transitive

Esempio di normalizzazione applicata:
- La tabella MARCHE è stata separata per evitare ridondanza (invece di avere nome_marca ripetuto in TICKETS)
- La tabella TIPI_DISPOSITIVO è stata separata per lo stesso motivo
- La tabella PDA è separata da USERS per gestire le informazioni specifiche del punto di accettazione
