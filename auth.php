<?php
require __DIR__ . '/../db.php';
session_start();

function require_login(): void {
    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}

// Jeton anti-CSRF : empêche un autre site de déclencher une action à ta place
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Requête invalide.');
    }
}

function page_head(string $titre): void {
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . e($titre) . '</title><style>'
       . 'body{font:16px/1.6 system-ui,sans-serif;max-width:760px;margin:2rem auto;padding:0 1rem}'
       . 'label{display:block;margin-top:1rem;font-weight:600}'
       . 'input,textarea,select{width:100%;padding:.4rem;font:inherit}'
       . 'table{width:100%;border-collapse:collapse}td,th{border-bottom:1px solid #ccc;padding:.4rem;text-align:left}'
       . '.err{color:#b00020}button{font:inherit;padding:.4rem .8rem;margin-top:1rem}'
       . '.inline{display:inline}.inline button{margin:0}'
       . '</style></head><body>';
}
