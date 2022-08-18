<?php

use PhpCsFixer\Config;

return (new Config())
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->files()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
    )
    ->setRules([
        '@PSR2' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => trim(file_get_contents(__DIR__ . '/LICENSE.md')),
            'location' => 'after_open',
            'separate' => 'none',
        ],
    ]);
