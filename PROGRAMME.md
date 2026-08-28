# Programme d'apprentissage Symfony — Backend API

**Profil** : PHP connu et pratiqué, découverte du framework Symfony · rythme intensif (quasi quotidien) · suivi via ce document, mis à jour au fil des sessions.

**Environnement** : projet Symfony 7 (skeleton minimal, sans le pack `webapp` — pas de Twig, pas de Forms, on n'installe que ce dont un backend API a besoin), PHP 8.2+, Composer, Symfony CLI, base SQLite en local, Doctrine ORM, VS Code avec l'extension REST Client. Dépôt Git relié à GitHub, en Gitflow dès le Jour 1. Chaque jour = un défi concret avec des critères de réussite vérifiables (réponse JSON testée au `curl` ou via `requests.http`, sortie de commande, tests qui passent, lien de la PR).

**Comment ça marche** : tu fais le défi du jour en local, Claude Code corrige, commente ce qui aurait pu être fait autrement, et coche l'étape ici. Rien n'est chronométré à la seconde près — l'idée est d'enchaîner les jours sans trop de blancs pour que ça reste engageant.

**Mini-projet fil rouge** : une bibliothèque personnelle, exposée en **API JSON pure** (pas d'interface web, pas de Twig). Un utilisateur peut s'inscrire, ajouter des livres (titre, description, date de publication, auteur, catégories) via l'API, les modifier ou les supprimer, et ne peut gérer que ses propres livres. Le projet se construit couche par couche : d'abord des endpoints simples, puis une vraie persistance en base, puis création/modification via des payloads JSON validés, puis authentification par token, puis pagination/filtres et tests.

---

## Phase 0 — Mise en place (Jour 1)

Objectif : un projet Symfony minimal qui tourne, structure comprise, dépôt Git en Gitflow dès le départ.

- [x] Vérifier PHP (8.2+) et Composer installés (`php -v`, `composer -V`).
- [x] Installer le Symfony CLI si absent (`symfony check:requirements`).
- [x] Créer le projet avec `symfony new .` **sans** `--webapp` (dans le dossier `symfony-dojo` déjà présent) — squelette minimal (routing, HttpFoundation) : pas de Twig ni de Forms installés d'emblée, on ajoutera chaque brique (Doctrine, Validator, Serializer, Security) au moment où le programme en a réellement besoin, comme sur un vrai projet API.
- [x] Configurer `DATABASE_URL` dans `.env.local` pour utiliser SQLite (fichier dans `var/`), pas de Docker nécessaire pour ce dojo.
- [x] Lancer `symfony server:start` (ou `symfony serve -d`) et vérifier qu'il répond (même une 404 sur `/` est normale : aucune route n'est encore déclarée).
- [x] Initialiser Git si besoin, créer le repo GitHub `symfony-dojo`, relier, `.gitignore` Symfony standard (`vendor/`, `var/`, `.env.local`), premier commit "Initial commit" sur `main`.
- [x] Créer `develop` à partir de `main`, la pousser, la définir comme branche par défaut pour les PR.
- [x] Explorer la structure générée : `src/`, `config/`, `public/index.php`, `.env` — comprendre à quoi sert chaque dossier avant d'écrire du code.

**Défi du jour** : me montrer que le serveur démarre sans erreur (`symfony server:start` ou `symfony serve`), plus `git log --graph --all --decorate` montrant `main` et `develop`.

---

## Phase 1 — Fondamentaux (Jours 2 à 4)

### Jour 2 — Routing et contrôleurs API
- Route par attribut PHP (`#[Route('/api/...', name: 'app_api_xxx', methods: ['GET'])]`), paramètres de route (`/api/hello/{name}`), `JsonResponse` plutôt que `Response` (pourquoi laisser Symfony gérer l'encodage JSON et les headers plutôt que faire `echo json_encode(...)` à la main).
- `bin/console debug:router` pour lister les routes déclarées.
- Défi : un endpoint `GET /api/ping` qui renvoie `{"status": "ok"}` + une route dynamique `GET /api/hello/{name}` qui renvoie un JSON incluant le paramètre.
- Branche : `feature/routes-api-de-base`.
- Livrable : code des deux contrôleurs + sortie de `debug:router` + réponses `curl`.

### Jour 3 — Sérialisation JSON
- Installer le composant Serializer (`composer require symfony/serializer symfony/property-access`). Pourquoi ne pas sérialiser "à la main" (`json_encode` sur un objet) dès que la structure se complexifie : groupes de sérialisation, normalisation cohérente, séparation entre le modèle PHP et sa représentation JSON.
- Défi : un tableau d'objets `Book` codés en dur dans le contrôleur (pas encore Doctrine) exposé via `GET /api/books`, sérialisé avec le `SerializerInterface`. Si le tableau est vide, l'endpoint renvoie `[]` (pas de HTML conditionnel, juste une réponse JSON cohérente dans les deux cas).
- Branche : `feature/serialisation-livres`.
- Livrable : code du contrôleur + réponse `curl` avec liste pleine, puis vidée dans le code pour montrer `[]`.

### Jour 4 — Doctrine et première entité
- Installer Doctrine (`composer require orm-pack`, puis `--dev maker`). `make:entity Author` (nom), comprendre le mapping par attributs (`#[ORM\Entity]`, `#[ORM\Column]`), générer et exécuter une migration (`make:migration` ou `doctrine:migrations:diff` puis `doctrine:migrations:migrate`).
- Comprendre le rôle de l'`EntityManager` et d'un `Repository`.
- Défi : `GET /api/authors` vient maintenant de la base de données (quelques auteurs insérés à la main) via `AuthorRepository`, plus de tableau en dur.
- Branche : `feature/persistance-auteurs`.
- Livrable : code du contrôleur modifié + preuve que les données viennent bien de la base (ex. ajout d'un auteur en base, requête `curl` rafraîchie sans toucher au code).

**Fin de Phase 1** : créer `release/0.1.0` depuis `develop`, relire l'ensemble du code des jours 2-4, merger dans `main` et `develop`, taguer `v0.1.0`.

---

## Phase 2 — Comprendre l'architecture Symfony (Jour 5)

Objectif : théorie avant pratique, pour ne pas empiler du code Symfony sans comprendre le modèle sous-jacent — surtout venant de PHP "classique".

- Cycle requête/réponse : front controller (`public/index.php`), Kernel, Router, Controller, Response — et comment ça diffère d'un script PHP exécuté directement par le serveur.
- Bundles : ce qu'ils sont, pourquoi Symfony est construit comme un assemblage de bundles plutôt qu'un monolithe (et pourquoi on n'a installé que `orm-pack`/`serializer` et pas `webapp` en entier au Jour 1).
- Conteneur de services et injection de dépendances : pourquoi un contrôleur ou un service reçoit ses dépendances via le constructeur plutôt que de faire `new MonService()` ou d'aller chercher une instance globale.
- Environnements (`dev`/`prod`/`test`), fichiers `.env` et `config/packages/` : comment la configuration change selon l'environnement.

**Défi du jour** : m'expliquer avec tes mots (2-3 phrases) pourquoi Symfony utilise l'injection de dépendances plutôt que d'instancier les classes à la volée, avec un exemple concret (ex. injecter l'`EntityManager` ou le `SerializerInterface` dans un contrôleur) plutôt qu'une explication abstraite.

---

## Phase 3 — CRUD API et relations, à la main (Jours 6 à 9)

On construit le CRUD complet à la main (désérialisation, validation, contrôleur), sans utiliser `make:crud` — l'idée est de comprendre chaque brique avant de laisser un générateur les assembler. `make:entity` et `make:controller` restent autorisés pour le squelette vide (ils ne génèrent pas de logique).

### Jour 6 — Entité Book et relation ManyToOne
- `make:entity Book` (titre, description, date de publication) + relation `ManyToOne` vers `Author`.
- Installer `DoctrineFixturesBundle`, écrire une fixture qui peuple quelques auteurs et livres factices.
- Deux pièges classiques du Serializer avec Doctrine à anticiper : le format par défaut d'un `DateTime` sérialisé (objet verbeux avec timezone plutôt qu'une simple chaîne ISO — un `DateTimeNormalizer` ou un format explicite règle ça), et la sérialisation circulaire si la relation est bidirectionnelle (`Book` → `author` → `books` → ... à l'infini) — à limiter avec des groupes de sérialisation ou en ne mappant pas la relation inverse pour l'instant.
- Défi : `doctrine:fixtures:load` fonctionne, `GET /api/books` renvoie les livres avec leur auteur imbriqué dans le JSON (relation traversée depuis le repository, exposée via des groupes de sérialisation ou une structure dédiée), avec une date de publication lisible dans le JSON.
- Branche : `feature/entite-book-et-fixtures`.
- Livrable : code de l'entité, de la fixture, et de la requête utilisée pour construire la réponse.

### Jour 7 — Création via l'API (désérialisation + validation)
- Désérialiser un payload JSON entrant en objet (`$serializer->deserialize()`) et le valider avec le `ValidatorInterface` (contraintes `Assert` posées sur l'entité) — pas de Symfony Forms ici : les Forms servent à générer/gérer un formulaire HTML affiché à un utilisateur, alors qu'ici on reçoit directement un payload JSON d'un client (curl, front séparé, appli mobile).
- Le `curl` en une ligne devient vite lourd dès qu'il faut un body JSON : on passe à un fichier `requests.http` versionné à la racine, exécuté depuis VS Code (extension REST Client) — une requête par bloc, variables réutilisables pour l'URL de base et le token à venir au Jour 10.
- Défi : `POST /api/books` avec un corps JSON crée un livre en base, réponse `201 Created` avec un header `Location` vers `/api/books/{id}`.
- Branche : `feature/creation-livre-api`.
- Livrable : code du contrôleur + requête dans `requests.http` + réponse `201`.

### Jour 8 — CRUD complet et validation
- Actions `show` (`GET /api/books/{id}`), `edit` (`PUT`/`PATCH`), `delete` (`DELETE`), contraintes de validation sur l'entité (`Assert\NotBlank`, `Assert\Length`...), erreurs renvoyées en JSON structuré (ex. liste des violations) avec un statut `400`.
- Par défaut, Symfony renvoie du HTML pour les erreurs non gérées (route inexistante → 404 HTML, exception non catchée → 500 HTML, body JSON malformé envoyé à `deserialize()`) — ce qui casse la promesse "jamais de HTML" de l'API. Mettre en place un listener sur `kernel.exception` qui transforme systématiquement ces cas en réponse JSON avec le bon statut.
- Pourquoi pas de token CSRF ici : CSRF protège des requêtes envoyées depuis un navigateur via des cookies de session ; une API stateless consommée par `curl`/un client externe n'est pas concernée — la protection viendra de l'authentification par token (Jour 10).
- Défi : CRUD `Book` complet (create/read/update/delete) validé de bout en bout, avec une requête invalide renvoyant `400` et le détail des erreurs en JSON, un `GET` sur un id inexistant renvoyant un `404` en JSON, et un body JSON malformé renvoyant une erreur JSON propre plutôt qu'une page HTML.
- Branche : `feature/crud-livre-complet-api`.
- Livrable : démonstration des 4 actions au `curl` + réponse JSON d'une erreur de validation + réponse JSON d'un 404.

### Jour 9 — Relation ManyToMany
- Entité `Category`, relation `ManyToMany` avec `Book`. Le payload JSON de création/édition accepte un tableau d'identifiants de catégories à associer.
- Même vigilance qu'au Jour 6 sur la sérialisation circulaire si `Category` référence `books` en retour : limiter via des groupes de sérialisation plutôt que sérialiser la relation inverse.
- Défi : un livre peut avoir plusieurs catégories, assignables via `POST`/`PUT`, visibles dans le JSON de la fiche livre (`GET /api/books/{id}`).
- Branche : `feature/categories-livres`.
- Livrable : réponse JSON d'un livre avec plusieurs catégories + code du contrôleur modifié.

**Fin de Phase 3** : créer `release/0.2.0` depuis `develop`, relire l'ensemble du CRUD produit, merger dans `main` et `develop`, taguer `v0.2.0`.

---

## Phase 4 — Écosystème et outillage (Jours 10 à 12)

### Jour 10 — Sécurité API et propriété des données
- `make:user`, endpoints `POST /api/register` et `POST /api/login`. Authentification par token API (colonne `apiToken` + authenticator dédié pour commencer — plus simple à comprendre qu'un JWT signé ; upgrade vers `LexikJWTAuthenticationBundle` en bonus si tu veux aller plus loin). Le client authentifie ses requêtes suivantes via le header `Authorization: Bearer <token>`.
- À l'inscription, un email déjà utilisé doit renvoyer un `409 Conflict` en JSON plutôt que laisser remonter l'erreur SQL de contrainte unique brute.
- Relation `Book` → `User` (propriétaire), contrôle d'accès : seul le propriétaire peut éditer/supprimer son livre (voter ou vérification explicite dans le contrôleur), réponse `401` si non authentifié, `403` si authentifié mais pas propriétaire (JSON dans les deux cas).
- Note CORS : les tests restent en `curl`/REST Client donc le navigateur n'est pas concerné ici, mais un vrai front séparé sur un autre domaine serait bloqué sans `NelmioCorsBundle` — à garder en tête si le projet doit un jour être consommé par une appli web.
- Défi : inscription et connexion fonctionnelles (token obtenu), une tentative de ré-inscription avec le même email renvoyant `409`, et preuve qu'un utilisateur ne peut pas éditer le livre d'un autre (testé avec deux comptes, deux tokens).
- Branche : `feature/authentification-api-et-proprietaire`.
- Livrable : démonstration des deux comptes (requêtes `curl` avec les deux tokens) + code du contrôle d'accès + réponse `409` sur email dupliqué.

### Jour 11 — Tests fonctionnels
- PHPUnit, `WebTestCase` avec requêtes JSON (`$client->request(..., content: json_encode(...))`), base de données de test séparée, assertions sur le code de statut et le contenu JSON de la réponse.
- Défi : au moins deux tests fonctionnels qui passent (ex. `GET /api/books` répond `200` et contient un titre connu ; un accès non autorisé à l'édition du livre d'un autre utilisateur répond `403`).
- Branche : `feature/tests-fonctionnels`.
- Livrable : sortie de `php bin/phpunit` verte.

### Jour 12 — Pagination et filtres avancés
- Pagination sur `GET /api/books` (`setMaxResults`/`setFirstResult`, métadonnées `total`/`page`/`limit` dans la réponse JSON), filtres combinables par query params (`?author=...&category=...`), tri (`?sort=...`).
- Le Jour 11 n'a couvert que des tests fonctionnels (`WebTestCase`, bout en bout via HTTP) : la logique de filtrage/tri dans le repository (query builder) est un bon candidat pour un premier test unitaire isolé, sans passer par une requête HTTP complète.
- Défi : réponse paginée avec métadonnées visibles, filtre combiné (auteur + catégorie) testé au `curl`.
- Branche : `feature/pagination-filtres-api`.
- Bonus : comparer en 2-3 phrases ce qu'apporterait API Platform (pagination/filtres/sérialisation générés automatiquement à partir des attributs de l'entité) par rapport à ce que tu viens d'écrire à la main — pour comprendre pourquoi/quand l'utiliser sur un vrai projet.

**Fin de Phase 4** : créer `release/0.3.0` depuis `develop`, relire l'intégration sécurité/tests/API, merger dans `main` et `develop`, taguer `v0.3.0`.

---

## Phase 5 — Défi final (Jours 13-14)

Un scénario complet, enchaîné sans étapes détaillées cette fois — à toi de dérouler la bonne architecture :

> Complète l'API de la bibliothèque personnelle : une recherche texte libre sur les livres (titre/description, en plus des filtres du Jour 12), un endpoint `GET /api/me/books` listant uniquement les livres de l'utilisateur authentifié, les tests fonctionnels du Jour 11 étendus pour couvrir ces nouvelles routes, et un `README.md` à jour documentant l'API (endpoints disponibles, exemples de requêtes `curl`, comment installer et lancer le projet).

- Livrable : le projet complet (repo GitHub), une démonstration des endpoints au `curl`/Postman, un court résumé de tes choix (pourquoi telle requête Doctrine plutôt qu'une autre, pourquoi tel contrôle d'accès).
- Découpage libre en plusieurs `feature/*` nommées selon les fonctionnalités livrées, c'est aussi ça l'exercice : savoir décider soi-même où couper.
- Revue complète comme si c'était une review de code réelle.
- Clôture : `release/1.0.0` depuis `develop`, merge dans `main` et `develop`, tag `v1.0.0`.

---

## Journal de progression

- **2026-08-27** — Jour 1 validé. Squelette Symfony généré (sans `--webapp`) dans le dépôt existant en le construisant à part dans `/tmp` puis en le rapatriant à la racine, `main`/`develop` déjà en place ont été conservés sans conflit avec les fichiers de doc. SQLite configuré via `.env.local`, serveur local démarré et répond (404 attendu, aucune route déclarée). Commit `feat: initialise le squelette Symfony minimal` poussé directement sur `develop` (pas de `feature/*` pour ce jour de mise en place).
- **2026-08-27** — Jour 2 validé. `symfony/maker-bundle` installé en dev pour générer le squelette de `ApiController` (`make:controller`), puis logique écrite à la main : `GET /api/ping` et `GET /api/hello/{name}`. Plusieurs allers-retours de correction en cours de route (paramètre de route non lié à la méthode, interpolation de chaîne invalide avec des backticks au lieu de guillemets doubles, champ `path` de démo à retirer). `debug:router` et les réponses `curl` confirmés. PR #1 mergée (`--merge`, pas de squash) dans `develop`, branche `feature/routes-api-de-base` supprimée.
- **2026-08-28** — Jour 3 validé. `symfony/serializer` et `symfony/property-access` installés. Classe `Book` créée dans `src/Entity/` (namespace oublié puis corrigé, constructeur d'abord sans paramètres puis corrigé pour accepter titre/description en argument). `SerializerInterface` injecté dans `ApiController` (import manquant, corrigé), `GET /api/books` sérialise un tableau de `Book` codés en dur. Piège du double encodage JSON rencontré et corrigé (`serialize()` renvoie déjà une chaîne JSON, `new JsonResponse($data, json: true)` nécessaire pour éviter le ré-encodage) — repéré via `curl` avant correction. Cas du tableau vide testé et confirmé (`[]` propre). PR #2 mergée (`--merge`) dans `develop`, branche `feature/serialisation-livres` supprimée.
