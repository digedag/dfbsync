<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "dfbsync".
 *
 * Auto generated 03-02-2015 22:01
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'DFB Sync',
    'description' => 'Import and update competitions from DFB Sportmedia',
    'category' => 'module',
    'shy' => 0,
    'version' => '1.4.0',
    'dependencies' => 'rn_base',
    'module' => 'mod1',
    'state' => 'stable',
    'uploadfolder' => 1,
    'createDirs' => '',
    'clearcacheonload' => 1,
    'lockType' => '',
    'author' => 'Rene Nitzsche',
    'author_email' => 'rene@system25.de',
    'author_company' => 'System 25',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-12.4.99',
            'php' => '7.1.0-8.9.99',
            'rn_base' => '1.18.0-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
