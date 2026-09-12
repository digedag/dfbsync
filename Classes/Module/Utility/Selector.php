<?php

namespace System25\T3sports\DfbSync\Module\Utility;

use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Utility\Misc;
use System25\T3sports\DfbSync\Scheduler\SyncTask;
use Throwable;
use tx_rnbase;
use TYPO3\CMS\Scheduler\Domain\Repository\SchedulerTaskRepository;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2026 Rene Nitzsche <rene@system25.de>
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
 * Module function.
 *
 * @author	Rene Nitzsche <rene@system25.de>
 */
class Selector
{
    private $doc;

    private $MCONF;

    private $formTool;

    private $modName;
    private $module;

    public function __construct(private SchedulerTaskRepository $taskRepo)
    {
    }

    /**
     * Initialisiert das Objekt mit dem Template und der Modul-Config.
     */
    public function init($doc, IModule $module): void
    {
        $this->doc = $doc;
        $this->MCONF['name'] = $module->getName(); // deprecated
        $this->modName = $module->getName();
        $this->module = $module;
        $this->formTool = tx_rnbase::makeInstance(ToolBox::class);
        $this->formTool->init($this->doc, $module);
        Misc::prepareTSFE();
    }

    public function showSchedulerSelector(&$content, $pid, $games = 0)
    {
        $tasks = $this->getDfbSyncTasks();
        if (!$tasks) {
            $content .= '<p>Keine DFB-Scheduler-Tasks konfiguriert.</p>';

            return 0;
        }

        $entries = [];
        foreach ($tasks as $uid => $task) {
            $description = $task->getDescription();
            $entries[$uid] = $description ?: 'DFB-Sync-Task '.$uid;
        }

        $menuData = $this->getFormTool()->showMenu($pid, 'dfbsyncTask', $this->modName, $entries);
        $content .= $menuData['menu'];

        return $menuData['value'] ? $tasks[$menuData['value']] : 0;
    }

    /**
     * Returns all configured DFB sync tasks, including disabled tasks.
     *
     * @return SyncTask[] Indexed by scheduler task UID
     */
    public function getDfbSyncTasks(): array
    {
        $tasks = [];
        $groupedTasks = $this->taskRepo->getGroupedTasks();
        foreach ($groupedTasks['taskGroupsWithTasks'] ?? [] as $taskGroup) {
            foreach ($taskGroup['tasks'] as $taskData) {
                if (SyncTask::class !== ($taskData['class'] ?? null)) {
                    continue;
                }

                try {
                    $task = $this->taskRepo->findByUid((int) $taskData['uid']);
                } catch (Throwable) {
                    continue;
                }

                if ($task instanceof SyncTask) {
                    $tasks[(int) $taskData['uid']] = $task;
                }
            }
        }

        return $tasks;
    }

    private function getFormTool(): ToolBox
    {
        return $this->formTool;
    }
}
