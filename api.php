<?php
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// L’API est en lecture seule : seule la méthode GET est acceptée
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erreur' => 'Méthode non autorisée']);
    exit;
}

$cat = (int) ($_GET['cat'] ?? 0);
$q   = trim($_GET['q'] ?? '');

$sql = 'SELECT r.id, r.titre, r.description, r.url, r.source, r.date_verif, c.nom AS categorie
        FROM ressources r JOIN categories c ON c.id = r.categorie_id';
$where = [];
$params = [];

if ($cat > 0) {
    $where[] = 'r.categorie_id = ?';
    $params[] = $cat;
}
if ($q !== '') {
    // addcslashes neutralise les caractères joker % et _ saisis par l’utilisateur
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(r.titre LIKE ? OR r.description LIKE ?)';
    $params[] = $like;
    $params[] = $like;
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.date_verif DESC, r.id DESC';

$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

echo json_encode(
    ['nombre' => count($rows), 'ressources' => $rows],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
