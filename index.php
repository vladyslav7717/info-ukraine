<?php
require __DIR__ . '/auth.php';
require_login();

$lignes = $pdo->query(
    'SELECT r.id, r.titre, r.date_verif, c.nom AS categorie
     FROM ressources r JOIN categories c ON c.id = r.categorie_id
     ORDER BY r.id DESC'
)->fetchAll();

page_head('Administration');
?>
<h1>Administration</h1>
<p><a href="form.php">+ Ajouter une ressource</a> · <a href="../index.php">Voir le site</a> · <a href="logout.php">Déconnexion</a></p>
<table>
  <tr><th>Titre</th><th>Catégorie</th><th>Vérifié le</th><th></th></tr>
  <?php foreach ($lignes as $l): ?>
  <tr>
    <td><?= e($l['titre']) ?></td>
    <td><?= e($l['categorie']) ?></td>
    <td><?= e($l['date_verif']) ?></td>
    <td>
      <a href="form.php?id=<?= (int) $l['id'] ?>">Modifier</a>
      <form class="inline" method="post" action="delete.php" onsubmit="return confirm('Supprimer cette ressource ?')">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
        <button type="submit">Supprimer</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</body></html>
