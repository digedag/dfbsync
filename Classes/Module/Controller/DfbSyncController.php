<?php

namespace System25\T3sports\DfbSync\Module\Controller;

use Sys25\RnBase\Backend\Module\BaseModFunc;
use Sys25\RnBase\Backend\Utility\Tables;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Utility\Files;
use System25\T3sports\DfbSync\Module\Utility\Selector;
use System25\T3sports\DfbSync\Xml\MatchTableReader;
use System25\T3sports\Model\Repository\CompetitionRepository;
use System25\T3sports\Model\Saison;
use Throwable;

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
class DfbSyncController extends BaseModFunc
{
    public function __construct(
        private Selector $selector,
        private Tables $tables,
        private CompetitionRepository $compRepo
    ) {
    }

    protected function getFuncId()
    {
        return 'funcdfbsync';
    }

    public function getModuleIdentifier()
    {
        return 'cfc_league';
    }

    protected function getContent($template, &$configurations, &$formatter, $formTool)
    {
        $this->selector->init($this->getModule()->getDoc(), $this->getModule());
        $commonStart = Templates::getSubpart($template, '###COMMON_START###');
        $commonEnd = Templates::getSubpart($template, '###COMMON_END###');
        $content = '';
        $selectorContent = '';
        $currentTask = $this->selector->showSchedulerSelector($selectorContent, $this->getModule()->getPid());
        if (!$currentTask) {
            return $this->getModule()->getDoc()->section(
                '###LABEL_INFO###:',
                '###LABEL_no_scheduler_found###',
                0,
                1,
                self::ICON_INFO
            );
        }
        $this->getModule()->setSelector($selectorContent);

        $fileMatchTable = $currentTask->getFileMatchtable();
        $fileResults = $currentTask->getFileResults();
        $saisonUid = (int) $currentTask->getSaisonUid();
        if ($saisonUid) {
            $saison = new Saison($saisonUid);
            $content .= '<p>Saison: '.$saison->getName().'</p>';
        }

        $content .= $this->renderFileOverview($fileMatchTable, (int) $currentTask->getSaisonUid());

        $out = $commonStart;
        $out .= $content;
        $out .= $commonEnd;

        return $out;
    }

    private function renderFileOverview(string $template, int $saisonUid): string
    {
        $data = [[
            'Datei',
            'DFB-Identifier',
            'Wettbewerb',
            'Vereine',
            'Status',
            'XML-Infos',
        ]];
        foreach ($this->findFiles($template) as $fileData) {
            $data[] = $this->renderFileRow($fileData['file'], $fileData['identifier'], $saisonUid);
        }

        if (1 === count($data)) {
            return '<p>Keine Dateien für das konfigurierte Spielplanmuster gefunden.</p>';
        }

        return $this->tables->buildTable($data, [
            'headRows' => [0],
            'table' => ['<table class="table table-striped table-hover table-condensed">', '</table><br/>'],
            '0' => [
                'tr' => ['<tr>', '</tr>'],
                'defCol' => ['<th>', '</th>'],
            ],
            'defRow' => [
                'tr' => ['<tr>', '</tr>'],
                'defCol' => ['<td>', '</td>'],
            ],
        ]);
    }

    /**
     * Finds files matching the configured template and extracts the placeholder value.
     *
     * @return array<int, array{file: string, identifier: string}>
     */
    private function findFiles(string $template): array
    {
        if (!$template || !str_contains($template, '${divisionIdentifier}')) {
            return [];
        }

        $basePath = Files::isAbsPath($template)
            ? $template
            : \TYPO3\CMS\Core\Core\Environment::getVarPath().'/'.$template;
        $placeholder = '${divisionIdentifier}';
        $glob = str_replace($placeholder, '*', $basePath);
        $pattern = '#^'.str_replace(
            preg_quote($placeholder, '#'), '([^/]+)', preg_quote($basePath, '#')
        ).'$#';
        $files = [];

        foreach (glob($glob) ?: [] as $file) {
            if (!is_file($file) || !preg_match($pattern, $file, $matches)) {
                continue;
            }
            $files[] = ['file' => $file, 'identifier' => $matches[1]];
        }

        return $files;
    }

    private function renderFileRow(string $file, string $identifier, int $saisonUid): array
    {
        $escapedFile = htmlspecialchars(basename($file), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedPath = htmlspecialchars($file, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $escapedIdentifier = htmlspecialchars($identifier, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $competition = $this->findCompetition($identifier, $saisonUid);
        $info = 'Größe: '.number_format(filesize($file), 0, ',', '.').' Byte<br>geändert: '.date('Y-m-d H:i:s', filemtime($file));
        $clubs = [];
        try {
            $reader = new MatchTableReader($file);
            $header = $reader->getKopfdaten();
            foreach ($reader->getClubNames() as $clubName) {
                if ('' !== $clubName) {
                    $clubs[] = htmlspecialchars($clubName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            }
            $clubs = array_values(array_unique($clubs));
            if ($header) {
                $info .= '<br>'.htmlspecialchars($header->getStaffelName().' / '.$header->getWettkampfName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $info .= '<br>Mannschaften: '.$header->getAnzahlMannschaften().'<br>Paarungen: '.$header->getAnzahlPaarungen();
            }
            $status = 'OK';
        } catch (Throwable $exception) {
            $status = 'XML-Fehler: '.htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return [
            '<span title="'.$escapedPath.'">'.$escapedFile.'</span>',
            $escapedIdentifier,
            $competition,
            $clubs ? implode('<br>', $clubs) : '<span class="text-muted">Keine Vereinsdaten</span>',
            $status,
            $info,
        ];
    }

    private function findCompetition(string $identifier, int $saisonUid): string
    {
        $fields = [];
        $fields['COMPETITION.EXTID'][OP_EQ_NOCASE] = $identifier;
        $fields['COMPETITION.SAISON'][OP_EQ_INT] = $saisonUid;
        $competitions = $this->compRepo->search($fields, []);
        $names = [];

        foreach ($competitions as $competition) {
            $editLink = $this->getModule()->getFormTool()->createEditLink(
                'tx_cfcleague_competition',
                $competition->getUid(),
                ''
            );
            $names[] = htmlspecialchars($competition->getName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').' '.$editLink;
        }

        return $names ? implode('<br>', $names) : '<span class="text-muted">Keine Zuordnung</span>';
    }
}
