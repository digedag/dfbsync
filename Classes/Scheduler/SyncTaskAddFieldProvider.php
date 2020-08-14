<?php
namespace System25\T3sports\DfbNet\Scheduler;

use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;


/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 René Nitzsche <rene@system25.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 */
class Tx_Dflsync_Scheduler_SyncTaskAddFieldProvider extends  AbstractAdditionalFieldProvider
{

    /**
     * Add a multi select box with all available cache backends.
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {

        $additionalFields = [];
        return $additionalFields;
    }

    /**
     * Save selected backends in task object
     *
     * @param array $submittedData Contains data submitted by the user
     * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the current task object
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {}

    /**
     *
     * @param array $submittedData Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array $submittedData, SchedulerModuleController $schedulerModule)
    {
        return true;
    }
}
