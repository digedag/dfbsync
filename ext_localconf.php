<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$_EXTKEY = 'dfbsync';

// Scheduler für Sync
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\System25\T3sports\DfbNet\Scheduler\SyncTask::class] = [
    'extension'        => $_EXTKEY,
    'title'            => '[DFB Sync] Spielplan aktualisieren',
    'description'      => 'Aktualisiert die Spielpläne des DFB',
    'additionalFields' => \System25\T3sports\DfbNet\Scheduler\SyncTaskAddFieldProvider::class
];
