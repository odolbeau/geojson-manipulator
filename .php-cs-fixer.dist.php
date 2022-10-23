<?php

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ))
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
