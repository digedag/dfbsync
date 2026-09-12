<?php

return [
    'web_CfcLeagueM1_dfbsync' => [
        'parent' => 'web_CfcLeagueM1',
        'access' => 'user',
        'workspaces' => '*',
        'iconIdentifier' => 'ext-cfcleague-ext-default',
        'path' => '/module/web/t3sports/dfbsync',
        'labels' => [
            'title' => 'LLL:EXT:dfbsync/Resources/Private/Language/locallang_db.xlf:tx_dfbsync_module_name',
        ],
        'routes' => [
            '_default' => [
                'target' => System25\T3sports\DfbSync\Module\Controller\DfbSyncController::class.'::main',
            ],
        ],
        'moduleData' => [
            'langFiles' => [],
            'pages' => '0',
            'depth' => 0,
        ],
    ],
];
