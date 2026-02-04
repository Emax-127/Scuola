<?php
/**
 * Dettaglio Ticket
 * Sistema Gestione Ticket Riparazione
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth();

$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id || !is_numeric($ticket_id)) {
    header("Location: elenco_ticket.php");
    exit();
}

$pageTitle = 'Dettaglio Ticket #' . $ticket_id;

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

// Carica dati del ticket
try {
    $stmt = $conn->prepare("
        SELECT 
            t.*,
            CONCAT(c.nome, ' ', c.cognome) AS cliente_nome,
            c.telefono AS cliente_telefono,
            c.email AS cliente_email,
            m.nome_marca,
            td.nome_tipo,
            p.codice_pda,
            p.nome_negozio,
            p.telefono AS pda_telefono,
            p.email AS pda_email,
            DATEDIFF(COALESCE(t.data_chiusura, CURRENT_DATE), t.data_apertura) AS giorni_lavorazione,
            CASE 
                WHEN t.stato = 'CHIUSO' THEN 0
                WHEN t.tempo_stimato IS NULL THEN NULL
                ELSE GREATEST(0, t.tempo_stimato - DATEDIFF(CURRENT_DATE, t.data_apertura))
            END AS giorni_rimanenti
        FROM TICKETS t
        JOIN CLIENTI c ON t.cliente_id = c.cliente_id
        JOIN MARCHE m ON t.marca_id = m.marca_id
        JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
        JOIN PDA p ON t.pda_id = p.pda_id
        WHERE t.ticket_id = ?
    ");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        header("Location: elenco_ticket.php");
        exit();
    }
    
} catch(PDOException $e) {
    error_log("Errore caricamento ticket: " . $e->getMessage());
    header("Location: elenco_ticket.php");
    exit();
}

// Gestione aggiornamento stato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_stato') {
            $nuovo_stato = $_POST['stato'] ?? '';
            $note_riparatore = trim($_POST['note_riparatore'] ?? '');
            $esito = null;
            
            if ($nuovo_stato === 'CHIUSO') {
                $esito = $_POST['esito_riparazione'] ?? null;
                if (!$esito) {
                    throw new Exception('Specificare l\'esito della riparazione per chiudere il ticket.');
                }
            }
            
            $stmt = $conn->prepare("
                UPDATE TICKETS 
                SET stato = ?, 
                    note_riparatore = ?,
                    esito_riparazione = ?
                WHERE ticket_id = ?
            ");
            $stmt->execute([$nuovo_stato, $note_riparatore, $esito, $ticket_id]);
            
            $success = 'Stato del ticket aggiornato con successo!';
            
            // Ricarica i dati
            header("Location: dettaglio_ticket.php?id=$ticket_id&updated=1");
            exit();
        }
        
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

if (isset($_GET['updated'])) {
    $success = 'Stato del ticket aggiornato con successo!';
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-ticket-detailed"></i> Ticket #<?php echo $ticket['ticket_id']; ?></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="elenco_ticket.php">Elenco Ticket</a></li>
                    <li class="breadcrumb-item active">Ticket #<?php echo $ticket['ticket_id']; ?></li>
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
        <div class="col-lg-8">
            <!-- Stato e Info Principali -->
            <div class="card ticket-detail mb-4">
                <div class="ticket-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-0">
                                <?php
                                $badgeClass = '';
                                switch($ticket['stato']) {
                                    case 'APERTO': $badgeClass = 'badge-aperto'; break;
                                    case 'IN_RIPARAZIONE': $badgeClass = 'badge-in-riparazione'; break;
                                    case 'CHIUSO': $badgeClass = 'badge-chiuso'; break;
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?> fs-6">
                                    <?php echo str_replace('_', ' ', $ticket['stato']); ?>
                                </span>
                            </h4>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal">
                                <i class="bi bi-pencil"></i> Aggiorna Stato
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Data Apertura</h6>
                            <p class="mb-0"><strong><?php echo date('d/m/Y', strtotime($ticket['data_apertura'])); ?></strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Data Chiusura</h6>
                            <p class="mb-0">
                                <strong><?php echo $ticket['data_chiusura'] ? date('d/m/Y', strtotime($ticket['data_chiusura'])) : '-'; ?></strong>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Giorni di Lavorazione</h6>
                            <p class="mb-0"><strong><?php echo $ticket['giorni_lavorazione']; ?> giorni</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Tempo Stimato Rimanente</h6>
                            <p class="mb-0">
                                <?php if ($ticket['giorni_rimanenti'] !== null): ?>
                                    <strong><?php echo max(0, $ticket['giorni_rimanenti']); ?> giorni</strong>
                                    <?php if ($ticket['giorni_rimanenti'] < 0): ?>
                                        <span class="badge bg-danger">In Ritardo</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Non specificato</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dispositivo -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="bi bi-phone"></i> Dispositivo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted mb-1">Tipo</h6>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($ticket['nome_tipo']); ?></strong></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted mb-1">Marca</h6>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($ticket['nome_marca']); ?></strong></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted mb-1">Modello</h6>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($ticket['modello']); ?></strong></p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6 class="text-muted mb-1">Difetto Segnalato</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['difetto_segnalato'])); ?></p>
                    </div>
                    <?php if ($ticket['note_riparatore']): ?>
                    <div class="mt-3">
                        <h6 class="text-muted mb-1">Note del Riparatore</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['note_riparatore'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($ticket['esito_riparazione']): ?>
                    <div class="mt-3">
                        <h6 class="text-muted mb-1">Esito Riparazione</h6>
                        <p class="mb-0">
                            <span class="badge <?php echo $ticket['esito_riparazione'] === 'RIPARATO' ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo str_replace('_', ' ', $ticket['esito_riparazione']); ?>
                            </span>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Cliente -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="bi bi-person"></i> Cliente</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong><?php echo htmlspecialchars($ticket['cliente_nome']); ?></strong></p>
                    <?php if ($ticket['cliente_telefono']): ?>
                    <p class="mb-2">
                        <i class="bi bi-telephone"></i> 
                        <a href="tel:<?php echo htmlspecialchars($ticket['cliente_telefono']); ?>">
                            <?php echo htmlspecialchars($ticket['cliente_telefono']); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    <?php if ($ticket['cliente_email']): ?>
                    <p class="mb-0">
                        <i class="bi bi-envelope"></i> 
                        <a href="mailto:<?php echo htmlspecialchars($ticket['cliente_email']); ?>">
                            <?php echo htmlspecialchars($ticket['cliente_email']); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- PDA -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="bi bi-shop"></i> Punto Di Accettazione</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($ticket['codice_pda']); ?></span>
                    </p>
                    <p class="mb-2"><strong><?php echo htmlspecialchars($ticket['nome_negozio']); ?></strong></p>
                    <?php if ($ticket['pda_telefono']): ?>
                    <p class="mb-2">
                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($ticket['pda_telefono']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($ticket['pda_email']): ?>
                    <p class="mb-0">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($ticket['pda_email']); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aggiornamento Stato -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="dettaglio_ticket.php?id=<?php echo $ticket_id; ?>">
                <input type="hidden" name="action" value="update_stato">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiorna Stato Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="stato" class="form-label">Nuovo Stato</label>
                        <select class="form-select" id="stato" name="stato" required>
                            <option value="APERTO" <?php echo $ticket['stato'] === 'APERTO' ? 'selected' : ''; ?>>Aperto</option>
                            <option value="IN_RIPARAZIONE" <?php echo $ticket['stato'] === 'IN_RIPARAZIONE' ? 'selected' : ''; ?>>In Riparazione</option>
                            <option value="CHIUSO" <?php echo $ticket['stato'] === 'CHIUSO' ? 'selected' : ''; ?>>Chiuso</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="esitoContainer" style="display: none;">
                        <label for="esito_riparazione" class="form-label">Esito Riparazione</label>
                        <select class="form-select" id="esito_riparazione" name="esito_riparazione">
                            <option value="">Seleziona...</option>
                            <option value="RIPARATO">Riparato</option>
                            <option value="NON_RIPARATO">Non Riparato</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note_riparatore" class="form-label">Note del Riparatore</label>
                        <textarea class="form-control" id="note_riparatore" name="note_riparatore" rows="4"><?php echo htmlspecialchars($ticket['note_riparatore'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mostra/nascondi campo esito in base allo stato selezionato
document.getElementById('stato').addEventListener('change', function() {
    const esitoContainer = document.getElementById('esitoContainer');
    const esitoSelect = document.getElementById('esito_riparazione');
    
    if (this.value === 'CHIUSO') {
        esitoContainer.style.display = 'block';
        esitoSelect.required = true;
    } else {
        esitoContainer.style.display = 'none';
        esitoSelect.required = false;
    }
});

// Trigger al caricamento
if (document.getElementById('stato').value === 'CHIUSO') {
    document.getElementById('esitoContainer').style.display = 'block';
    document.getElementById('esito_riparazione').required = true;
}
</script>

<?php require_once 'includes/footer.php'; ?>
