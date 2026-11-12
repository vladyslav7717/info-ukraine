# Ressources vérifiées sur l’Ukraine

Petit catalogue de sources fiables (organisations officielles, vérification de l’information, démarches en France). Chaque ressource indique sa source et sa date de vérification.

Projet réalisé en première année de BUT MMI.

## Fonctionnalités

- Site public avec filtre par catégorie et recherche sans rechargement de page (JavaScript + `fetch`)
- API JSON en lecture seule (`api.php`, paramètres `cat` et `q`)
- Administration protégée par mot de passe : ajout, modification, suppression (CRUD)

## Technologies

PHP 8, MySQL, PDO, JavaScript, HTML, CSS

## Sécurité

Requêtes préparées (injection SQL), échappement du HTML (XSS), `password_hash` / `password_verify`, jeton CSRF, validation des formulaires et des URL.

## Installation en local (Laragon)

1. Copier le projet dans `C:\laragon\www\info_ukraine`.
2. Exécuter `schema.sql` puis `seed.sql` dans HeidiSQL.
3. Copier `config.example.php` en `config.php` et adapter les valeurs.
4. Ouvrir `http://localhost/info_ukraine/`.

## Mot de passe administrateur

Générer un hash avec `php -r "echo password_hash('TON_MOT_DE_PASSE', PASSWORD_DEFAULT);"` (ou avec un petit script temporaire), puis le coller dans `ADMIN_HASH` de `config.php`.
