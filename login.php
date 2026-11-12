<?php
require __DIR__ . '/auth.php';

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // password_verify compare le mot de passe saisi avec le hash enregistré
    if (password_verify($_POST['mdp'] ?? '', ADMIN_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }
    $erreur = 'Mot de passe incorrect.';
}
page_head('Connexion');
?>
<h1>Connexion administrateur</h1>
<?php if ($erreur): ?><p class="err"><?= e($erreur) ?></p><?php endif; ?>
<form method="post">
  <label for="mdp">Mot de passe</label>
  <input type="password" id="mdp" name="mdp" required autofocus>
  <button type="submit">Se connecter</button>
</form>
</body></html>
