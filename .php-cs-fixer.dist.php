<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'node_modules']) // Exclusions
    ->notPath('src/Migrations/*') // Exclure les fichiers de migrations
    ->name('*.php') // Fichiers concernés
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);


return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true) // Active les règles risquées
    ->setIndent('    ') // Indentation avec 4 espaces (PSR-12)
    ->setLineEnding("\n") // Force les fins de ligne Unix
    ->setRules([
        '@Symfony' => true, // Applique les règles Symfony
        '@PSR12' => true, // Applique PSR-12
        'array_syntax' => ['syntax' => 'short'], // Utilisation de `[]` au lieu de `array()`
        'no_unused_imports' => true, // Supprime les `use` inutilisés
        'ordered_imports' => ['sort_algorithm' => 'alpha'], // Trie les `use`
        'single_line_throw' => false, // Autorise les `throw` multi-lignes
        'concat_space' => ['spacing' => 'one'], // Ajoute un espace autour des concaténations
        'declare_strict_types' => true, // Ajoute `declare(strict_types=1);` en début de fichier
        'phpdoc_align' => true, // Aligne les commentaires PHPDoc
        'phpdoc_order' => true, // Ordonne les annotations PHPDoc (@param avant @return)
        'phpdoc_separation' => true, // Ajoute une ligne entre chaque annotation
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
    ])
    ->setFinder($finder);
