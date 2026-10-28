# CI, dépendances et sécurité de la chaîne d'intégration

Ce guide décrit ce que le dépôt contient pour la maintenance des dépendances, la CI et
les analyses de sécurité, et les réglages qui restent **à faire à la main dans
GitHub**. Les fichiers du dépôt préparent les protections ; elles ne sont pas actives
tant que les réglages de la section 4 n'ont pas été faits.

## 1. Contexte du projet

| Élément | Valeur constatée |
| --- | --- |
| Branche principale | `prod` (branche par défaut, déployée par `02-deploy.yml`). `develop` existe aussi. |
| PHP / Symfony | PHP `>=8.4` (CI 8.4), Symfony `7.4.*` (`extra.symfony.require`), Doctrine ORM 3, EasyAdmin 4 |
| JavaScript | **npm** (`package-lock.json` v3), Node 24 (déploiement et CI) |
| Front | Vue 3.5 (`vue-loader` 17), Webpack 5 via **Webpack Encore 6**, Stimulus/Turbo, Tailwind 4 |
| Particularité | `@symfony/ux-*` est installé depuis `file:vendor/symfony/ux-*/assets` : il faut `composer install` **avant** `npm ci`. |
| Base de test | MySQL `8.0.33` (même version que `compose.yaml` et `serverVersion` de `DATABASE_URL`) |
| Docker | `compose.yaml` / `compose.override.yaml` pour le dev local uniquement (MySQL, Mailpit). **Aucun Dockerfile** : aucune image n'est construite. |

## 2. Ce qui est installé dans le dépôt

| Fichier | Rôle |
| --- | --- |
| `.github/dependabot.yml` | Mises à jour de versions : Composer, npm, GitHub Actions, outils CI (pip). |
| `.github/workflows/01-code-quality.yml` (« CI ») | Qualité PHP, tests PHPUnit sur MySQL, lint + build Encore, lint des workflows. |
| `.github/workflows/security-audit.yml` | `composer audit` et `npm audit`, sur PR/push et **chaque jour**. |
| `.github/workflows/dependency-review.yml` | Bloque les PR qui ajoutent une dépendance vulnérable. |
| `.github/workflows/codeql.yml` + `.github/codeql/codeql-config.yml` | CodeQL JavaScript/Vue et GitHub Actions, sur PR/push et chaque lundi. |
| `.github/ci-tools/zizmor/requirements.{in,txt}` | Version de zizmor épinglée avec empreintes (pip `--require-hashes`). |
| `.github/workflows/02-deploy.yml` | Durci : actions épinglées, `permissions: contents: read`, `persist-credentials: false`, timeout, environment `production`. |

Le workflow de démonstration `test.yml` a été supprimé : il s'exécutait sur chaque push
avec une action non épinglée et ne testait rien.

### Durcissement appliqué à tous les workflows

- Actions épinglées par SHA de commit complet (vérifié via l'API GitHub et `git ls-remote`),
  avec la version en commentaire ; Dependabot (`github-actions`) met à jour le SHA et
  le commentaire.
- `permissions: {}` au niveau workflow, droits accordés par job (`contents: read`,
  plus `security-events: write` pour CodeQL uniquement).
- `persist-credentials: false` sur tous les checkouts, `timeout-minutes` sur tous les
  jobs, `concurrency` (annulation des runs obsolètes sur les PR uniquement ; jamais
  pour le déploiement).
- Pas de `pull_request_target` ni `workflow_run`, aucun secret dans les jobs de
  validation, aucune donnée de l'événement interpolée dans un `run:`, aucun
  commentaire publié sur les PR.
- Pas de filtre `paths:` sur les déclencheurs : un check requis ne reste jamais
  « en attente ».
- `actionlint` (avec shellcheck) et `zizmor --offline` vérifient les workflows en CI.

## 3. Checks et politique de blocage

### Noms des checks

Noms exacts à sélectionner dans le ruleset (ils n'apparaissent dans la liste qu'après
une première exécution sur GitHub) :

| Check | Workflow | Ce qui le fait échouer | État au 2026-10-25 (exécution locale) |
| --- | --- | --- | --- |
| `PHP quality` | CI | `composer validate --strict`, install depuis le lock, PHPCS PSR-12, PHP CS Fixer, PHPStan niv. 6, PHPMD, Twig CS Fixer, `lint:container`, `lint:twig`, `lint:yaml` | ✅ vert |
| `PHP tests (MySQL)` | CI | création BDD de test, migrations, `doctrine:schema:validate`, PHPUnit | ✅ vert (corrigé, voir 5) |
| `JS lint & build` | CI | `npm ci`, ESLint sans `--fix` (erreurs bloquantes, warnings non), `encore production`, présence de `manifest.json`, `entrypoints.json`, `service-worker.js` | ✅ vert (6 warnings ESLint) |
| `Workflow lint (actionlint, zizmor)` | CI | erreurs actionlint/shellcheck, findings zizmor (persona par défaut) | ✅ vert |
| `Composer audit` | Security audit | toute advisory sur `composer.lock`, tout paquet abandonné | ✅ vert (corrigé, voir 5) |
| `npm audit` | Security audit | advisory de sévérité ≥ high dans `package-lock.json` | ✅ vert (8 moderate restants, sous le seuil, voir 5) |
| `Dependency review` | Dependency review | dépendance ajoutée/modifiée par la PR avec une vulnérabilité ≥ moderate | s'exécute sur PR uniquement |
| `CodeQL (javascript-typescript)`, `CodeQL (actions)` | CodeQL | échec de l'analyse (pas les alertes, voir plus bas) | non exécuté localement |

Les jobs du déploiement (`Deploy to Production`) ne sont pas des checks de PR.

### Politique de sévérité

- **Composer** : `composer audit` n'a pas de seuil de sévérité ; toute advisory bloque.
  Exception possible uniquement via `config.audit.ignore` de `composer.json` avec l'ID
  de l'advisory, une justification et une date de revue en commentaire de PR.
- **npm** : `--audit-level=high` sur tout l'arbre, **devDependencies comprises** : `vue`,
  `@hotwired/*`, etc. sont déclarés en `devDependencies` mais embarqués dans
  `public/build`, donc `--omit=dev` donnerait une fausse image. Les « moderate » restent
  visibles dans les alertes Dependabot sans bloquer.
- **Dependency review** : `moderate` et plus, car seules les dépendances introduites par
  la PR sont évaluées. Aucune exception (`allow-ghsas`) n'est configurée.
- **CodeQL** : un check CodeQL vert signifie seulement que l'analyse a abouti, **pas
  qu'il n'y a pas d'alerte**. Pour bloquer les alertes, utiliser la règle de ruleset
  « Require code scanning results » (section 4.5).

### Couverture réelle des scanners

- **Le code PHP n'est analysé par aucun scanner de sécurité.** CodeQL ne prend pas
  en charge PHP, et Semgrep CE a été retiré (choix du mainteneur). Le PHP reste couvert
  par PHPStan (niveau 6), les lints et les tests, qui ne cherchent pas de failles.
- **CodeQL** analyse le JavaScript (`assets/`,
  `webpack.config.js`, etc.) et les workflows GitHub Actions. `assets/vendor/`
  (Stimulus/Turbo vendorés) et les répertoires générés sont exclus.
- **Fichiers `.vue`** (vérifié avec CodeQL CLI 2.27.3, `codeql database create
  --build-mode=none`) : les 19 fichiers `.vue` sont extraits ; les blocs `<script>` sont
  analysés, ainsi que 56 expressions de template, ce qui correspond à peu près aux 58
  interpolations `{{ }}`. Les directives (`:attr`, `v-model`, `@event`, 162 occurrences)
  ne sont **pas** extraites de façon fiable : leur logique n'est pas entièrement
  couverte. Aucun `v-html`/`innerHTML` n'est présent dans `assets/` aujourd'hui.
- **Trivy** : non ajouté, le projet ne construit aucune image Docker (pas de
  Dockerfile ; le déploiement se fait par rsync + script SSH). Si une image de
  production est ajoutée un jour : scanner l'image finale construite dans le même job,
  bloquer HIGH/CRITICAL, exceptions dans `.trivyignore.yaml` avec `expired_at` et
  justification, sans `--ignore-unfixed` global.
- **Dependabot Docker** : non activé. Les images de `compose.yaml` servent au dev local
  et MySQL doit rester aligné sur la production (`8.0.33`), comme l'image de service
  de la CI (épinglée par digest). Les mettre à jour à la main avec la production.

## 4. Réglages à faire dans GitHub

Rien de ce qui suit n'a été modifié à distance.

### 4.1 Sécurité du dépôt (Dependabot, secrets)

**Settings → Advanced Security** (« Code security » sur les interfaces plus anciennes) :

1. **Dependency graph** : activé (par défaut sur un dépôt public ; requis par
   Dependency review et Dependabot).
2. **Dependabot alerts** : *Enable*.
3. **Dependabot security updates** : *Enable*. Ces PR sont créées dès qu'une alerte est
   publiée, ne suivent ni le calendrier du lundi ni le `cooldown`, et ne sont pas
   regroupées (laisser **Grouped security updates** désactivé pour garder une PR par
   correctif).
4. **Dependabot version updates** : piloté par `.github/dependabot.yml`, rien à cocher.
5. **Secret scanning** et **Push protection** : *Enable* (gratuit sur un dépôt public).
6. **Private vulnerability reporting** : recommandé.

À vérifier : 4 exécutions « Dependabot Updates » ont échoué le 2026-10-24 (journaux
réservés aux administrateurs). Ouvrir **Insights → Dependency graph → Dependabot** pour
la cause ; une piste possible : les dépendances npm `file:vendor/…`, `vendor/` n'étant
pas versionné.

### 4.2 Code scanning (CodeQL)

**Settings → Advanced Security → Code scanning** : garder la configuration
**avancée** (workflow `codeql.yml`). **Ne pas activer « Default setup »** : il est
incompatible avec un workflow CodeQL avancé, dont l'envoi de résultats échoue alors.

### 4.3 Permissions GitHub Actions

**Settings → Actions → General** :

1. **Actions permissions** : *Allow Florent-V, and select non-Florent-V, actions and
   reusable workflows* → cocher *Allow actions created by GitHub* et ajouter :
   `shivammathur/setup-php@*, webfactory/ssh-agent@*`
   (`github/codeql-action` et `actions/*` sont couverts par « created by GitHub »).
2. Cocher **Require actions to be pinned to a full-length commit SHA** si l'option est
   proposée.
3. **Approval for running fork pull request workflows** : *Require approval for all
   external contributors*.
4. **Workflow permissions** : *Read repository contents and packages permissions* ;
   **décocher** *Allow GitHub Actions to create and approve pull requests*.

**Settings → Environments → production** (créé au premier déploiement, ou à créer) :
*Deployment branches and tags* → *Selected branches* → `prod`. Y déplacer les secrets
`SSH_PRIVATE_KEY`, `SSH_HOST`, `SSH_PORT`, `SSH_USERNAME`, `DEPLOY_PATH`, puis les
supprimer des secrets du dépôt.

Reste à faire, non modifié ici parce que cela demande un nouveau secret : le
déploiement fait `ssh-keyscan` pendant le job, ce qui accepte n'importe quelle clé
d'hôte. Remplacer par un secret d'environnement `SSH_KNOWN_HOSTS` (contenu vérifié de
`~/.ssh/known_hosts` pour le serveur), écrit dans `~/.ssh/known_hosts` à la place de
l'étape « Add server to known hosts ».

### 4.4 Modes de fusion

**Settings → General → Pull Requests** : garder uniquement **Allow squash merging**
(l'historique de `prod` est déjà composé de squash `(#NN)`), décocher *merge commits*
et *rebase merging*, cocher **Always suggest updating pull request branches** et
**Automatically delete head branches**. Ne pas activer l'auto-merge de façon générale.

### 4.5 Ruleset de la branche `prod`

**Settings → Rules → Rulesets → New ruleset → New branch ruleset** :

- **Ruleset name** : `prod` ; **Enforcement status** : *Active*.
- **Bypass list** : vide (ni administrateurs, ni Dependabot). En cas d'urgence,
  désactiver temporairement le ruleset, ce qui reste tracé.
- **Target branches** : *Add target → Include default branch*.
- Règles :
  - ☑ **Restrict deletions**
  - ☑ **Require linear history**
  - ☑ **Require a pull request before merging**
    - *Required approvals* : voir ci-dessous
    - ☑ *Dismiss stale pull request approvals when new commits are pushed*
    - ☑ *Require conversation resolution before merging*
    - *Allowed merge methods* : **Squash**
  - ☑ **Require status checks to pass**
    - ☑ *Require branches to be up to date before merging*
    - *Add checks* (source *GitHub Actions*), **après une première exécution** :
      `PHP quality`, `JS lint & build`, `Workflow lint (actionlint, zizmor)`,
      `Dependency review`, `CodeQL (javascript-typescript)`, `CodeQL (actions)`,
      `PHP tests (MySQL)`, `Composer audit`, `npm audit`.
    - `Composer audit` et `npm audit` peuvent repasser au rouge sans changement de
      code (nouvelle advisory) et bloquer alors toutes les PR : corriger en priorité
      (section 7), ou retirer temporairement le check du ruleset en le notant.
  - ☑ **Block force pushes**
  - ☑ **Require code scanning results** : outil `CodeQL`, *Security alerts* :
    **High or higher**, *Alerts* : **Errors**.
- **Merge queue** : ne pas l'activer. Les workflows ne déclarent pas l'événement
  `merge_group`, les checks requis resteraient en attente.

Un ruleset équivalent, éventuellement plus souple, peut cibler `develop`.

**Mainteneur seul (situation actuelle)** : *Required approvals* = **0**. GitHub
interdit d'approuver sa propre PR ; une approbation obligatoire rendrait toute fusion
impossible sans contournement. Pas de fichier `CODEOWNERS` (il n'apporterait que des
demandes de revue automatiques) ; ne pas cocher *Require review from Code Owners*. La
PR reste obligatoire : c'est ce qui force le passage des checks.

**Plusieurs mainteneurs** : *Required approvals* ≥ **1**, ☑ *Require review from Code
Owners*, ☑ *Require approval of the most recent reviewable push*. Créer alors
`.github/CODEOWNERS` avec les mainteneurs (ou une équipe), au moins sur `/.github/`, les
manifestes de dépendances, `config/` et `src/Security/`.

## 5. Problèmes préexistants révélés

Ils ne venaient pas de cette mise en place. Les trois premiers ont été corrigés dans la
PR #65 pour que les checks correspondants puissent être rendus obligatoires.

| Problème | Correction |
| --- | --- |
| `composer audit` : 23 advisories sur 8 paquets (`easycorp/easyadmin-bundle`, `league/commonmark`, `squizlabs/php_codesniffer`, `symfony/ux-live-component`, `symfony/ux-icons`, `symfony/http-foundation`, `symfony/yaml`, `symfony/expression-language`) | `composer update … --with-dependencies` ciblé, Symfony reste en 7.4.*, `symfony/ux-*` montés ensemble en 2.36 ; contraintes de `composer.json` relevées par `bump-after-update` ; `config/reference.php` régénéré |
| `npm audit` : 38 vulnérabilités (3 critical, 19 high), dont `vue`, `postcss`, `webpack-dev-server`/`websocket-driver` | `npm audit fix` (sans `--force`) ; `copy-webpack-plugin` monté en 14 (seul changement cassant : Node ≥ 20.9) pour `serialize-javascript` (high). **Restent 8 moderate** (`uuid` via `webpack-notifier`/`sockjs`, `postcss-selector-parser` via `@tailwindcss/typography`) : le seul « correctif » proposé est une rétrogradation, ils sont sous le seuil `high` et à suivre via Dependabot |
| PHPUnit : 2 tests en échec (`user_name` NOT NULL non renseigné, textes attendus en anglais alors que l'interface est en français, `MAILER_DSN` SMTP en test) ; `phpunit.xml.dist` au format PHPUnit 9, ce qui fait sortir PHPUnit 12 en code 1 | tests alignés sur l'interface réelle, nettoyage des `ResetPasswordRequest` avant les utilisateurs, `MAILER_DSN=null://null` dans `.env.test`, `phpunit.xml.dist` migré (`<source>`, suppression du listener et de l'attribut obsolètes) |
| PHPUnit en CI : erreur 500 sur les pages rendues, `public/build/entrypoints.json` absent (le job PHP ne construit pas les assets ; en local le build existait) | `webpack_encore.strict_mode: false` sous `when@test` (option prévue par la recette, commentée) ; sans effet en dev/prod |

Restent **non corrigés** (hors périmètre) :

| Problème | Impact | Piste |
| --- | --- | --- |
| Redirection ouverte dans `src/Controller/LocaleController.php:32` : `str_starts_with($referer, 'https://' . $host)` accepte `https://<host>.attaquant.tld/…` (relevé par Semgrep avant son retrait) | aucun check, faille de faible gravité | comparer l'hôte de l'URL analysée (`parse_url`) ou rediriger vers une route interne |
| `src/Security/UserChecker.php` appelle la route `app_verify_email`, qui n'existe plus : un utilisateur non vérifié qui se connecte obtient une erreur 500 au lieu du mail d'activation. `LoginControllerTest` utilise donc un utilisateur vérifié | aucun check rouge, bug applicatif | recréer la route de vérification ou retirer l'envoi |

Remarque : MySQL 8.0 (production, `compose.yaml`, CI) est en fin de support ;
planifier la montée de version avec la production.

## 6. Procédures

### Traiter une alerte (Dependabot, Code scanning, audit planifié rouge)

1. Ouvrir l'alerte (**Security → Dependabot / Code scanning**) ou le run rouge
   « Security audit ».
2. Qualifier : la version vulnérable est-elle dans `composer.lock`/`package-lock.json`,
   le code vulnérable est-il atteignable (serveur, bundle navigateur, outil de build
   seulement) ?
3. Corriger : fusionner la PR de sécurité Dependabot, ou PR manuelle
   (`composer update vendor/paquet --with-dependencies`, `npm update paquet` /
   `npm audit fix`). Ne jamais utiliser `npm audit fix --force` sans relire les
   montées majeures.
4. Si aucun correctif n'existe : exception explicite et limitée (Composer
   `config.audit.ignore` avec l'ID ; *Dismiss* de l'alerte avec raison), avec une date
   de revue dans la PR. Pas de baisse du seuil global.
5. Déployer, puis vérifier que l'alerte se ferme.

### Traiter une PR de dépendances

1. Lire le titre/groupe : `symfony`, `symfony-ux`, `symfony-ai` (0.x, attention aux
   ruptures), `doctrine`, `vue`, `webpack-encore`… Une PR **majeure** arrive seule (ou
   dans `symfony-major`) : lire le guide de migration avant tout.
2. Attendre tous les checks. Pour `symfony/ux-*`, si `JS lint & build` échoue sur
   `npm ci`, récupérer la branche, `composer install && npm install`, committer
   `package-lock.json`.
3. Tester localement si la PR touche Vue/Encore (`npm run build`, pages Vue) ou
   Doctrine (migrations).
4. Fusionner en squash. Pas d'auto-merge généralisé ; Dependabot ne contourne pas le
   ruleset.

## 7. Maintenance des épinglages

| Élément | Où | Mise à jour |
| --- | --- | --- |
| Actions (SHA) | workflows | Dependabot `github-actions` |
| zizmor | `.github/ci-tools/zizmor/requirements.txt` | Dependabot `pip` ; à la main : `uvx --python 3.12 --from pip-tools pip-compile --generate-hashes --allow-unsafe --strip-extras --no-emit-index-url --output-file requirements.txt requirements.in` |
| actionlint | `ACTIONLINT_VERSION` (CI) | à la main (module Go, vérifié par `sum.golang.org`) |
| MySQL de test | `services.mysql.image` (digest) | à la main, avec la production |

## 8. Coûts et limites

- **Dépôt public (situation actuelle)** : minutes Actions des runners standard,
  CodeQL/Code scanning, Dependency review, Dependabot et Secret scanning sont gratuits.
  Aucun service payant n'est utilisé ; zizmor tourne localement sans compte.
- Les workflows planifiés d'un dépôt public sont **désactivés par GitHub après 60 jours
  sans activité** : les réactiver dans l'onglet Actions si besoin.
- **Si le dépôt devient privé** (forfait Free/Pro, sans GitHub Code Security ni Secret
  Protection, qui sont payants) :

  | Outil | En privé |
  | --- | --- |
  | Dependabot (alertes, security updates, version updates), Dependency graph | ✅ gratuit |
  | Jobs CI, `composer audit`, `npm audit`, actionlint, zizmor | ✅ fonctionnent, mais consomment le quota de minutes Actions (limité par mois) |
  | CodeQL (`codeql.yml`) | ❌ Code scanning payant, et la licence de CodeQL CLI interdit l'analyse de code non open source sans licence : supprimer le workflow |
  | Dependency review | ❌ payant : supprimer le workflow ; `npm audit`/`composer audit` restent |
  | Règle « Require code scanning results » | ❌ payant |
  | Rulesets / protection de branche | ❌ non appliqués avec GitHub Free ; ✅ avec GitHub Pro |
  | Secret scanning (dépôt) | ❌ payant ; alternative locale : un hook pre-commit comme gitleaks |

  Retirer des checks requis tout ce qui est supprimé, sinon les PR restent bloquées.
  Les workflows planifiés ne sont pas désactivés après 60 jours en privé.

## 9. Reproduire localement

```bash
composer validate --strict --no-check-publish && composer audit --locked
make lint && vendor/bin/phpstan analyse src --level 6 --configuration=phpstan.dist.neon
npm ci && npx eslint "assets/**/*.{js,vue}" && npm run build && npm audit --audit-level=high
# Tests : MySQL de test (ex. `make up`), puis
APP_ENV=test php bin/console doctrine:database:create --if-not-exists
APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction
APP_ENV=test php bin/console doctrine:schema:validate && php bin/phpunit
```

Références : [options Dependabot](https://docs.github.com/en/code-security/reference/supply-chain-security/dependabot-options-reference),
[règles des rulesets](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/available-rules-for-rulesets),
[langages CodeQL](https://codeql.github.com/docs/codeql-overview/supported-languages-and-frameworks/),
[Dependency review](https://docs.github.com/en/code-security/concepts/supply-chain-security/dependency-review).
