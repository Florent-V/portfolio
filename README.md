# Gestion de contenu d'article

Cette documentation explique comment gérer le contenu des articles avec le nouveau système de blocs.

## Principe

Un article n'est plus un simple bloc de texte, mais une collection de "blocs de contenu". Chaque bloc peut être d'un des types suivants :

*   **Paragraphe** : Un simple bloc de texte.
*   **Image** : Une image avec un texte alternatif.
*   **Code** : Un extrait de code avec son langage spécifié pour la coloration syntaxique.

Ces blocs sont ordonnés et affichés séquentiellement sur la page de l'article.

## Comment ça marche ?

### Entités

*   `Article` : L'entité principale de l'article. Elle contient les métadonnées de l'article (titre, slug, etc.) et une collection de `ArticleContent`.
*   `ArticleContent` : Représente un bloc de contenu. Il a un type (`ArticleContentType`), un ordre d'affichage, et une relation vers l'entité de contenu spécifique (`ArticleImage` ou `ArticleCodeSnippet`).
*   `ArticleImage` : Contient les informations sur une image (nom du fichier, texte alternatif).
*   `ArticleCodeSnippet` : Contient un extrait de code et son langage.

### EasyAdmin

Dans l'interface d'administration, lorsque vous créez ou modifiez un article, vous verrez une section "Contenu de l'article". Vous pouvez y ajouter, supprimer et réordonner des blocs de contenu.

Pour chaque bloc, vous devez d'abord choisir son type. En fonction du type choisi, vous devrez remplir les champs correspondants :

*   **Paragraphe** : Remplissez le champ "Contenu".
*   **Image** : Sélectionnez une image dans la liste. Vous pouvez en créer de nouvelles via le CRUD `ArticleImage`.
*   **Code** : Sélectionnez un extrait de code dans la liste. Vous pouvez en créer de nouveaux via le CRUD `ArticleCodeSnippet`.

N'oubliez pas de définir l'ordre d'affichage pour chaque bloc.

### Affichage

Le template `article/show.html.twig` itère sur les blocs de contenu de l'article et les affiche dans l'ordre défini. Chaque type de bloc a son propre rendu.
