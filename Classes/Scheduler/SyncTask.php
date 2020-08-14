<?php
namespace System25\T3sports\DfbSync\Scheduler;

use TYPO3\CMS\Scheduler\Task\AbstractTask;

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
class SyncTask extends AbstractTask
{
    private $saisonUid;
    private $fileMatchtable;

    /**
     * Function executed from the Scheduler.
     * Sends an email
     *
     * @return void
     */
    public function execute()
    {
        $success = true;

        return $success;
    }

    /**
     * @return mixed
     */
    public function getSaisonUid()
    {
        return $this->saisonUid;
    }

    /**
     * @return mixed
     */
    public function getFileMatchtable()
    {
        return $this->fileMatchtable;
    }

    /**
     * @param mixed $saisonUid
     */
    public function setSaisonUid($saisonUid)
    {
        $this->saisonUid = $saisonUid;
    }

    /**
     * @param mixed $fileMatchtable
     */
    public function setFileMatchtable($fileMatchtable)
    {
        $this->fileMatchtable = $fileMatchtable;
    }

}

