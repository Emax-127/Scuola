<?php
/**
 * Login Page
 * Sistema Gestione Ticket Riparazione
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';

// Se già autenticato, redirect alla dashboard
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Gestione del form di login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Inserisci username e password.';
    } else {
        if (login($username, $password)) {
            header("Location: index.php");
            exit();
        } else {
            $error = 'Credenziali non valide. Riprova.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="login-container">
        <div class="card login-card">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-tools" style="font-size: 3rem; color: #007bff;"></i>
                    <h3 class="mt-2"><?php echo APP_NAME; ?></h3>
                    <p class="text-muted">Sistema Informativo per la Gestione Ticket di Riparazione</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Accedi
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <small class="text-muted">
                        <strong>Utenti di test:</strong><br>
                        Username: <code>pda_rossi</code> | Password: <code>password</code><br>
                        Username: <code>admin</code> | Password: <code>password</code>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
