<?php

namespace System25\T3sports\DfbSync\Model\Search;

use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Misc;
use System25\T3sports\DfbSync\Model\SyncData;

/**
 * *************************************************************
 * Copyright notice.
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
class SyncDataSearch extends SearchBase
{
    protected function getBaseTable()
    {
        return 'tx_dfbsync_data';
    }

    protected function getBaseTableAlias()
    {
        return 'SYNCDATA';
    }

    protected function getTableMappings()
    {
        $tableMapping = [];
        $tableMapping['SYNCDATA'] = $this->getBaseTable();

        // Hook to append other tables
        Misc::callHook('dfbsync', 'search_SyncData_getTableMapping_hook', [
            'tableMapping' => &$tableMapping,
        ], $this);

        return $tableMapping;
    }

    public function getWrapperClass()
    {
        return SyncData::class;
    }

    protected function getJoins($tableAliases)
    {
        $join = [];

        // Hook to append other tables
        Misc::callHook('dfbsync', 'search_SyncData_getJoins_hook', [
            'join' => &$join,
            'tableAliases' => $tableAliases,
        ], $this);

        return $join;
    }
}
