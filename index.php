<?php
require __DIR__ . '/db.php';

$cat = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
$categories = $pdo->query('SELECT id, nom FROM categories ORDER BY nom')->fetchAll();

$sql = 'SELECT r.*, c.nom AS categorie
        FROM ressources r JOIN categories c ON c.id = r.categorie_id';
if ($cat > 0) {
    $st = $pdo->prepare($sql . ' WHERE r.categorie_id = ? ORDER BY r.date_verif DESC, r.id DESC');
    $st->execute([$cat]);
} else {
    $st = $pdo->query($sql . ' ORDER BY r.date_verif DESC, r.id DESC');
}
$ressources = $st->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ressources vérifiées sur l’Ukraine</title>
<style>
body{font:16px/1.6 system-ui,sans-serif;max-width:760px;margin:2rem auto;padding:0 1rem}
nav a{margin-right:.8rem}.on{font-weight:700}
input[type=search]{width:100%;padding:.5rem;font:inherit;margin:1rem 0 .5rem}
article{border-top:1px solid #ccc;padding:1rem 0}
small{color:#555}
</style>
</head>
<body>
<h1>Ressources vérifiées sur l’Ukraine</h1>
<p>Chaque ressource indique sa source et la date de vérification.</p>
<nav>
  <a href="index.php" data-cat="0" class="<?= $cat === 0 ? 'on' : '' ?>">Toutes</a>
  <?php foreach ($categories as $c): ?>
    <a href="?cat=<?= (int) $c['id'] ?>" data-cat="<?= (int) $c['id'] ?>" class="<?= $cat === (int) $c['id'] ? 'on' : '' ?>"><?= e($c['nom']) ?></a>
  <?php endforeach; ?>
</nav>
<label for="q" hidden>Rechercher</label>
<input type="search" id="q" placeholder="Rechercher dans les titres et descriptions">
<p id="compte" aria-live="polite"></p>

<!-- Rendu côté serveur : la page fonctionne même sans JavaScript -->
<div id="liste">
<?php if (!$ressources): ?>
  <p>Aucune ressource pour le moment.</p>
<?php endif; ?>
<?php foreach ($ressources as $r): ?>
  <article>
    <h2><a href="<?= e($r['url']) ?>" rel="noopener"><?= e($r['titre']) ?></a></h2>
    <p><?= e($r['description']) ?></p>
    <small><?= e($r['categorie']) ?> · Source : <?= e($r['source']) ?> · Vérifié le <?= e($r['date_verif']) ?></small>
  </article>
<?php endforeach; ?>
</div>

<script>
// Client JavaScript : interroge api.php avec fetch et met la liste à jour sans recharger la page
const liste = document.getElementById('liste');
const champ = document.getElementById('q');
const compte = document.getElementById('compte');
const liens = document.querySelectorAll('nav a');
let cat = <?= (int) $cat ?>;
let minuteur;

// On n’accepte que les liens http(s) pour éviter les adresses de type javascript:
const urlSure = (u) => (/^https?:\/\//i.test(u) ? u : '#');

function creer(tag, texte, parent) {
  const el = document.createElement(tag);
  if (texte) el.textContent = texte; // textContent : jamais de HTML injecté
  if (parent) parent.appendChild(el);
  return el;
}

function afficher(ressources) {
  liste.replaceChildren();
  compte.textContent = ressources.length + ' ressource(s)';
  if (ressources.length === 0) {
    creer('p', 'Aucune ressource ne correspond à votre recherche.', liste);
    return;
  }
  for (const r of ressources) {
    const art = creer('article', '', liste);
    const h2 = creer('h2', '', art);
    const a = creer('a', r.titre, h2);
    a.href = urlSure(r.url);
    a.rel = 'noopener';
    creer('p', r.description, art);
    creer('small', r.categorie + ' · Source : ' + r.source + ' · Vérifié le ' + r.date_verif, art);
  }
}

async function charger() {
  const params = new URLSearchParams();
  if (cat > 0) params.set('cat', cat);
  if (champ.value.trim() !== '') params.set('q', champ.value.trim());
  try {
    const reponse = await fetch('api.php?' + params.toString());
    if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
    const donnees = await reponse.json();
    afficher(donnees.ressources);
  } catch (erreur) {
    liste.replaceChildren();
    creer('p', 'Impossible de charger les ressources. Réessayez plus tard.', liste);
  }
}

liens.forEach((lien) => {
  lien.addEventListener('click', (ev) => {
    ev.preventDefault();
    cat = parseInt(lien.dataset.cat, 10);
    liens.forEach((l) => l.classList.toggle('on', l === lien));
    charger();
  });
});

// Recherche : on attend 250 ms après la dernière frappe avant d’appeler l’API
champ.addEventListener('input', () => {
  clearTimeout(minuteur);
  minuteur = setTimeout(charger, 250);
});
</script>
</body>
</html>
