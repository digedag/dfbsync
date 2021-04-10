<?php

namespace System25\T3sports\DfbSync\Tests\Scheduler;

use System25\T3sports\DfbSync\Model\Kopfdaten;
use System25\T3sports\DfbSync\Model\Paarung;
use System25\T3sports\DfbSync\Model\Team;
use System25\T3sports\DfbSync\Xml\MatchTableReader;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 Rene Nitzsche (rene@system25.de)
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

class MatchTableReaderTest extends \tx_rnbase_tests_BaseTestCase
{
    private $xmlFile;

    protected function setUp()
    {
        $this->xmlFile = $this->getFixturePath('spielplan.xml');
    }

    /**
     * @group unit
     */
    public function testReaderMatches()
    {
        $reader = new MatchTableReader($this->xmlFile);

        $matches = $reader->getMatches();
        $this->assertCount(306, $matches);

        /* @var $match Paarung */
        $match = $matches[0];
        $this->assertEquals('0208HMMHAS000000VS54898DVUVCCN5J', $match->getId());
        $this->assertEquals('A-Stadion', $match->getStadionName());
        $this->assertEquals(8, $match->getNummer());
        $this->assertEquals(600, $match->getStatus());
    }

    /**
     * @group unit
     */
    public function testReaderTeams()
    {
        $reader = new MatchTableReader($this->xmlFile);

        $teams = $reader->getTeams();
        $this->assertCount(18, $teams);
        /* @var $team Paarung */
        $team = $teams['011MAI8MVC000000VTVG0001ABC8C1K7'];
        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals('011MAI8MVC000000VTVG0001ABC8C1K7', $team->getId(), 'team id is wrong');
        $this->assertEquals('FC Team A', $team->getName());
        $this->assertEquals('00ES8GNBC800007UVV0AG08LVUPGND5I', $team->getClubId(), 'club id is wrong');
    }

    /**
     * @group unit
     */
    public function testReaderKopfdaten()
    {
        $reader = new MatchTableReader($this->xmlFile);

        $kopfdaten = $reader->getKopfdaten();
        $this->assertInstanceOf(Kopfdaten::class, $kopfdaten);
        $this->assertEquals('01TM7AT5S5000000VS67890ABCD90M3P', $kopfdaten->getStaffelId());
        $this->assertEquals('890022', $kopfdaten->getStaffelKennung());
        $this->assertEquals('Testliga', $kopfdaten->getStaffelName());
        $this->assertEquals('01TM7AT5S5000000VS67890ABCD90M3S', $kopfdaten->getWettkampfId());
        $this->assertEquals('Testliga', $kopfdaten->getWettkampfName());
        $this->assertEquals('Meisterschaft', $kopfdaten->getWettkampfTyp());
        $this->assertEquals('1', $kopfdaten->getWettkampfTypId());
        $this->assertEquals('1', $kopfdaten->getWettkampfTypId());
        $this->assertEquals(18, $kopfdaten->getAnzahlMannschaften());
        $this->assertEquals(306, $kopfdaten->getAnzahlPaarungen());
    }

    /**
     * @group unit
     * @expectedException \Exception
     */
    public function testReaderWidthMissingFile()
    {
        $xmlFile = $this->getFixturePath('spielplan_missing.xml');
        $reader = new MatchTableReader($xmlFile);
        $this->assertInstanceOf(Kopfdaten::class, $reader->getKopfdaten());
    }

    private function getFixturePath($filename)
    {
        return \tx_rnbase_util_Extensions::extPath('dfbsync').'Tests/Fixtures/'.$filename;
    }
}
