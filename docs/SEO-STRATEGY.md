# SEO Strategy — Portfolio Florent Vasseur

## Contexte

Portfolio Symfony bilinguë (FR/EN), locale par défaut : FR.
Pages publiques : Home (hero/about/expérience/skills/contact), `/projects/`, `/projects/{id}`, `/articles/`, `/articles/{slug}`.

---

## Audit — État actuel (Juin 2026)

| Problème | Sévérité | Impact |
|---|---|---|
| Meta description hardcodée "Description de votre application PWA" sur toutes les pages | 🔴 Critique | Snippet Google = texte générique → taux de clic nul |
| `<html>` sans attribut `lang` | 🔴 Critique | Google ne sait pas la langue du contenu |
| Aucun tag Open Graph ni Twitter Card | 🔴 Critique | Partages LinkedIn/Twitter = lien sans aperçu |
| Aucun `canonical` URL | 🔴 Critique | `/projects/{id}` + `/projects/{slug}` = contenu dupliqué |
| Pas de `robots.txt` | 🔴 Critique | Google peut crawler /admin, /login, pages inutiles |
| Pas de `sitemap.xml` | 🔴 Critique | Google ne découvre pas les articles et projets |
| URLs projets basées sur ID (`/projects/5`) | 🔴 Critique | 0 keyword dans l'URL, pas mémorisable |
| Aucun JSON-LD structured data | 🟠 Haute | Pas de rich snippets dans les résultats |
| Pas de hreflang (site bilingue FR/EN) | 🟠 Haute | Google affiche mauvaise langue selon pays |
| Pas de Breadcrumb schema | 🟡 Moyen | Navigation et compréhension réduite |
| Pas d'alt text sur images profil/projets | 🟡 Moyen | Images invisibles pour Google Images |
| Articles : pas de pagination (10 max hardcodé) | 🟡 Moyen | Limite l'indexation des articles |
| Pas de Google Search Console configuré | 🟡 Moyen | Aucune visibilité sur les erreurs d'indexation |
| Title par défaut "Welcome!" | 🔴 Critique | SERP affiche "Welcome!" si block non surchargé |

---

## Objectifs KPI

| Métrique | Baseline (maintenant) | 3 mois | 6 mois | 12 mois |
|---|---|---|---|---|
| Pages indexées Google | ~1 | 20+ | 40+ | 60+ |
| Trafic organique/mois | 0 | 50 | 200 | 500+ |
| Position moyenne (nom+portfolio) | non mesuré | top 5 | top 3 | top 1 |
| Rich snippets actifs | 0 | 3 types | 5 types | 5 types |
| Core Web Vitals (LCP) | non mesuré | < 2.5s | < 2.5s | < 2.0s |

---

## Roadmap — 4 phases

### Phase 1 — Fondations techniques (Semaines 1-2)
**Priorité absolue. Bloquant pour tout le reste.**

#### ✅ 1.1 `robots.txt` + `sitemap.xml`
- Créer `public/robots.txt` statique
- Créer `SitemapController` qui génère `/sitemap.xml` dynamiquement (projets publiés + articles publiés)
- Déclarer `Sitemap: https://[domain]/sitemap.xml` dans robots.txt
- Bloquer `/admin`, `/login`, `/reset-password`, `/_profiler`, `/theme`

#### ✅ 1.2 `base.html.twig` — refonte SEO complète
- Ajouter `lang="{{ app.request.locale }}"` sur `<html>`
- Rendre `meta description` dynamique via un block Twig `{% block meta_description %}`
- Ajouter bloc `{% block og_tags %}` avec : `og:title`, `og:description`, `og:url`, `og:image`, `og:type`, `og:locale`
- Ajouter bloc `{% block twitter_card %}` avec : `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`
- Ajouter `<link rel="canonical" href="{{ app.request.uri }}">` (ou block surchargeable)
- Chaque page enfant surcharge ces blocs

#### ❌ 1.3 hreflang (bilingue FR/EN) — *non implémenté : locale session-based, pas d'URL par langue*
- Ajouter dans `<head>` les balises `hreflang` pour chaque route disponible dans les deux langues
- `x-default` pointe vers la version FR

---

### Phase 2 — Données structurées (Semaines 3-4)

#### ✅ 2.1 Slugs pour les projets
- Ajouter champ `slug` à l'entité `Project` (unique, nullable temporairement pour migration)
- Générer les slugs automatiquement via `PrePersist`/`PreUpdate` (même logique qu'`Article`)
- Générer les slugs pour les projets existants via migration Doctrine
- Activer la route `/projects/{slug}` dans `ProjectController`
- Redirection 301 de `/projects/{id}` → `/projects/{slug}`
- Mettre à jour tous les liens internes (templates Twig)

#### ✅ 2.2 JSON-LD — Person schema (Home)
- Sur `home/index.html.twig` : injecter `<script type="application/ld+json">` avec schema `Person`
  - name, jobTitle, url, email, sameAs (GitHub, LinkedIn), image

#### ✅ 2.3 JSON-LD — Article schema
- Sur `article/show.html.twig` : schema `Article` avec headline, datePublished, dateModified, author, description, image

#### ✅ 2.4 JSON-LD — CreativeWork/SoftwareApplication schema  
- Sur `project/show.html.twig` : schema `CreativeWork` avec name, description, url, author, dateCreated, keywords (technologies)

#### ✅ 2.5 JSON-LD — BreadcrumbList
- Sur pages détail (article, projet) : schema `BreadcrumbList`
- Exemple : Home > Articles > {titre article}

#### ✅ 2.6 Alt text sur les images
- `AboutMe` photo de profil : alt = nom + "développeur web"
- Images projets : alt = nom du projet + technologie principale
- Auditer tous les `<img>` dans les templates

---

### Phase 3 — Contenu & performance (Semaines 5-8)

#### ✅ 3.1 Titles et meta descriptions optimisés par page

| Page | Title cible | Meta description cible |
|---|---|---|
| Home (FR) | `Florent Vasseur — Développeur Web Symfony` | `Portfolio de Florent Vasseur, développeur web full-stack spécialisé Symfony et PHP. Découvrez mes projets, mon expérience et contactez-moi.` |
| Home (EN) | `Florent Vasseur — Symfony Web Developer` | `Portfolio of Florent Vasseur, full-stack web developer specializing in Symfony and PHP. Browse my projects, experience and get in touch.` |
| Projects list | `Projets — Florent Vasseur` | `Découvrez les projets web réalisés par Florent Vasseur : applications Symfony, APIs, dashboards et plus.` |
| Project detail | `{nom projet} — Projet de Florent Vasseur` | `{description projet} — Stack : {technologies}` |
| Articles list | `Blog — Florent Vasseur` | `Articles et tutoriels sur Symfony, PHP et le développement web par Florent Vasseur.` |
| Article detail | `{titre article} — Blog Florent Vasseur` | `{résumé ou intro article}` |

#### ✅ 3.2 Pagination des articles
- Implémenter pagination Doctrine (KnpPaginatorBundle ou pagination manuelle)
- URLs paginées : `/articles/?page=2` avec `rel="next"` / `rel="prev"` dans `<head>`
- Éviter les pages vides dans le sitemap

#### ❌ 3.3 Liens internes — *non implémenté*
- Sur la home : lien "Voir tous mes projets" → `/projects/`
- Sur chaque article : section "Projets liés" si des tags correspondent
- Sur chaque projet : section "Articles liés" via tags communs
- Fil d'Ariane visible (Breadcrumb HTML + schema)

#### ⚠️ 3.4 Core Web Vitals — *partiellement implémenté*
- Auditer avec Lighthouse (LCP, CLS, FID/INP)
- Images : ajouter `loading="lazy"` + `width`/`height` explicites sur toutes les `<img>`
- Vérifier que AOS (animations on scroll) ne bloque pas le LCP
- Minification CSS/JS déjà gérée par Webpack Encore ✓

---

### Phase 4 — Autorité & monitoring (Mois 3-12)

#### 4.1 Google Search Console
- Vérifier le domaine (méta tag ou DNS)
- Soumettre le sitemap
- Surveiller couverture d'index, erreurs 404, performances de recherche

#### 4.2 Analytics
- Vérifier que Google Analytics ou équivalent privacy-friendly (Plausible, Matomo) est en place
- Configurer les conversions : formulaire de contact envoyé

#### 4.3 Backlinks — stratégie portfolio
- Profil GitHub : URL du portfolio dans la bio
- LinkedIn : URL du portfolio dans "Site web"
- DEV.to / Hashnode : publier des extraits d'articles avec lien vers le blog
- Ajouter le portfolio sur : malt.fr, wellfound.com, portfoliobox annuaires dev FR
- Répondre sur Stack Overflow avec lien contextuel

#### 4.4 Contenu régulier (signal E-E-A-T)
- Publier 1 article/mois minimum sur des sujets Symfony, PHP, architecture
- Chaque article : 800+ mots, titre H1 unique, H2/H3 structurés, meta description personnalisée
- Cibler des mots-clés longue traîne : "symfony portfolio", "développeur symfony freelance france", etc.

---

## Implémentation prioritaire — Ordre exact

```
1. robots.txt                          ← 30 min, impact immédiat
2. base.html.twig refonte              ← 2h, bloquant pour tout
3. Sitemap dynamique                   ← 2h, critique pour indexation
4. Project slug (entité + migration)   ← 3h, URLs propres
5. JSON-LD Person (home)               ← 1h, rich snippets
6. JSON-LD Article (show)              ← 1h, rich snippets blog
7. JSON-LD Project (show)              ← 1h, rich snippets projets
8. hreflang                            ← 1h, bilingue
9. Alt text images                     ← 1h, Google Images
10. Pagination articles                ← 2h, indexation complète
11. Titles/descriptions par page       ← 1h, CTR
12. Google Search Console              ← 30 min, monitoring
```

**Total estimé : ~16h de développement**

---

## Stack technique recommandée

- Pas de bundle SEO externe nécessaire : tout en Twig natif + Symfony Routing
- `Symfony\Component\String\Slugger\SluggerInterface` pour génération des slugs (déjà disponible)
- Sitemap : contrôleur Symfony simple, réponse XML, mis en cache HTTP 24h
- JSON-LD : blocs Twig, pas de bundle spécifique

---

## État du code — Juin 2026

### ✅ Implémenté (branch `feat/seo-improvements`)

| Fichier | Description |
|---|---|
| `src/Controller/SitemapController.php` | Route `/sitemap.xml` dynamique |
| `src/Controller/RobotsController.php` | Route `/robots.txt` avec blocage admin/login |
| `templates/seo/sitemap.xml.twig` | Sitemap XML avec lastmod |
| `templates/seo/robots.txt.twig` | Robots.txt pointant vers sitemap |
| `templates/base.html.twig` | `lang=`, meta description dynamique, OG/Twitter/canonical/JSON-LD blocks |
| `templates/home/index.html.twig` | Titre optimisé, meta, og:image, JSON-LD Person, fetchpriority LCP |
| `templates/article/index.html.twig` | Titre, meta, JSON-LD CollectionPage, pagination UI, rel prev/next |
| `templates/article/show.html.twig` | Titre, og:type article, og:image, JSON-LD Article + Breadcrumb |
| `templates/project/index.html.twig` | Titre, meta, JSON-LD CollectionPage, lazy loading |
| `templates/project/show.html.twig` | og:image, JSON-LD CreativeWork + Breadcrumb, lazy loading |
| `src/Entity/Project.php` | Champ `slug` + `#[Gedmo\Slug(fields: ['title'])]` |
| `src/Controller/ProjectIndexController.php` | Route `/projects/` |
| `src/Controller/ProjectShowByIdController.php` | Redirection 301 `/projects/{id}` → `/projects/{slug}` |
| `src/Controller/ProjectShowBySlugController.php` | Route `/projects/{slug}` |
| `src/Repository/ArticleRepository.php` | `countPublished()` pour pagination |
| `src/Controller/ArticleIndexController.php` | Pagination 9 articles/page avec `?page=N` |
| `src/Controller/ArticleShowController.php` | Route `/articles/{slug}` |
| `migrations/Version20260608000000.php` | Ajout colonne `slug` sur table `project` |

---

## Actions manuelles — À faire après déploiement

### 🔴 Priorité immédiate

#### 1. Lancer la migration Doctrine
```bash
php bin/console doctrine:migrations:migrate
```

#### 2. Régénérer les slugs des projets existants
Gedmo Slug génère automatiquement le slug à la **prochaine sauvegarde** de chaque projet.
→ Aller dans EasyAdmin > Projets > ouvrir chaque projet > Save (sans changer quoi que ce soit).
Alternative rapide via console :
```bash
php bin/console doctrine:query:sql "UPDATE project SET slug = LOWER(REGEXP_REPLACE(REPLACE(REPLACE(title, ' ', '-'), '''', ''), '[^a-z0-9-]', '')) WHERE slug IS NULL"
```
⚠️ Cette commande génère des slugs basiques sans gestion des doublons — privilégier la resauvegarde via EasyAdmin pour un résultat propre.

#### 3. Vérifier `/robots.txt` et `/sitemap.xml` en production
- Ouvrir `https://[domain]/robots.txt` → vérifier que Disallow et Sitemap URL sont corrects
- Ouvrir `https://[domain]/sitemap.xml` → vérifier que projets et articles apparaissent

### 🟠 Semaine 1 post-déploiement

#### 4. Google Search Console
- Aller sur [search.google.com/search-console](https://search.google.com/search-console)
- Ajouter la propriété (domaine ou préfixe URL)
- Vérifier via balise meta ou DNS
- Soumettre `https://[domain]/sitemap.xml`
- Vérifier la couverture d'indexation après 48-72h

#### 5. Google Analytics / Plausible
- Vérifier qu'un outil de tracking est actif
- Configurer un objectif de conversion sur le formulaire de contact

#### 6. Mettre à jour les profils externes
- **GitHub** : ajouter l'URL du portfolio dans la bio
- **LinkedIn** : ajouter l'URL dans "Site web" du profil
- **DEV.to / Hashnode** : créer un compte et publier un extrait d'article avec lien retour

### 🟡 Mois 1-3

#### 7. Annuaires développeurs francophones
- [malt.fr](https://malt.fr) — créer un profil avec lien portfolio
- [comet.co](https://comet.co) — idem
- [wellfound.com](https://wellfound.com) — profil anglophone

#### 8. Contenu régulier (signal E-E-A-T)
- Publier minimum **1 article/mois** via EasyAdmin
- Cibler mots-clés longue traîne : `développeur symfony freelance`, `portfolio symfony php`, `tutoriel symfony [version]`
- Chaque article : H1 unique, H2/H3 structurés, 800+ mots, meta description personnalisée via EasyAdmin

#### 9. Surveiller les Core Web Vitals
- Tester avec [PageSpeed Insights](https://pagespeed.web.dev) une fois déployé
- LCP cible : < 2.5s (photo profil déjà en `fetchpriority="high"`)
- CLS cible : < 0.1 (vérifier les images sans dimensions explicites)

---

## Reste à faire (code)

### ❌ 1.3 hreflang
**Prérequis :** migrer le système de locale de session-based vers URL-based (`/fr/`, `/en/`).
Sans URL différente par langue, les balises hreflang n'ont aucune valeur pour Google.
> Option pragmatique : ajouter un paramètre `?lang=fr` et un `LocaleSubscriber` qui lit ce paramètre — permet d'implémenter hreflang sans restructurer toutes les routes.

### ❌ 3.3 Liens internes
Ce qui manque :
- Section "Articles liés" sur `project/show.html.twig` — articles partageant un tag avec le projet
- Section "Projets liés" sur `article/show.html.twig` — projets partageant un tag avec l'article
- Breadcrumb HTML visible (actuellement breadcrumb uniquement en JSON-LD, pas affiché)

**Effort estimé :** ~3h
- Ajouter méthode `findByTag()` dans `ArticleRepository` et `ProjectRepository`
- Injecter les résultats depuis les contrôleurs `ArticleShowController` et `ProjectShowBySlugController`
- Ajouter les sections HTML dans les templates

### ⚠️ 3.4 Core Web Vitals — dimensions explicites manquantes
`loading="lazy"` et `fetchpriority="high"` sont en place.
Ce qui reste :
- Ajouter `width` et `height` explicites sur les `<img>` pour éviter le CLS (Cumulative Layout Shift)
- Les dimensions ne peuvent pas être hardcodées en Twig sans connaître les dimensions réelles des images uploadées
- **Solution recommandée :** stocker `width`/`height` dans les entités `Project`, `Article`, `AboutMe` lors de l'upload (via VichUploader listener) et les exposer dans les templates
