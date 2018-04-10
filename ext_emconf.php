<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Meta Tag',
    'description' => 'Fluid view helper for generating meta tags.',
    'category' => 'frontend',
    'author' => 'Felix Althaus',
    'author_email' => 'felix.althaus@undkonsorten.com',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7',
            'php' => '7.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Undkonsorten\\MetaTag\\' => 'Classes/'
        ]
    ],
];
