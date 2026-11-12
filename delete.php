<?php
require __DIR__ . '/auth.php';
require_login();

// La suppression n’est acceptée qu’en POST, avec le jeton anti-CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $st = $pdo->prepare('DELETE FROM ressources WHERE id = ?');
    $st->execute([(int) ($_POST['id'] ?? 0)]);
}
header('Location: index.php');
