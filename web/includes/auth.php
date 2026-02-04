<?php
/**
 * Authentication Functions
 * Sistema Gestione Ticket Riparazione
 */

session_start();

// Verifica se l'utente è autenticato
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Verifica timeout sessione
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if ($elapsed > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}

// Richiede autenticazione (redirect se non autenticato)
function requireAuth() {
    if (!isLoggedIn() || !checkSessionTimeout()) {
        header("Location: login.php");
        exit();
    }
}

// Effettua il login
function login($username, $password) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, ruolo, email FROM USERS WHERE username = ? AND attivo = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login riuscito
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['ruolo'] = $user['ruolo'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
            // Se è un PDA, recupera anche il codice PDA
            if ($user['ruolo'] === 'PDA') {
                $stmt = $conn->prepare("SELECT pda_id, codice_pda FROM PDA WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);
                $pda = $stmt->fetch();
                if ($pda) {
                    $_SESSION['pda_id'] = $pda['pda_id'];
                    $_SESSION['codice_pda'] = $pda['codice_pda'];
                }
            }
            
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Errore login: " . $e->getMessage());
        return false;
    }
}

// Effettua il logout
function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Verifica se l'utente ha un determinato ruolo
function hasRole($role) {
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === $role;
}

// Ottiene l'ID del PDA dell'utente corrente (se è un PDA)
function getCurrentPdaId() {
    return isset($_SESSION['pda_id']) ? $_SESSION['pda_id'] : null;
}
?>
