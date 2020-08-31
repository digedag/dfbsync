<?php
namespace System25\T3sports\DfbSync\Sync;


use System25\T3sports\DfbSync\Xml\MatchTableReader;
use System25\T3sports\DfbSync\Model\SyncData;
use System25\T3sports\DfbSync\Model\Repository\SyncDataRepository;

/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2020 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

/**
 */
class Runner
{
    const INFO_COMP_FOUND = 'compFound';
    const INFO_COMP_SYNCED = 'compSynced';
    const INFO_COMP_FILE = 'compFile';

    private $sync;
    private $syncRepo;

    public function __construct()
    {
        $this->sync = new CompetitionSync();
        $this->syncRepo = new SyncDataRepository();
    }

    public function sync(int $saisonUid, $fileTemplate): array
    {
        $info = [
            self::INFO_COMP_FOUND => 0,
            self::INFO_COMP_SYNCED => 0,
        ];
        $competition = $this->lookupCompetition($info, $saisonUid);
        if (!$competition) {
            return $info;
        }
        $info[self::INFO_COMP_SYNCED] = sprintf('%d (%s)', $competition->getUid(), $competition->getName());

        $fileName = $this->getFileName($fileTemplate, $competition);
        $info[self::INFO_COMP_FILE] = $fileName;

        try {
            $this->sync->doSync($competition, $fileName, $info);
        }
        catch (\Exception $e) {
            $this->updateSyncData($competition, false);
            throw $e;
        }

        $this->updateSyncData($competition);

        \tx_rnbase_util_Logger::warn(sprintf('Competition sync executed for UID %d', $competition->getUid()), 'dfbsync', $info);

        return $info;
    }

    private function updateSyncData(\tx_cfcleague_models_Competition $competition, $success = true): SyncData
    {
        $fields = [];
        $fields['SYNCDATA.COMPETITION'][OP_EQ_INT] = $competition->getUid();
        $data = $this->syncRepo->searchSingle($fields);
        if (!$data) {
            $data = new SyncData();
            $data->setProperty('competition', $competition->getUid());
            $data->setProperty('pid', $competition->getPid());
        }
        $data->setProperty('lastsync', \tx_rnbase_util_Dates::datetime_tstamp2mysql(time(), true));
        $data->setProperty('success', $success ? 1 : 0);
        $this->syncRepo->persist($data);
        return $data;
    }

    private function lookupCompetition(&$info, $saisonUid): ?\tx_cfcleague_models_Competition
    {
        // Suche nächsten Wettbewerb zum Sync
        $fields = $options = [];
        $fields['COMPETITION.SAISON'][OP_EQ_INT] = $saisonUid;
        $fields['COMPETITION.EXTID'][OP_NOTEQ] = '';
        $options['orderby']['DFBSYNC.lastsync'] = 'asc';
        $compSrv = \tx_cfcleague_util_ServiceRegistry::getCompetitionService();
        $comps = $compSrv->search($fields, $options);
        $info[self::INFO_COMP_FOUND] = count($comps);
        return !empty($comps) ? $comps[0] : null;

    }

    private function getFileName($fileTemplate, \tx_cfcleague_models_Competition $competition): string
    {
        $isAbs = \tx_rnbase_util_Files::isAbsPath($fileTemplate);
        $path = $isAbs ? $fileTemplate : PATH_site . $fileTemplate;
        return str_replace('${divisionIdentifier}', $competition->getProperty('extid'), $path);
    }
}
