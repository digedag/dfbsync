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
class Team
{

    private $id;

    private $clubId;

    private $name;

    private $noMatch;

    public function __construct($id, $name, $clubId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->clubId = $clubId;
        $this->noMatch = $id ? false : true;
    }

    /**
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     *
     * @return mixed
     */
    public function getClubId(): string
    {
        return $this->clubId;
    }

    /**
     *
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function isNoMatch(): bool
    {
        return $this->noMatch;
    }
}
