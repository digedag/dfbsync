<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}
$_EXTKEY = 'dfbsync';

// Hook for competition search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Competition_getTableMapping_hook'][] = 'System25\T3sports\DfbSync\Hook\Search->getTableMappingCompetition';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Competition_getJoins_hook'][] = 'System25\T3sports\DfbSync\Hook\Search->getJoinsCompetition';

// Scheduler für Sync
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\System25\T3sports\DfbSync\Scheduler\SyncTask::class] = [
    'extension' => $_EXTKEY,
    'title' => '[DFB Sync] Spielplan aktualisieren',
    'description' => 'Aktualisiert die Spielpläne des DFB',
    'additionalFields' => \System25\T3sports\DfbSync\Scheduler\SyncTaskAddFieldProvider::class,
];
