# Scuola - Progetto Agenda Studente

Sistema di gestione database MySQL per un'agenda studente, sviluppato con PHP e MySQL.

## 🚀 Avvio Rapido

Per istruzioni complete di installazione e configurazione, consulta il file [SETUP.md](SETUP.md).

### Requisiti Minimi
- XAMPP (o LAMP/WAMP) 
- PHP 7.0+
- MySQL 5.6+

### Setup Base
1. Installa XAMPP
2. Avvia Apache e MySQL
3. Importa `database.sql` in phpMyAdmin
4. Copia i file in `htdocs/agenda_studente/`
5. Apri `http://localhost/agenda_studente/connessione.php` per testare

## 📁 File Principali

- `config.php` - Configurazione database
- `connessione.php` - Script di test connessione
- `database.sql` - Schema del database
- `esempio_utilizzo.php` - Esempio pratico di utilizzo
- `SETUP.md` - Guida completa all'installazione

## 📝 Note

Il codice originale conteneva diversi errori sintattici che sono stati corretti:
- Sintassi mysqli corretta
- Gestione errori migliorata  
- Separazione configurazione/logica
- Documentazione completa

Per dettagli completi, vedi [SETUP.md](SETUP.md).