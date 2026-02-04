<?php
/**
 * Dashboard - Home Page
 * Sistema Gestione Ticket Riparazione
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth();

$pageTitle = 'Dashboard';

$db = new Database();
$conn = $db->getConnection();

// Statistiche generali
$stats = [
    'totali' => 0,
    'aperti' => 0,
    'in_riparazione' => 0,
    'chiusi' => 0
];

try {
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as totali,
            SUM(CASE WHEN stato = 'APERTO' THEN 1 ELSE 0 END) as aperti,
            SUM(CASE WHEN stato = 'IN_RIPARAZIONE' THEN 1 ELSE 0 END) as in_riparazione,
            SUM(CASE WHEN stato = 'CHIUSO' THEN 1 ELSE 0 END) as chiusi
        FROM TICKETS
    ");
    $result = $stmt->fetch();
    if ($result) {
        $stats = $result;
    }
    
    // Ultimi ticket
    $stmt = $conn->prepare("
        SELECT 
            t.ticket_id,
            t.data_apertura,
            t.stato,
            CONCAT(c.nome, ' ', c.cognome) AS cliente,
            m.nome_marca,
            t.modello,
            td.nome_tipo,
            p.codice_pda
        FROM TICKETS t
        JOIN CLIENTI c ON t.cliente_id = c.cliente_id
        JOIN MARCHE m ON t.marca_id = m.marca_id
        JOIN TIPI_DISPOSITIVO td ON t.tipo_id = td.tipo_id
        JOIN PDA p ON t.pda_id = p.pda_id
        ORDER BY t.data_apertura DESC, t.ticket_id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $ultimi_ticket = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Errore caricamento dashboard: " . $e->getMessage());
    $ultimi_ticket = [];
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
            <p class="text-muted">Panoramica del sistema di gestione ticket</p>
        </div>
    </div>
    
    <!-- Statistiche Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card totali">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Ticket Totali</h6>
                            <h2 class="mb-0"><?php echo $stats['totali']; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-ticket-detailed" style="font-size: 2.5rem; color: #007bff;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card aperti">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Aperti</h6>
                            <h2 class="mb-0"><?php echo $stats['aperti']; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-folder2-open" style="font-size: 2.5rem; color: #ffc107;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card in-riparazione">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">In Riparazione</h6>
                            <h2 class="mb-0"><?php echo $stats['in_riparazione']; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-tools" style="font-size: 2.5rem; color: #17a2b8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card chiusi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Chiusi</h6>
                            <h2 class="mb-0"><?php echo $stats['chiusi']; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-check-circle" style="font-size: 2.5rem; color: #28a745;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Azioni rapide -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-lightning"></i> Azioni Rapide</h5>
                    <div class="d-flex gap-2">
                        <a href="nuovo_ticket.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Nuovo Ticket
                        </a>
                        <a href="elenco_ticket.php" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> Visualizza Tutti i Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ultimi ticket -->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Ultimi Ticket</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($ultimi_ticket)): ?>
                    <div class="text-center p-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Nessun ticket presente</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Dispositivo</th>
                                    <th>PDA</th>
                                    <th>Stato</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimi_ticket as $ticket): ?>
                                <tr>
                                    <td><strong>#<?php echo $ticket['ticket_id']; ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($ticket['data_apertura'])); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['cliente']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($ticket['nome_tipo']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($ticket['nome_marca'] . ' ' . $ticket['modello']); ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($ticket['codice_pda']); ?></span></td>
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
                                        <a href="dettaglio_ticket.php?id=<?php echo $ticket['ticket_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Visualizza
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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
