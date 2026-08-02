# Travelia

Site web de réservation de destinations touristiques.

## Technologies utilisées

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript

## Fonctionnalités

- Inscription / Connexion
- Gestion des sessions
- Liste des destinations
- Réservations
- Tableau de bord administrateur
- Gestion utilisateurs/destinations/réservations

## Organisation recommandée pour la maintenance

Pour faciliter l’ajout de nouvelles fonctionnalités, il est recommandé d’évoluer vers une organisation légère par modules ou un modèle MVC minimaliste :

- `config/` ou `INCLUDE/` : configuration et fonctions partagées (DB, sécurité, validation).
- `app/controllers/` : logique métier et traitement des formulaires.
- `app/models/` : accès aux données et requêtes SQL préparées.
- `app/views/` : templates HTML / fragments réutilisables (`header.php`, `footer.php`, listes, formulaires).
- `public/` : pages accessibles publiquement, avec un front controller unique (`index.php`) si possible.

Avec cette organisation, chaque nouvelle fonctionnalité devient une unité plus sûre à modifier :

- une route / un contrôleur par flux
- un modèle par entité (utilisateurs, destinations, réservations, avis)
- des vues séparées du code métier

## Conventions de code

- Toujours utiliser `require_once __DIR__ . '/...';` pour inclure des fichiers.
- Centraliser la connexion DB dans une fonction unique (`get_db_connection()`).
- Préparer toutes les requêtes SQL avec `prepare()` et `bind_param()`.
- Valider côté serveur toutes les données entrantes avant tout traitement.
- Échapper toute sortie HTML avec `htmlspecialchars(..., ENT_QUOTES)`.
- Utiliser des jetons CSRF sur tous les formulaires sensibles.
- Standardiser les variables d’état (`$error`, `$success`) et les messages affichés.
- Factoriser les règles de validation dans des fonctions réutilisables.

## Règles de sécurité

- Protection contre les injections SQL : requêtes préparées + validation stricte.
- Protection contre XSS : échappement de toute chaîne affichée dans une page.
- Protection contre CSRF : vérifier le token CSRF pour chaque requête POST.
- Gestion des sessions : régénération d’ID après connexion et paramétrage sécurisé de la session.
- Téléchargement de fichiers : limiter les types autorisés, la taille maximale et vérifier les erreurs d’upload.
- Messages d’erreur : afficher des retours utiles à l’utilisateur sans exposer les détails internes.

## Stratégie de tests

### Tests manuels recommandés

- Inscription
  - remplir le formulaire avec des données valides
  - tester les cas de validation invalides (email invalide, mot de passe court, email déjà utilisé)
- Connexion
  - connexion réussie
  - tentative avec mauvais mot de passe / utilisateur inconnu
  - verrouillage ou message d’erreur clair en cas d’échec répété
- Réservation
  - réservation d’une destination valide
  - saisie d’une date ou d’un nombre de voyageurs invalide
  - vérification de la création en base et de l’affichage dans le tableau de bord
- Avis
  - envoi d’un avis valide
  - blocage des avis vides ou trop longs
  - vérification de l’affichage du nouvel avis
- Administration
  - création d’une destination avec image
  - modification d’une destination existante
  - suppression d’une destination
  - mise à jour du statut de réservation
  - gestion des rôles utilisateurs

### Tests automatiques possibles

- tests unitaires PHP pour les helpers de validation et de sécurité
- tests d’intégration pour les flux critiques : inscription, connexion, réservation, avis, administration
- tests fonctionnels de bout en bout avec Selenium / Playwright / Cypress pour les pages web
- scripts Postman pour vérifier les API internes ou les points d’entrée POST

## Objectif

Rendre les futures modifications plus sûres en appliquant des conventions claires, en factorisant l’accès aux données et en définissant une stratégie de tests explicite pour les flux clés.
