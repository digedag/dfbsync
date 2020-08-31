<?php
namespace System25\T3sports\DfbSync\Model;

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

class Paarung
{
    private $id;
    private $nummer;
    private $spieltag;
    private $status;
    private $datum;
    private $stadionName;
    private $heim;
    private $gast;
    private $toreHeim;
    private $toreGast;

    public function __construct(\tx_rnbase_util_XmlElement $node)
    {
        $this->parse($node);
    }

    private function parse(\tx_rnbase_util_XmlElement $node)
    {
        $this->id = $node->getValueFromPath('spiel.id');
        $this->nummer = $node->getIntFromPath('spiel.nummer');
        $this->spieltag = $node->getIntFromPath('spieltag');
        $this->status = $node->getIntFromPath('spiel.status.id');
        $tag = $node->getValueFromPath('spiel.datum');
        $zeit = $node->getValueFromPath('spiel.uhrzeit');
        $this->datum = new \DateTime($tag .' '.$zeit);
        $this->stadionName = $node->getValueFromPath('spielstaette.name');
        $this->heim = $node->getValueFromPath('heimmannschaft.id');
        $this->gast = $node->getValueFromPath('gastmannschaft.id');
        $this->toreHeim = $node->getValueFromPath('ergebnis.heim');
        $this->toreGast = $node->getValueFromPath('ergebnis.gast');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return number
     */
    public function getNummer()
    {
        return $this->nummer;
    }

    /**
     * @return number
     */
    public function getSpieltag()
    {
        return $this->spieltag;
    }

    /**
     * @return number
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getDatum()
    {
        return $this->datum;
    }

    /**
     * @return string
     */
    public function getStadionName()
    {
        return $this->stadionName;
    }

    /**
     * @return string
     */
    public function getHeim()
    {
        return $this->heim;
    }

    /**
     * @return string
     */
    public function getGast()
    {
        return $this->gast;
    }

    /**
     * @return string
     */
    public function getToreHeim()
    {
        return $this->toreHeim;
    }

    /**
     * @return string
     */
    public function getToreGast()
    {
        return $this->toreGast;
    }
}
