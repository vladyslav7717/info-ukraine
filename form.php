<?php
require __DIR__ . '/auth.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$r = ['titre' => '', 'description' => '', 'url' => '', 'source' => '',
      'date_verif' => date('Y-m-d'), 'categorie_id' => 0];

if ($id > 0) {
    $st = $pdo->prepare('SELECT * FROM ressources WHERE id = ?');
    $st->execute([$id]);
    $r = $st->fetch();
    if (!$r) { http_response_code(404); exit('Ressource introuvable.'); }
}

$categories = $pdo->query('SELECT id, nom FROM categories ORDER BY nom')->fetchAll();
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $r = [
        'titre'        => trim($_POST['titre'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
        'url'          => trim($_POST['url'] ?? ''),
        'source'       => trim($_POST['source'] ?? ''),
        'date_verif'   => trim($_POST['date_verif'] ?? ''),
        'categorie_id' => (int) ($_POST['categorie_id'] ?? 0),
    ];

    if ($r['titre'] === '' || mb_strlen($r['titre']) > 150) $erreurs[] = 'Titre : 1 à 150 caractères.';
    if ($r['description'] === '') $erreurs[] = 'La description est obligatoire.';
    // Seuls http(s) sont acceptés : on évite les liens de type javascript:
    if (!preg_match('#^https?://#i', $r['url']) || !filter_var($r['url'], FILTER_VALIDATE_URL) || mb_strlen($r['url']) > 500)
        $erreurs[] = 'URL invalide (elle doit commencer par http:// ou https://).';
    if ($r['source'] === '' || mb_strlen($r['source']) > 150) $erreurs[] = 'Source : 1 à 150 caractères.';
    $d = DateTime::createFromFormat('Y-m-d', $r['date_verif']);
    if (!$d || $d->format('Y-m-d') !== $r['date_verif']) $erreurs[] = 'Date invalide (format AAAA-MM-JJ).';
    $ok = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
    $ok->execute([$r['categorie_id']]);
    if (!$ok->fetchColumn()) $erreurs[] = 'Choisis une catégorie.';

    if (!$erreurs) {
        $params = [$r['titre'], $r['description'], $r['url'], $r['source'], $r['date_verif'], $r['categorie_id']];
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE ressources SET titre=?, description=?, url=?, source=?, date_verif=?, categorie_id=? WHERE id=?');
            $st->execute([...$params, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO ressources (titre, description, url, source, date_verif, categorie_id) VALUES (?,?,?,?,?,?)');
            $st->execute($params);
        }
        header('Location: index.php');
        exit;
    }
}

page_head($id > 0 ? 'Modifier' : 'Ajouter');
?>
<h1><?= $id > 0 ? 'Modifier la ressource' : 'Ajouter une ressource' ?></h1>
<?php foreach ($erreurs as $m): ?><p class="err"><?= e($m) ?></p><?php endforeach; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label for="titre">Titre</label>
  <input id="titre" name="titre" value="<?= e($r['titre']) ?>" required>
  <label for="description">Description</label>
  <textarea id="description" name="description" rows="4" required><?= e($r['description']) ?></textarea>
  <label for="url">Lien (URL)</label>
  <input id="url" name="url" type="url" value="<?= e($r['url']) ?>" required>
  <label for="source">Source</label>
  <input id="source" name="source" value="<?= e($r['source']) ?>" required>
  <label for="date_verif">Date de vérification</label>
  <input id="date_verif" name="date_verif" type="date" value="<?= e($r['date_verif']) ?>" required>
  <label for="categorie_id">Catégorie</label>
  <select id="categorie_id" name="categorie_id" required>
    <option value="">Choisir…</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= (int) $c['id'] ?>" <?= (int) $r['categorie_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['nom']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Enregistrer</button> <a href="index.php">Annuler</a>
</form>
</body></html>
