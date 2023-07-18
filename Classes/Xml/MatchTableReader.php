<?php

namespace System25\T3sports\DfbSync\Xml;

use Sys25\RnBase\Utility\Logger;
use Sys25\RnBase\Utility\XmlElement;
use System25\T3sports\DfbSync\Model\Kopfdaten;
use System25\T3sports\DfbSync\Model\Paarung;
use System25\T3sports\DfbSync\Model\Team;

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
class MatchTableReader
{
    public const TAG = 'dfbsync';

    private $reader;
    private $kopfdaten;
    private $clubs = [];
    private $teams = [];
    private $matches = [];

    public function __construct($file)
    {
        $this->readHeader($this->createReader($file));
        $this->readClubs($this->createReader($file));
        $this->readTeams($this->createReader($file));
        $this->readMatches($this->createReader($file));
    }

    private function createReader($file)
    {
        $reader = new \XMLReader();
        if (!file_exists($file)) {
            Logger::fatal('Missing schedule xml file %s!', self::TAG, $file);
            throw new \Exception(sprintf('Missing XML file %s', $file));
        }
        if (!$reader->open($file, 'UTF-8', 0)) {
            Logger::fatal('Error reading match schedule xml string!', self::TAG, $file);
            throw new \Exception(sprintf('Error reading XML file %s', $file));
        }

        return $reader;
    }

    private function readMatches(\XMLReader $reader)
    {
        while ($reader->read() && 'paarung' !== $reader->name) {
        }
        while ('paarung' === $reader->name) {
            $paarung = new Paarung($this->expandNode($reader));
            $this->matches[$paarung->getId()] = $paarung;
            $reader->next('paarung');
        }
    }

    private function readClubs(\XMLReader $reader)
    {
        while ($reader->read() && 'verein' !== $reader->name) {
        }
        while ('verein' === $reader->name) {
            $node = $this->expandNode($reader);
            $teamId = $node->getValueFromPath('mannschaftId');
            $clubId = $node->getValueFromPath('id');
            $this->clubs[$teamId] = $clubId;
            $reader->next('verein');
        }
    }

    private function readTeams(\XMLReader $reader)
    {
        while ($reader->read() && 'paarung' !== $reader->name) {
        }
        while ('paarung' === $reader->name) {
            $node = $this->expandNode($reader);
            $homeId = $node->getValueFromPath('heimmannschaft.id');
            if (!array_key_exists($homeId, $this->teams)) {
                $clubId = isset($this->clubs[$homeId]) ? $this->clubs[$homeId] : null;
                $this->teams[$homeId] = new Team($homeId, $node->getValueFromPath('heimmannschaft.name'), $clubId);
            }
            $guestId = $node->getValueFromPath('gastmannschaft.id');
            if (!array_key_exists($guestId, $this->teams)) {
                $clubId = isset($this->clubs[$guestId]) ? $this->clubs[$guestId] : null;
                $this->teams[$guestId] = new Team($guestId, $node->getValueFromPath('gastmannschaft.name'), $clubId);
            }
            $reader->next('paarung');
        }
    }

    private function readHeader(\XMLReader $reader)
    {
        while ($reader->read() && 'kopfdaten' !== $reader->name) {
        }
        if ('kopfdaten' === $reader->name) {
            $node = $this->expandNode($reader);
            $this->kopfdaten = new Kopfdaten($node);
        }
    }

    public function getKopfdaten(): ?Kopfdaten
    {
        return $this->kopfdaten;
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @return Paarung[]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @param \XMLReader $reader
     *
     * @throws \LogicException
     *
     * @return XmlElement
     */
    private function expandNode(\XMLReader $reader): XmlElement
    {
        $doc = new \DOMDocument();
        $node = $reader->expand();
        if (false === $node || !$node instanceof \DOMNode) {
            throw new \LogicException('The current DOMNode is invalid. Last error: '.print_r(error_get_last(), true), 1452694257);
        }

        return simplexml_import_dom($doc->importNode($node, true), XmlElement::class);
    }
}
