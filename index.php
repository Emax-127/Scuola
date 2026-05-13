<?php
session_start();

$formLink = 'https://docs.google.com/forms/d/e/1FAIpQLSfLLcEQUvBQFh5k2qb7GhslnmBPHFidaOUhXGXFsNTlSX0ImQ/viewform?usp=dialog';
$errors = [];
$generalError = '';
$values = [
    'name' => '',
    'email' => '',
    'class' => '',
];

$formLinkIsValid = filter_var($formLink, FILTER_VALIDATE_URL) !== false;
if (!$formLinkIsValid) {
    $generalError = 'Link al modulo non valido. Contatta il docente.';
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name'] = trim($_POST['name'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $values['class'] = trim($_POST['class'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $generalError = 'Sessione non valida. Riprova.';
    } elseif ($generalError === '') {
        if ($values['name'] === '') {
            $errors['name'] = 'Inserisci il nome.';
        }

        if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Inserisci un indirizzo email valido.';
        }

        if ($values['class'] === '') {
            $errors['class'] = 'Inserisci la classe.';
        }

        if ($password === '' || strlen($password) < 6) {
            $errors['password'] = 'La password deve avere almeno 6 caratteri.';
        }

        if ($errors === []) {
            header('Location: ' . $formLink);
            exit;
        }
    }
}

function field_value(string $key, array $values): string
{
    return htmlspecialchars($values[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrazione - TPSIT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        h1 {
            margin-top: 0;
        }
        label {
            display: block;
            margin-top: 16px;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error {
            color: #b00020;
            margin-top: 6px;
            font-size: 0.9rem;
        }
        button {
            margin-top: 20px;
            padding: 12px 16px;
            width: 100%;
            border: none;
            background: #1a73e8;
            color: #fff;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #1558b5;
        }
        .hint {
            margin-top: 6px;
            color: #5f6368;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrazione TPSIT</h1>
        <p>Compila il form per accedere al modulo domande.</p>

        <?php if ($generalError !== '') : ?>
            <div class="error"><?php echo htmlspecialchars($generalError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <label for="name">Nome</label>
            <input type="text" id="name" name="name" required value="<?php echo field_value('name', $values); ?>">
            <?php if (isset($errors['name'])) : ?>
                <div class="error"><?php echo htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo field_value('email', $values); ?>">
            <?php if (isset($errors['email'])) : ?>
                <div class="error"><?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <label for="class">Classe</label>
            <input type="text" id="class" name="class" required value="<?php echo field_value('class', $values); ?>">
            <?php if (isset($errors['class'])) : ?>
                <div class="error"><?php echo htmlspecialchars($errors['class'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <div class="hint">La password non viene salvata, serve solo per l'accesso al modulo.</div>
            <?php if (isset($errors['password'])) : ?>
                <div class="error"><?php echo htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <button type="submit">Registrati e vai al modulo</button>
        </form>
    </div>
</body>
</html>
