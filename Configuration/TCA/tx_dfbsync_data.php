<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

$tx_dfbsync_data = [
    'ctrl' => [
        'title' => 'LLL:EXT:dfbsync/Resources/Private/Language/locallang_db.xlf:tx_dfbsync_data',
        'label' => 'competition',
        'searchFields' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
        ],
        'typeicon_classes' => [
            'default' => 'ext-cfcleague-saison-default',
        ],
        'iconfile' => 'EXT:cfc_league/Resources/Public/Icons/icon_tx_cfcleague_saison.gif',
    ],
    'columns' => [
        'competition' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:cfc_league/Resources/Private/Language/locallang_db.xlf:tx_cfcleague_games.competition',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [' ', '0'],
                ],
                'foreign_table' => 'tx_cfcleague_competition',
                'foreign_table_where' => 'AND tx_cfcleague_competition.pid=###CURRENT_PID### ORDER BY tx_cfcleague_competition.uid',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'lastsync' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dfbsync/Resources/Private/Language/locallang_db.xlf:tx_dfbsync_data_lastsync',
            'config' => [
                'type' => 'input',
                'size' => '10',
                'max' => '20',
                'eval' => 'required,trim',
            ],
        ],
        'success' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:dfbsync/Resources/Private/Language/locallang_db.xlf:tx_dfbsync_data_success',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'competition, lastsync, success'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];

if (\Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher()) {
    unset($tx_dfbsync_data['interface']['showRecordFieldList']);
}

return $tx_dfbsync_data;
