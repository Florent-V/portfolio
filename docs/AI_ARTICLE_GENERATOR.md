# Générateur d'articles via IA

## Vue d'ensemble

Cette fonctionnalité permet de générer automatiquement des articles techniques depuis l'interface d'administration.
L'IA produit un article structuré (titre, slug, blocs paragraphe et code) qui est sauvegardé en brouillon
pour relecture avant publication.

---

## Variables d'environnement

Ajoutez ces variables dans votre fichier `../.env.local` (jamais dans `../.env` pour les clés secrètes) :

| Variable | Obligatoire | Exemple | Description |
|---|---|---|---|
| `OPENROUTER_API_KEY` | **Oui** | `sk-or-v1-xxxxx` | Clé API OpenRouter |
| `AI_DEFAULT_MODEL` | Oui | `openai/gpt-4o` | Modèle utilisé sans recherche web |
| `AI_WEB_SEARCH_MODEL` | Oui | `perplexity/sonar-pro` | Modèle utilisé avec recherche web |

### Obtenir une clé OpenRouter

1. Créer un compte sur [openrouter.ai](https://openrouter.ai)
2. Aller dans **Keys** → **Create Key**
3. Copier la clé dans `../.env.local` :

```dotenv
OPENROUTER_API_KEY=sk-or-v1-votre-cle-ici
AI_DEFAULT_MODEL=openai/gpt-4o
AI_WEB_SEARCH_MODEL=perplexity/sonar-pro
```

### Modèles recommandés (via OpenRouter)

**Sans recherche web (`AI_DEFAULT_MODEL`) :**
| Modèle | Qualité | Coût | Notes |
|---|---|---|---|
| `openai/gpt-4o` | Excellent | ~$5/Mtok | Meilleur équilibre qualité/coût |
| `anthropic/claude-opus-4.6` | Excellent | ~$15/Mtok | Meilleure rédaction |
| `openai/gpt-4o-mini` | Bon | ~$0.15/Mtok | Option économique |
| `anthropic/claude-3.5-haiku` | Bon | ~$0.8/Mtok | Rapide et économique |

**Avec recherche web (`AI_WEB_SEARCH_MODEL`) :**
| Modèle | Notes |
|---|---|
| `perplexity/sonar-pro` | Recommandé — recherche web haute qualité |
| `perplexity/sonar` | Version économique de Sonar |

### Ajouter des providers natifs à l'avenir

Pour basculer vers un provider direct (sans OpenRouter), modifier `../config/packages/ai.yaml` :

```yaml
# Claude direct
ai:
    platform:
        anthropic:
            api_key: '%env(ANTHROPIC_API_KEY)%'
    agent:
        article_generator:
            platform: 'ai.platform.anthropic'
            model: 'claude-opus-4-7-20251101'
```

Packages disponibles : `symfony/ai-open-ai-platform`, `symfony/ai-anthropic-platform`,
`symfony/ai-gemini-platform`, `symfony/ai-mistral-platform`.

---

## Architecture de l'implémentation

### Packages installés

```
symfony/ai-bundle             ^0.9   — Bundle Symfony pour l'IA
symfony/ai-platform           ^0.9   — Composant platform (interfaces communes)
symfony/ai-generic-platform   ^0.9   — Bridge OpenAI-compatible (dépendance)
symfony/ai-open-router-platform ^0.9 — Bridge OpenRouter natif
symfony/ai-agent              ^0.9   — Composant agent (appels LLM)
twig/twig                     ^3.27  — Moteur de rendu des prompts (déjà requis par symfony/twig-bundle)
league/commonmark             ^2.8   — Conversion Markdown → HTML des blocs "format: markdown"
```

### Trois flux, un pipeline commun

```
[Admin] ArticleGeneratorController   (topic → article)
[Admin] ArticleHumanizerController   (texte brut → article)
[Admin] ArticleSummarizerController  (article existant → résumé 3 points)
    │
    ├── [Form] ArticleGeneratorFormType / ArticleHumanizerFormType
    │         (provider, langue, format HTML|Markdown, longueur — générateur uniquement)
    │
    └── [Service] ArticleGeneratorService / ArticleHumanizerService / ArticleSummaryService
            │
            ├── Sélectionne l'agent via AiProviderRegistry (OpenRouter, Ollama, Nvidia, Gemini, Mistral)
            ├── [Builder] PromptBuilder → rend les templates Twig sous resources/ai/prompts/
            ├── AgentInterface::call(MessageBag) → appel du provider
            ├── [Parser] ArticleResponseParser::parse(string) → GeneratedArticleData DTO
            ├── [Mapper] ArticleResponseMapper::map() → Article + ArticleContent[]
            └── AiArticleProcessor orchestre l'appel, le parsing, le mapping, le logging (AiGenerationLogger) et le flush
```

### Prompts composables (Twig)

```
resources/ai/prompts/
├── _partials/
│   └── content_block_schema.twig   — schéma JSON + règles HTML/Markdown partagées, paramétré par
│                                      `format` (html|markdown) et, pour le générateur, `length_instruction`
├── generator/{system,user}.twig    — inclut le partiel, ajoute la consigne de longueur
├── humanizer/{system,user}.twig    — inclut le partiel, pas de longueur (dictée par le texte source)
└── summarizer/{system,user}.twig   — sortie fixe (<ul> de 3 points), non concerné par format/longueur
```

`PromptBuilder` (`../src/AI/PromptBuilder.php`) rend ces fichiers via un `Twig\Environment` dédié (loader isolé sur `../resources/ai/prompts`, autoescape désactivé — les prompts contiennent des balises HTML littérales comme `<h2>` qui ne doivent pas être échappées).

### Longueur (générateur uniquement)

`App\Enum\ArticleLength` (`../src/Enum/ArticleLength.php`) — 3 paliers, exposés dans le formulaire du générateur :

| Palier | Mots | Blocs paragraphe |
|---|---|---|
| `SHORT` | ~500 | 2-3 |
| `MEDIUM` (défaut) | ~1000 | 4-6 |
| `LONG` | ~2000 | 7-10 |

### Format (générateur + humanizer)

`App\Enum\ArticleContentFormat` (`../src/Enum/ArticleContentFormat.php`) — `HTML` (défaut) ou `MARKDOWN`. Un seul format par génération : la valeur choisie est forcée sur chaque bloc renvoyé par l'IA (`"format": "html"` ou `"format": "markdown"` dans chaque objet `content_blocks[]`). Le format HTML n'autorise que des balises sémantiques (`<h2>`, `<p>`, `<strong>`, `<em>`, `<code>`, `<ul>`, `<ol>`, `<li>`, `<blockquote>`) — jamais de `class` ni de `style` : le rendu visuel vient entièrement du CSS de l'application (`.prose` + variables DaisyUI dans `../assets/styles/article.css`), jamais du contenu généré.

### Format JSON attendu de l'IA

```json
{
    "title": "Les nouveautés de PHP 8.4 : guide complet",
    "slug": "nouveautes-php-8-4-guide-complet",
    "content_blocks": [
        {
            "type": "paragraph",
            "content": "<h2>Introduction</h2><p>PHP 8.4 apporte...</p>",
            "display_order": 1,
            "language": null,
            "format": "html"
        },
        {
            "type": "code",
            "content": "<?php\n$result = array_find([1, 2, 3], fn($x) => $x > 2);",
            "display_order": 2,
            "language": "php",
            "format": "html"
        }
    ]
}
```

Types supportés : `paragraph` (texte) et `code` (bloc de code avec langage). `format` est optionnel côté parseur (défaut `"html"`), mais toujours émis explicitement par les prompts actuels.

### Fichiers clés

```
src/AI/
├── DTO/
│   ├── ArticleGenerationRequest.php   — topic, langue, provider, format, length, web search
│   ├── ArticleHumanizeRequest.php     — texte brut, langue, provider, format
│   ├── ContentBlockData.php           — bloc parsé (type, content, displayOrder, language, format)
│   └── GeneratedArticleData.php       — article parsé (DTO intermédiaire)
├── Enum/AiProvider.php                — openrouter, ollama, nvidia, gemini, mistral
├── Provider/                          — un adaptateur par provider (AiProviderInterface)
├── Exception/ArticleGenerationException.php
├── PromptBuilder.php                  — rendu Twig des prompts
├── ArticleGeneratorService.php        — topic → article
├── ArticleHumanizerService.php        — texte brut → article
├── ArticleSummaryService.php          — article → résumé 3 points
├── ArticleResponseParser.php          — parsing et validation du JSON de l'IA
├── ArticleResponseMapper.php          — mapping DTO → entités Doctrine
├── AiArticleProcessor.php             — orchestration commune (call, parse, map, log, flush)
├── AiProviderRegistry.php             — résolution de l'agent/modèle par provider
└── AiGenerationLogger.php             — persistance des logs de génération

src/Enum/
├── ArticleLength.php                  — paliers de longueur (générateur)
└── ArticleContentFormat.php           — html | markdown

src/Form/Admin/
├── ArticleGeneratorFormType.php
├── ArticleHumanizerFormType.php
└── ArticleJsonImportFormType.php

templates/admin/
├── article_generator/index.html.twig
├── article_humanizer/index.html.twig
└── article_importer/index.html.twig
```

---

## Notice de test

### Prérequis

1. Configurer les variables dans `../.env.local` :

```dotenv
OPENROUTER_API_KEY=sk-or-v1-xxxxx
AI_DEFAULT_MODEL=openai/gpt-4o
AI_WEB_SEARCH_MODEL=perplexity/sonar-pro
```

2. Vérifier que le compte OpenRouter a du crédit (https://openrouter.ai/credits).

3. S'assurer qu'un utilisateur admin existe en BDD.

---

### Test 1 — Accès à la page

1. Se connecter à l'admin : `/admin`
2. Dans le menu de gauche, section **"Intelligence Artificielle"**, cliquer sur **"Générer un article"**
3. **Résultat attendu :** Page de génération avec un formulaire DaisyUI/Tailwind

**URL directe :** `/admin/generate-article`

---

### Test 2 — Génération sans recherche web

1. Remplir le formulaire :
   - **Thème :** `Introduction aux propriétés readonly en PHP 8.1`
   - **Langue :** Français
   - **Recherche web :** décoché
   - **Instructions supplémentaires :** (vide)
2. Cliquer **"Générer l'article"**
3. **Attendre 15–45 secondes**
4. **Résultat attendu :**
   - Message flash vert : `Article "..." généré et sauvegardé en brouillon !`
   - Redirection vers la page d'édition EasyAdmin de l'article
   - L'article a `isPublished = false`
   - Les blocs de contenu (`ArticleContent`) sont présents (section "Blocs de contenu")

---

### Test 3 — Génération avec recherche web

1. Remplir le formulaire :
   - **Thème :** `Les dernières nouvelles sur PHP 8.5`
   - **Langue :** Français
   - **Recherche web :** coché ✓
2. Cliquer **"Générer l'article"**
3. **Résultat attendu :** Même comportement qu'au Test 2, mais avec des infos plus récentes (si le modèle a accès au web)

> **Note :** `perplexity/sonar-pro` fait des recherches web en temps réel. Les résultats peuvent varier.

---

### Test 4 — Validation du formulaire

1. Laisser le champ **Thème** vide
2. Cliquer **"Générer l'article"**
3. **Résultat attendu :** Message d'erreur de validation sous le champ ("Le thème ne peut pas être vide.")

---

### Test 5 — Vérification en base de données

Après la génération (Test 2 ou 3), vérifier en BDD :

```sql
-- Dernier article créé
SELECT id, title, slug, is_published, author_id FROM article ORDER BY id DESC LIMIT 1;

-- Blocs de contenu associés
SELECT type, display_order, LEFT(content, 100) as content_preview, language
FROM article_content
WHERE article_id = <id_article>
ORDER BY display_order;
```

**Attendu :**
- `is_published = 0` (brouillon)
- Au moins 4-6 blocs de contenu avec `type = 'paragraph'` ou `type = 'code'`
- `display_order` séquentiel (1, 2, 3…)

---

### Test 6 — Gestion d'erreur (clé API invalide)

1. Mettre une fausse clé dans `../.env.local` : `OPENROUTER_API_KEY=sk-invalid`
2. Tenter une génération
3. **Résultat attendu :** Message flash rouge avec l'erreur provider, pas de crash 500

---

### Test 7 — Unicité du slug

1. Générer deux articles sur le même thème
2. Vérifier en BDD que les slugs sont différents (le deuxième a un suffixe aléatoire)

---

## Logs

Les événements de génération sont loggués dans `../var/log/dev.log` :

```
[info] Starting AI article generation {"topic": "...", "language": "fr", "use_web_search": false, "agent": "article_generator"}
[info] AI article parsed successfully {"title": "...", "slug": "...", "block_count": 8}
```

En cas d'erreur :
```
[error] AI agent call failed {"exception": "..."}
```

---

## Générer un article via un LLM en ligne (ChatGPT, Claude.ai, Gemini...)

Le JSON produit par n'importe quel LLM peut être importé via `/admin/import-article-json` (`ArticleJsonImportController`), exactement comme le JSON généré en interne. Le prompt ci-dessous est autonome (pas besoin d'avoir ce dépôt ou le skill Claude Code sous la main) — colle-le tel quel dans l'interface du LLM, puis modifie uniquement les lignes marquées `[À REMPLIR]`.

```
Tu es un rédacteur technique senior. Génère un article complet et retourne-le comme un UNIQUE objet JSON valide, strictement conforme au schéma ci-dessous. Aucun texte avant ou après le JSON, aucun bloc de code markdown autour (pas de ```json```), juste { ... }.

SCHÉMA JSON REQUIS :
{
  "title": "titre accrocheur, spécifique, pas générique",
  "slug": "kebab-case-sans-accents-derive-du-titre",
  "content_blocks": [
    {
      "type": "paragraph | code",
      "content": "voir règles de format ci-dessous",
      "display_order": 1,
      "language": "identifiant en minuscule pour un bloc code (php, javascript, bash...), null pour un paragraphe",
      "format": "html | markdown"
    }
  ]
}

=== PARAMÈTRES [À REMPLIR] ===
SUJET : [décris ici précisément le sujet, l'angle, ce que l'article doit couvrir, le public visé]
LANGUE : Français
LONGUEUR : Moyen   (Court ~500 mots / Moyen ~1000 mots / Long ~2000 mots — choisis-en un)
FORMAT : HTML   (HTML ou Markdown — choisis-en un, jamais les deux dans le même article)
INSTRUCTIONS SUPPLÉMENTAIRES (optionnel) : [ex: cibler les débutants, inclure un exemple de code PHP, ton plus personnel, éviter tel sujet...]
===============================

RÈGLE DE FORMAT — respecte EXACTEMENT le FORMAT choisi ci-dessus. Chaque bloc "content_blocks[].format" doit porter cette même valeur, sans exception.

SI FORMAT = HTML :
- Chaque bloc "paragraph" commence OBLIGATOIREMENT par <h2>Titre de section</h2> (y compris intro et conclusion)
- Suivi de 2 à 4 <p> de 3 à 5 phrases chacun
- Pour un article long avec des sections denses, un <h3> optionnel peut subdiviser une section en sous-thèmes — jamais de <h3> sans <h2> parent dans le même bloc
- Tableau (<table><thead><tbody><tr><th><td>) autorisé pour comparer 2+ éléments sur des critères communs — même usage que le tableau Markdown, en vraies balises
- Tags autorisés UNIQUEMENT : <h2> <h3> <p> <strong> <em> <code> <ul> <ol> <li> <blockquote> <table> <thead> <tbody> <tr> <th> <td>
- INTERDIT : <div>, <span>, attribut style
- attribut class AUTORISÉ uniquement avec ces classes Tailwind/DaisyUI précises (les seules compilées pour ce contenu — le site scanne son CSS au build et ne compile aucune classe qui n'existe que dans ce JSON, sauf celles-ci explicitement mises en liste blanche) : text-primary text-secondary text-accent text-error text-warning text-success text-info font-bold italic underline uppercase tracking-wide text-sm text-base text-lg text-xl. Toute autre classe n'aura AUCUN effet visuel. Usage rare, seulement pour un vrai besoin de mise en avant au-delà de <strong>/<em> (ex: <strong class="text-primary">terme</strong>), jamais en remplacement des balises sémantiques.

SI FORMAT = MARKDOWN :
- Chaque bloc "paragraph" commence OBLIGATOIREMENT par "## Titre de section"
- Syntaxe CommonMark de base + extensions GFM activées côté site (tableaux, barré, autolink) : **gras**, *italique*, `code`, listes à puces "- item" ou numérotées "1. item", citations "> texte", liens "[texte](url)", séparateur "---" (rare, 0-1 par article), tableaux GFM (voir ci-dessous), ~~barré~~ (rare, usage "correction"), URLs nues auto-liées (pas besoin de `<...>`)
- Tableaux : à utiliser quand on compare 2+ éléments sur des critères communs. Syntaxe stricte : pipe de début ET de fin sur CHAQUE ligne, ligne de délimitation "|---|---|" avec EXACTEMENT le même nombre de colonnes que l'en-tête. Exemple :
  `| Critère | Option A | Option B |`
  `|---------|----------|----------|`
  `| Prix    | Cher     | Abordable|`
- INTERDIT : balises HTML brutes, classes CSS, ET les autres syntaxes GFM non supportées — listes de tâches "- [ ]", notes de bas de page : non chargées, s'afficheraient cassées ou en texte brut. (Toute balise HTML brute glissée par erreur sera de toute façon neutralisée/échappée par le convertisseur, mais reste interdite dans le prompt.)

Dans les deux cas :
- Utilise des listes dès que 3 éléments parallèles ou plus apparaissent dans le texte
- Mets en gras (strong/**) les termes clés à leur première apparition
- Jamais deux listes consécutives sans un paragraphe entre les deux
- Une citation maximum pour tout l'article

BLOCS DE CODE ("type": "code") :
- "content" = code source brut uniquement, jamais de HTML ni de Markdown autour
- "language" = identifiant en minuscule (php, javascript, typescript, bash, python, sql, yaml, json, html, css...)
- Indentation correcte pour le langage, contexte réaliste (imports, déclaration de fonction/classe)
- Articles non techniques : 0 bloc code

LONGUEUR — adapte le nombre de blocs à la valeur choisie dans les paramètres :
| Longueur | Mots visés | Blocs paragraphe | Blocs code |
|---|---|---|---|
| Court | ~500 | 2-3 (intro + 1 section + conclusion) | 0-1 |
| Moyen | ~1000 | 4-6 (intro + 2-3 sections + conclusion) | 0-2 |
| Long | ~2000 | 7-10 (intro + 5-8 sections + conclusion) | 1-3 |

STYLE — humain, pas IA :
- Varie la longueur des phrases : phrases courtes percutantes, suivies de phrases plus longues qui développent une idée
- Prends parti, ne noie pas tout dans des nuances prudentes
- Du concret plutôt que de l'abstrait ("réduit le temps de debug de 40%" plutôt que "améliore les performances")
- Voix active, transitions naturelles ("Mais voilà le problème.", "Et c'est là que ça devient intéressant.")
- BANNIS : "Il est important de noter que", "En conclusion, nous avons vu que", "Dans cet article, nous allons explorer"
- L'intro se termine par une accroche, pas un résumé du plan. La conclusion par une vraie idée, pas une liste récapitulative.

RÈGLES DU PARSEUR (le non-respect fait échouer l'import) :
- "type" exactement "paragraph" ou "code", rien d'autre
- "display_order" séquentiel à partir de 1, sans trou
- "language" = null pour tous les blocs "paragraph"
- "format" présent sur CHAQUE bloc, identique sur tous les blocs de l'article
- JSON strictement valide : chaînes correctement échappées (utilise \n pour les retours à la ligne, jamais de retour à la ligne brut dans une valeur), pas de guillemets non échappés

Réponds uniquement avec le JSON brut, rien d'autre avant ou après.
```

**Pour changer un paramètre**, modifie uniquement le bloc `=== PARAMÈTRES ===` avant d'envoyer :
- **Sujet** : la seule partie vraiment obligatoire à personnaliser — sois précis, plus le sujet est détaillé meilleur est l'article
- **Longueur** : `Court` / `Moyen` / `Long` (voir le tableau mots/blocs dans le prompt)
- **Format** : `HTML` (par défaut, compatible avec la zone de texte de l'admin) ou `Markdown` (si tu préfères écrire/relire en Markdown)
- **Instructions supplémentaires** : optionnel, pour cadrer le ton, le public, ce qu'il faut éviter, etc.

Une fois le JSON reçu, colle-le dans un fichier `.json` et importe-le via **`/admin/import-article-json`**.
