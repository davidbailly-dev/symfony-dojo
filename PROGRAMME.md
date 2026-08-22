# Programme d'apprentissage Symfony

**Profil** : PHP connu et pratiqué, découverte du framework Symfony · rythme intensif (quasi quotidien) · suivi via ce document, mis à jour au fil des sessions.

**Environnement** : projet Symfony 7 (skeleton `--webapp`), PHP 8.2+, Composer, Symfony CLI, base SQLite en local, Doctrine ORM, Twig. Dépôt Git relié à GitHub, en Gitflow dès le Jour 1. Chaque jour = un défi concret avec des critères de réussite vérifiables (rendu dans le navigateur, sortie de commande, tests qui passent, lien de la PR).

**Comment ça marche** : tu fais le défi du jour en local, Claude Code corrige, commente ce qui aurait pu être fait autrement, et coche l'étape ici. Rien n'est chronométré à la seconde près — l'idée est d'enchaîner les jours sans trop de blancs pour que ça reste engageant.

**Mini-projet fil rouge** : une bibliothèque personnelle. Un utilisateur peut s'inscrire, ajouter des livres (titre, description, date de publication, auteur, catégories), les modifier ou les supprimer, et ne peut gérer que ses propres livres. Le projet se construit couche par couche : d'abord des pages statiques, puis une vraie persistance en base, puis des formulaires, puis l'authentification, puis une API et des tests.

---

## Phase 0 — Mise en place (Jour 1)

Objectif : un projet Symfony qui tourne, structure comprise, dépôt Git en Gitflow dès le départ.

- [ ] Vérifier PHP (8.2+) et Composer installés (`php -v`, `composer -V`).
- [ ] Installer le Symfony CLI si absent (`symfony check:requirements`).
- [ ] Créer le projet avec `symfony new . --webapp` (dans le dossier `symfony-dojo` déjà présent) — le pack `webapp` embarque Twig, Doctrine, Forms, Security, Mailer.
- [ ] Configurer `DATABASE_URL` dans `.env.local` pour utiliser SQLite (fichier dans `var/`), pas de Docker nécessaire pour ce dojo.
- [ ] Lancer `symfony server:start` (ou `symfony serve -d`) et vérifier la page d'accueil Symfony par défaut.
- [ ] Initialiser Git si besoin, créer le repo GitHub `symfony-dojo`, relier, `.gitignore` Symfony standard (`vendor/`, `var/`, `.env.local`), premier commit "Initial commit" sur `main`.
- [ ] Créer `develop` à partir de `main`, la pousser, la définir comme branche par défaut pour les PR.
- [ ] Explorer la structure générée : `src/`, `config/`, `templates/`, `public/index.php`, `.env` — comprendre à quoi sert chaque dossier avant d'écrire du code.

**Défi du jour** : me montrer la page d'accueil Symfony dans le navigateur (ou description), plus `git log --graph --all --decorate` montrant `main` et `develop`.

---

## Phase 1 — Fondamentaux (Jours 2 à 4)

### Jour 2 — Routing et contrôleurs
- Route par attribut PHP (`#[Route('/chemin', name: 'app_xxx')]`), paramètres de route (`/hello/{name}`), objet `Response`.
- `bin/console debug:router` pour lister les routes déclarées.
- Défi : une page d'accueil personnalisée (pas celle par défaut) + une route dynamique `/hello/{name}` qui affiche un texte incluant le paramètre.
- Branche : `feature/routes-de-base`.
- Livrable : code des deux contrôleurs + sortie de `debug:router`.

### Jour 3 — Twig
- Layout de base (`base.html.twig`) étendu par les autres templates (`extends`, `block`), variables passées depuis le contrôleur vers la vue, filtres (`|date`, `|upper`, `|length`...).
- `for` et `if` dans un template.
- Défi : une page listant des livres (tableau PHP codé en dur dans le contrôleur pour l'instant) via une boucle `for`, avec un message "aucun livre" affiché via `if` quand la liste est vide.
- Branche : `feature/page-liste-livres`.
- Livrable : capture avec liste pleine, puis vidée dans le code pour montrer le message conditionnel.

### Jour 4 — Doctrine et première entité
- `make:entity Author` (nom), comprendre le mapping par attributs (`#[ORM\Entity]`, `#[ORM\Column]`), générer et exécuter une migration (`make:migration` ou `doctrine:migrations:diff` puis `doctrine:migrations:migrate`).
- Comprendre le rôle de l'`EntityManager` et d'un `Repository`.
- Défi : la liste du Jour 3 vient maintenant de la base de données (quelques auteurs insérés à la main) via `AuthorRepository`, plus de tableau en dur.
- Branche : `feature/persistance-auteurs`.
- Livrable : code du contrôleur modifié + preuve que les données viennent bien de la base (ex. ajout d'un auteur en base, rafraîchi sans toucher au code).

**Fin de Phase 1** : créer `release/0.1.0` depuis `develop`, relire l'ensemble du code des jours 2-4, merger dans `main` et `develop`, taguer `v0.1.0`.

---

## Phase 2 — Comprendre l'architecture Symfony (Jour 5)

Objectif : théorie avant pratique, pour ne pas empiler du code Symfony sans comprendre le modèle sous-jacent — surtout venant de PHP "classique".

- Cycle requête/réponse : front controller (`public/index.php`), Kernel, Router, Controller, Response — et comment ça diffère d'un script PHP exécuté directement par le serveur.
- Bundles : ce qu'ils sont, pourquoi Symfony est construit comme un assemblage de bundles plutôt qu'un monolithe.
- Conteneur de services et injection de dépendances : pourquoi un contrôleur ou un service reçoit ses dépendances via le constructeur plutôt que de faire `new MonService()` ou d'aller chercher une instance globale.
- Environnements (`dev`/`prod`/`test`), fichiers `.env` et `config/packages/` : comment la configuration change selon l'environnement.

**Défi du jour** : m'expliquer avec tes mots (2-3 phrases) pourquoi Symfony utilise l'injection de dépendances plutôt que d'instancier les classes à la volée, avec un exemple concret (ex. injecter l'`EntityManager` ou un logger dans un contrôleur) plutôt qu'une explication abstraite.

---

## Phase 3 — CRUD et relations, à la main (Jours 6 à 9)

On construit le CRUD complet à la main (formulaires, contrôleur, validation), sans utiliser `make:crud` — l'idée est de comprendre chaque brique avant de laisser un générateur les assembler. `make:entity` et `make:controller` restent autorisés pour le squelette vide (ils ne génèrent pas de logique).

### Jour 6 — Entité Book et relation ManyToOne
- `make:entity Book` (titre, description, date de publication) + relation `ManyToOne` vers `Author`.
- Installer `DoctrineFixturesBundle`, écrire une fixture qui peuple quelques auteurs et livres factices.
- Défi : `doctrine:fixtures:load` fonctionne, une page affiche les livres avec le nom de leur auteur (relation traversée depuis le template ou le repository).
- Branche : `feature/entite-book-et-fixtures`.
- Livrable : code de l'entité, de la fixture, et de la requête utilisée pour afficher les livres.

### Jour 7 — Formulaire de création
- `BookType` (Symfony Forms), gestion à la main dans le contrôleur (`createForm`, `handleRequest`, `isSubmitted() && isValid()`), `persist` + `flush`.
- Défi : formulaire de création d'un livre fonctionnel, avec redirection après succès et message flash.
- Branche : `feature/formulaire-creation-livre`.
- Livrable : code du `FormType` + contrôleur + démonstration d'un livre créé via le formulaire.

### Jour 8 — CRUD complet et validation
- Actions `show`, `edit`, `delete` (delete protégé par token CSRF), contraintes de validation sur l'entité (`Assert\NotBlank`, `Assert\Length`...), affichage des erreurs dans le template.
- Défi : CRUD `Book` complet (create/read/update/delete) validé de bout en bout, avec un message d'erreur visible si on soumet un formulaire invalide.
- Branche : `feature/crud-livre-complet`.
- Livrable : démonstration des 4 actions + capture d'une erreur de validation affichée.

### Jour 9 — Relation ManyToMany
- Entité `Category`, relation `ManyToMany` avec `Book`, champ formulaire multiple (`EntityType` avec `multiple: true`).
- Défi : un livre peut avoir plusieurs catégories, assignables et modifiables depuis le formulaire, affichées sur la page de détail.
- Branche : `feature/categories-livres`.
- Livrable : capture d'un livre avec plusieurs catégories + code du formulaire modifié.

**Fin de Phase 3** : créer `release/0.2.0` depuis `develop`, relire l'ensemble du CRUD produit, merger dans `main` et `develop`, taguer `v0.2.0`.

---

## Phase 4 — Écosystème et outillage (Jours 10 à 12)

### Jour 10 — Sécurité et propriété des données
- `make:user`, formulaire d'inscription et de connexion, hashage des mots de passe.
- Relation `Book` → `User` (propriétaire), contrôle d'accès : seul le propriétaire peut éditer/supprimer son livre (voter ou vérification explicite dans le contrôleur).
- Défi : inscription et connexion fonctionnelles, et preuve qu'un utilisateur ne peut pas éditer le livre d'un autre (testé avec deux comptes).
- Branche : `feature/authentification-et-proprietaire`.
- Livrable : démonstration des deux comptes + code du contrôle d'accès.

### Jour 11 — Tests fonctionnels
- PHPUnit, `WebTestCase`, base de données de test séparée.
- Défi : au moins deux tests fonctionnels qui passent (ex. la liste des livres répond 200 et contient un titre connu ; un accès non autorisé à l'édition d'un livre d'autrui répond 403).
- Branche : `feature/tests-fonctionnels`.
- Livrable : sortie de `php bin/phpunit` verte.

### Jour 12 — API JSON
- Endpoint `/api/books` retournant du JSON (sérialisation avec le composant Serializer), pagination simple (`setMaxResults`/`setFirstResult`).
- Défi : endpoint consommable (testé avec `curl` ou Postman), pagination visible dans la réponse.
- Branche : `feature/api-books-json`.
- Bonus : filtrer par auteur ou catégorie via un paramètre de requête (`?author=...`).

**Fin de Phase 4** : créer `release/0.3.0` depuis `develop`, relire l'intégration sécurité/tests/API, merger dans `main` et `develop`, taguer `v0.3.0`.

---

## Phase 5 — Défi final (Jours 13-14)

Un scénario complet, enchaîné sans étapes détaillées cette fois — à toi de dérouler la bonne architecture :

> Complète la bibliothèque personnelle : une page de recherche/filtre des livres (par titre, auteur, catégorie), une page de profil utilisateur listant uniquement ses propres livres, les tests fonctionnels du Jour 11 étendus pour couvrir ces nouvelles routes, et un `README.md` à jour expliquant comment installer et lancer le projet.

- Livrable : le projet complet (repo GitHub), une démonstration du rendu, un court résumé de tes choix (pourquoi telle requête Doctrine plutôt qu'une autre, pourquoi tel contrôle d'accès).
- Découpage libre en plusieurs `feature/*` nommées selon les fonctionnalités livrées, c'est aussi ça l'exercice : savoir décider soi-même où couper.
- Revue complète comme si c'était une review de code réelle.
- Clôture : `release/1.0.0` depuis `develop`, merge dans `main` et `develop`, tag `v1.0.0`.

---

## Journal de progression

*(mis à jour au fil des sessions Claude Code — vide pour l'instant, le programme n'a pas encore démarré)*
