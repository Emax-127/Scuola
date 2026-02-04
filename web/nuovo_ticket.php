<?php
/**
 * Nuovo Ticket
 * Sistema Gestione Ticket Riparazione
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth();

$pageTitle = 'Nuovo Ticket';

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

// Carica i dati per i dropdown
try {
    $tipi_dispositivo = $conn->query("SELECT tipo_id, nome_tipo FROM TIPI_DISPOSITIVO ORDER BY nome_tipo")->fetchAll();
    $marche = $conn->query("SELECT marca_id, nome_marca FROM MARCHE ORDER BY nome_marca")->fetchAll();
    $pda_list = $conn->query("SELECT pda_id, codice_pda, nome_negozio FROM PDA ORDER BY codice_pda")->fetchAll();
} catch(PDOException $e) {
    error_log("Errore caricamento dati form: " . $e->getMessage());
    $tipi_dispositivo = [];
    $marche = [];
    $pda_list = [];
}

// Gestione form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validazione input
        $pda_id = $_POST['pda_id'] ?? null;
        $cliente_nome = trim($_POST['cliente_nome'] ?? '');
        $cliente_cognome = trim($_POST['cliente_cognome'] ?? '');
        $cliente_telefono = trim($_POST['cliente_telefono'] ?? '');
        $cliente_email = trim($_POST['cliente_email'] ?? '');
        $tipo_id = $_POST['tipo_id'] ?? null;
        $marca_id = $_POST['marca_id'] ?? null;
        $modello = trim($_POST['modello'] ?? '');
        $difetto = trim($_POST['difetto'] ?? '');
        $tempo_stimato = $_POST['tempo_stimato'] ?? null;
        
        // Validazioni
        if (empty($cliente_nome) || empty($cliente_cognome) || empty($tipo_id) || 
            empty($marca_id) || empty($modello) || empty($difetto)) {
            throw new Exception('Tutti i campi obbligatori devono essere compilati.');
        }
        
        if (empty($cliente_telefono) && empty($cliente_email)) {
            throw new Exception('Inserire almeno un contatto (telefono o email) per il cliente.');
        }
        
        // Se l'utente è un PDA, usa il suo pda_id
        if (hasRole('PDA')) {
            $pda_id = getCurrentPdaId();
        }
        
        if (empty($pda_id)) {
            throw new Exception('PDA non specificato.');
        }
        
        $conn->beginTransaction();
        
        // Inserisci o trova il cliente
        $stmt = $conn->prepare("
            SELECT cliente_id FROM CLIENTI 
            WHERE LOWER(nome) = LOWER(?) AND LOWER(cognome) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$cliente_nome, $cliente_cognome]);
        $cliente = $stmt->fetch();
        
        if ($cliente) {
            $cliente_id = $cliente['cliente_id'];
            // Aggiorna i contatti se forniti
            $stmt = $conn->prepare("
                UPDATE CLIENTI 
                SET telefono = COALESCE(NULLIF(?, ''), telefono),
                    email = COALESCE(NULLIF(?, ''), email)
                WHERE cliente_id = ?
            ");
            $stmt->execute([$cliente_telefono, $cliente_email, $cliente_id]);
        } else {
            // Inserisci nuovo cliente
            $stmt = $conn->prepare("
                INSERT INTO CLIENTI (nome, cognome, telefono, email)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$cliente_nome, $cliente_cognome, $cliente_telefono, $cliente_email]);
            $cliente_id = $conn->lastInsertId();
        }
        
        // Inserisci il ticket
        $stmt = $conn->prepare("
            INSERT INTO TICKETS (pda_id, cliente_id, tipo_id, marca_id, modello, 
                               difetto_segnalato, tempo_stimato)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $pda_id, 
            $cliente_id, 
            $tipo_id, 
            $marca_id, 
            $modello, 
            $difetto, 
            $tempo_stimato ?: null
        ]);
        
        $ticket_id = $conn->lastInsertId();
        
        $conn->commit();
        
        $success = "Ticket #$ticket_id creato con successo!";
        
        // Reindirizza al dettaglio del ticket dopo 2 secondi
        header("refresh:2;url=dettaglio_ticket.php?id=$ticket_id");
        
    } catch(Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-plus-circle"></i> Nuovo Ticket di Riparazione</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Nuovo Ticket</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="nuovo_ticket.php" id="ticketForm">
                        <!-- Sezione Cliente -->
                        <h5 class="mb-3"><i class="bi bi-person"></i> Dati Cliente</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cliente_nome" class="form-label required">Nome</label>
                                <input type="text" class="form-control" id="cliente_nome" name="cliente_nome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cliente_cognome" class="form-label required">Cognome</label>
                                <input type="text" class="form-control" id="cliente_cognome" name="cliente_cognome" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cliente_telefono" class="form-label">Telefono</label>
                                <input type="tel" class="form-control" id="cliente_telefono" name="cliente_telefono" placeholder="es. 333-1234567">
                            </div>
                            <div class="col-md-6">
                                <label for="cliente_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="cliente_email" name="cliente_email" placeholder="es. email@esempio.it">
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Sezione Dispositivo -->
                        <h5 class="mb-3"><i class="bi bi-phone"></i> Dati Dispositivo</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo_id" class="form-label required">Tipo Dispositivo</label>
                                <select class="form-select" id="tipo_id" name="tipo_id" required>
                                    <option value="">Seleziona...</option>
                                    <?php foreach ($tipi_dispositivo as $tipo): ?>
                                    <option value="<?php echo $tipo['tipo_id']; ?>">
                                        <?php echo htmlspecialchars($tipo['nome_tipo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="marca_id" class="form-label required">Marca</label>
                                <select class="form-select" id="marca_id" name="marca_id" required>
                                    <option value="">Seleziona...</option>
                                    <?php foreach ($marche as $marca): ?>
                                    <option value="<?php echo $marca['marca_id']; ?>">
                                        <?php echo htmlspecialchars($marca['nome_marca']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modello" class="form-label required">Modello</label>
                            <input type="text" class="form-control" id="modello" name="modello" 
                                   placeholder="es. Galaxy S21, iPhone 13, etc." required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="difetto" class="form-label required">Difetto Segnalato / Anomalia</label>
                            <textarea class="form-control" id="difetto" name="difetto" rows="4" 
                                      placeholder="Descrivere il difetto o l'anomalia riscontrata..." required></textarea>
                        </div>
                        
                        <hr>
                        
                        <!-- Sezione PDA e Tempi -->
                        <h5 class="mb-3"><i class="bi bi-gear"></i> Informazioni Aggiuntive</h5>
                        
                        <?php if (!hasRole('PDA')): ?>
                        <div class="mb-3">
                            <label for="pda_id" class="form-label required">Punto Di Accettazione (PDA)</label>
                            <select class="form-select" id="pda_id" name="pda_id" required>
                                <option value="">Seleziona...</option>
                                <?php foreach ($pda_list as $pda): ?>
                                <option value="<?php echo $pda['pda_id']; ?>">
                                    <?php echo htmlspecialchars($pda['codice_pda'] . ' - ' . $pda['nome_negozio']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="tempo_stimato" class="form-label">Tempo Stimato (giorni)</label>
                            <input type="number" class="form-control" id="tempo_stimato" name="tempo_stimato" 
                                   min="1" max="365" placeholder="es. 5">
                            <div class="form-text">Tempo stimato per completare la riparazione (opzionale)</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annulla
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crea Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
