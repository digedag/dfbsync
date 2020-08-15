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

class Kopfdaten
{
    private $staffelKennung;
    private $staffelId;
    private $staffelName;
    private $wettkampfId;
    private $wettkampfTypId;
    private $wettkampfTyp;
    private $wettkampfName;
    private $anzahlMannschaften;
    private $anzahlPaarungen;

    public function __construct(\tx_rnbase_util_XmlElement $node)
    {
        $this->parse($node);
    }

    private function parse(\tx_rnbase_util_XmlElement $node)
    {
        $this->staffelId = $node->getValueFromPath('staffel.id');
        $this->staffelKennung = $node->getValueFromPath('staffel.kennung');
        $this->staffelName = $node->getValueFromPath('staffel.name');
        $this->wettkampfId = $node->getValueFromPath('wettkampf.id');
        $this->wettkampfName = $node->getValueFromPath('wettkampf.name');
        $this->wettkampfTyp = $node->getValueFromPath('wettkampf.typ');
        $this->wettkampfTypId = $node->getValueFromPath('wettkampf.typ.id');
        $this->anzahlMannschaften = $node->getValueFromPath('anzahlMannschaften');
        $this->anzahlPaarungen = $node->getValueFromPath('anzahlPaarungen');
    }

    /**
     * @return string
     */
    public function getStaffelKennung() : string
    {
        return $this->staffelKennung;
    }

    /**
     * @return string
     */
    public function getStaffelId() : string
    {
        return $this->staffelId;
    }

    /**
     * @return mixed
     */
    public function getStaffelName() : string
    {
        return $this->staffelName;
    }

    /**
     * @return mixed
     */
    public function getWettkampfId() : string
    {
        return $this->wettkampfId;
    }

    /**
     * @return mixed
     */
    public function getWettkampfTypId() : string
    {
        return $this->wettkampfTypId;
    }

    /**
     * @return mixed
     */
    public function getWettkampfTyp() : string
    {
        return $this->wettkampfTyp;
    }

    /**
     * @return mixed
     */
    public function getWettkampfName() : string
    {
        return $this->wettkampfName;
    }

    /**
     * @return mixed
     */
    public function getAnzahlMannschaften() : int
    {
        return $this->anzahlMannschaften;
    }

    /**
     * @return mixed
     */
    public function getAnzahlPaarungen() : int
    {
        return $this->anzahlPaarungen;
    }

}