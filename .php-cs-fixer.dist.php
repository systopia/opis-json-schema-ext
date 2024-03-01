<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->filter(function (SplFileInfo $fileInfo) {
        // ignore code copied from upstream
        return 'SystopiaSchemaParser.php' !== $fileInfo->getFilename();
    })
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PhpCsFixer' => true,
    '@PhpCsFixer:risky' => true,
    'comment_to_phpdoc' => ['ignored_tags' => ['phpstan-ignore-next-line']],
    'fully_qualified_strict_types' => ['import_symbols' => false],
    'phpdoc_align' => ['align' => 'left'],
    'phpdoc_to_comment' => ['ignored_tags' => ['noinspection']],
    'php_unit_internal_class' => false,
    'php_unit_strict' => false,
    'no_superfluous_phpdoc_tags' => [
        'allow_mixed' => true,
        'remove_inheritdoc' => false,
    ],
])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
