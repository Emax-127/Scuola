<?php
/**
 * Elenco Ticket
 * Sistema Gestione Ticket Riparazione
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth();

$pageTitle = 'Elenco Ticket';

$db = new Database();
$conn = $db->getConnection();

// Filtri
$filtro_stato = $_GET['stato'] ?? '';
$filtro_pda = $_GET['pda'] ?? '';
$filtro_ricerca = $_GET['ricerca'] ?? '';

// Query base
$where_clauses = [];
$params = [];

if ($filtro_stato) {
    $where_clauses[] = "t.stato = ?";
    $params[] = $filtro_stato;
}

if ($filtro_pda) {
    $where_clauses[] = "p.codice_pda = ?";
    $params[] = $filtro_pda;
}

if ($filtro_ricerca) {
    $where_clauses[] = "(CONCAT(c.nome, ' ', c.cognome) LIKE ? OR t.modello LIKE ? OR m.nome_marca LIKE ?)";
    $search_term = "%$filtro_ricerca%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

try {
    $stmt = $conn->prepare("
        SELECT 
            t.ticket_id,
            t.data_apertura,
            t.data_chiusura,
            t.stato,
            t.tempo_stimato,
            CONCAT(c.nome, ' ', c.cognome) AS cliente,
            c.telefono AS cliente_telefono,
            m.nome_marca,
            t.modello,
            td.nome_tipo,
            p.codice_pda,
            p.nome_negozio,
            DATEDIFF(COALESCE(t.data_chiusura, CURRENT_DATE), t.data_apertura) AS giorni_lavorazione
        FROM TICKETS t
        JOIN CLIENTI c ON t.cliente_id = c.cliente_id
        JOIN MARCHE m ON t.marca_id = m.marca_id
        JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
        JOIN PDA p ON t.pda_id = p.pda_id
        $where_sql
        ORDER BY 
            CASE t.stato
                WHEN 'APERTO' THEN 1
                WHEN 'IN_RIPARAZIONE' THEN 2
                WHEN 'CHIUSO' THEN 3
            END,
            t.data_apertura DESC
    ");
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();
    
    // Carica lista PDA per filtro
    $pda_list = $conn->query("SELECT DISTINCT codice_pda FROM PDA ORDER BY codice_pda")->fetchAll();
    
} catch(PDOException $e) {
    error_log("Errore caricamento ticket: " . $e->getMessage());
    $tickets = [];
    $pda_list = [];
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-list-ul"></i> Elenco Ticket</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Elenco Ticket</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Filtri -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="elenco_ticket.php" class="row g-3">
                <div class="col-md-3">
                    <label for="stato" class="form-label">Stato</label>
                    <select class="form-select" id="stato" name="stato">
                        <option value="">Tutti</option>
                        <option value="APERTO" <?php echo $filtro_stato === 'APERTO' ? 'selected' : ''; ?>>Aperto</option>
                        <option value="IN_RIPARAZIONE" <?php echo $filtro_stato === 'IN_RIPARAZIONE' ? 'selected' : ''; ?>>In Riparazione</option>
                        <option value="CHIUSO" <?php echo $filtro_stato === 'CHIUSO' ? 'selected' : ''; ?>>Chiuso</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="pda" class="form-label">PDA</label>
                    <select class="form-select" id="pda" name="pda">
                        <option value="">Tutti</option>
                        <?php foreach ($pda_list as $pda): ?>
                        <option value="<?php echo htmlspecialchars($pda['codice_pda']); ?>" 
                                <?php echo $filtro_pda === $pda['codice_pda'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pda['codice_pda']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="ricerca" class="form-label">Ricerca</label>
                    <input type="text" class="form-control" id="ricerca" name="ricerca" 
                           value="<?php echo htmlspecialchars($filtro_ricerca); ?>"
                           placeholder="Cliente, marca, modello...">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cerca
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabella Ticket -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-ticket-detailed"></i> Ticket 
                <span class="badge bg-secondary"><?php echo count($tickets); ?></span>
            </h5>
            <a href="nuovo_ticket.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nuovo Ticket
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
            <div class="text-center p-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">Nessun ticket trovato</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Data Apertura</th>
                            <th>Cliente</th>
                            <th>Dispositivo</th>
                            <th>PDA</th>
                            <th>Stato</th>
                            <th>Giorni</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><strong>#<?php echo $ticket['ticket_id']; ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($ticket['data_apertura'])); ?></td>
                            <td>
                                <?php echo htmlspecialchars($ticket['cliente']); ?><br>
                                <?php if ($ticket['cliente_telefono']): ?>
                                <small class="text-muted"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($ticket['cliente_telefono']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($ticket['nome_tipo']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($ticket['nome_marca'] . ' ' . $ticket['modello']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary" title="<?php echo htmlspecialchars($ticket['nome_negozio']); ?>">
                                    <?php echo htmlspecialchars($ticket['codice_pda']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $badgeClass = '';
                                switch($ticket['stato']) {
                                    case 'APERTO': $badgeClass = 'badge-aperto'; break;
                                    case 'IN_RIPARAZIONE': $badgeClass = 'badge-in-riparazione'; break;
                                    case 'CHIUSO': $badgeClass = 'badge-chiuso'; break;
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo str_replace('_', ' ', $ticket['stato']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo $ticket['giorni_lavorazione']; ?></strong>
                                <?php if ($ticket['tempo_stimato']): ?>
                                <br><small class="text-muted">/ <?php echo $ticket['tempo_stimato']; ?> stim.</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="dettaglio_ticket.php?id=<?php echo $ticket['ticket_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Visualizza dettagli">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
