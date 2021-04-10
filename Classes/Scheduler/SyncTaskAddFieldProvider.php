<?php

namespace System25\T3sports\DfbSync\Scheduler;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

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

class SyncTaskAddFieldProvider extends AbstractAdditionalFieldProvider
{
    const FIELD_SAISON_UID = 'task_dfbsync_saison';
    const FIELD_FILE_MATCHTABLE = 'task_dfbsync_file_matchtable';
    const FIELD_FILE_RESULTS = 'task_dfbsync_file_results';

    /**
     * Add a multi select box with all available cache backends.
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     *
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];
        $additionalFields[self::FIELD_SAISON_UID] = $this->getSaisonField($taskInfo, $task, $schedulerModule);
        $additionalFields[self::FIELD_FILE_MATCHTABLE] = $this->getFileMatchtableField($taskInfo, $task, $schedulerModule);
        $additionalFields[self::FIELD_FILE_RESULTS] = $this->getFileResultsField($taskInfo, $task, $schedulerModule);

        return $additionalFields;
    }

    /**
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param SyncTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     *
     * @return array Array containing all the information pertaining to the additional fields
     */
    protected function getSaisonField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        $fields = $options = [];
        $saisons = [];
        foreach (\tx_cfcleague_util_ServiceRegistry::getSaisonService()->search($fields, $options) as $saison) {
            $saisons[$saison->getUid()] = $saison;
        }
        $options = [];
        // Add an empty option on top if an existing task is configured
        // with a table that can not be found in configuration anymore
        if ($task && !array_key_exists($task->getSaisonUid(), $saisons) && $currentSchedulerModuleAction->equals(Action::EDIT)) {
            $options[] = '<option value="" selected="selected"></option>';
        }
        foreach ($saisons as $uid => $saison) {
            if ($currentSchedulerModuleAction->equals(Action::ADD) && empty($options)) {
                // Select first table by default if adding a new task
                $options[] = '<option value="'.$uid.'" selected="selected">'.$saison->getProperty('name').'</option>';
            } elseif ($task && ($task->getSaisonUid() === $uid)) {
                // Select currently selected table
                $options[] = '<option value="'.$uid.'" selected="selected">'.$saison->getProperty('name').'</option>';
            } else {
                $options[] = '<option value="'.$uid.'">'.$saison->getProperty('name').'</option>';
            }
        }
        $fieldName = 'tx_scheduler['.self::FIELD_SAISON_UID.']';
        $fieldId = self::FIELD_SAISON_UID;
        $fieldHtml = [];
        // Add table drop down html
        $fieldHtml[] = '<select class="form-control" name="'.$fieldName.'" id="'.$fieldId.'">'.implode(LF, $options).'</select>';
        $fieldConfiguration = [
            'code' => implode(LF, $fieldHtml),
            'label' => 'LLL:EXT:dfbsync/Resources/Private/Language/locallang_db.xml:label_scheduler_saison',
            'cshKey' => '_MOD_system_txschedulerM1',
        ];

        return $fieldConfiguration;
    }

    /**
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param SyncTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     *
     * @return array Array containing all the information pertaining to the additional fields
     */
    protected function getFileMatchtableField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $fieldId = self::FIELD_FILE_MATCHTABLE;
        if (empty($taskInfo[$fieldId])) {
            $taskInfo[$fieldId] = $task && $task->getFileMatchtable() ? $task->getFileMatchtable() : '';
        }
        $fieldName = 'tx_scheduler['.$fieldId.']';
        $fieldHtml = '<input class="form-control" type="text" '.'name="'.$fieldName.'" '.'id="'.$fieldId.'" '.'value="'.$taskInfo[$fieldId].'" '.'size="30">';
        $fieldConfiguration = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:dfbsync/Resources/Private/Language/locallang_db.xml:label_scheduler_file_matchtable',
            'cshKey' => '_MOD_system_txschedulerM1',
        ];

        return $fieldConfiguration;
    }

    /**
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param SyncTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     *
     * @return array Array containing all the information pertaining to the additional fields
     */
    protected function getFileResultsField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $fieldId = self::FIELD_FILE_RESULTS;
        if (empty($taskInfo[$fieldId])) {
            $taskInfo[$fieldId] = $task && $task->getFileResults() ? $task->getFileResults() : '';
        }
        $fieldName = 'tx_scheduler['.$fieldId.']';
        $fieldHtml = '<input class="form-control" type="text" '.'name="'.$fieldName.'" '.'id="'.$fieldId.'" '.'value="'.$taskInfo[$fieldId].'" '.'size="30">';
        $fieldConfiguration = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:dfbsync/Resources/Private/Language/locallang_db.xml:label_scheduler_file_results',
            'cshKey' => '_MOD_system_txschedulerM1',
        ];

        return $fieldConfiguration;
    }

    /**
     * Save selected backends in task object.
     *
     * @param array $submittedData Contains data submitted by the user
     * @param SyncTask $task Reference to the current task object
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->setSaisonUid($submittedData[self::FIELD_SAISON_UID]);
        $task->setFileMatchtable($submittedData[self::FIELD_FILE_MATCHTABLE]);
        $task->setFileResults($submittedData[self::FIELD_FILE_RESULTS]);
    }

    /**
     * @param array $submittedData Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     *
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        return true;
    }

    /**
     * Returns an instance of LanguageService.
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
