<?php
namespace System25\T3sports\DfbSync\Sync;


use System25\T3sports\DfbSync\Xml\MatchTableReader;
use System25\T3sports\DfbSync\Model\Paarung;
use System25\T3sports\DfbSync\Model\Team;

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
class CompetitionSync
{
    const TABLE_GAMES = 'tx_cfcleague_games';
    const TABLE_TEAMS = 'tx_cfcleague_teams';
    const TABLE_COMPETITION = 'tx_cfcleague_competition';

    /**
     * Key ist DFB-ID, value ist T3-UID
     */
    private $teamMap = [];

    /**
     * Key ist DFB-ID, value ist T3-UID
     */
    private $matchMap = [];
    private $pageUid = 0;
    private $stats = [];
    private $xmlReader;

    public function doSync($competition, $fileName, &$info)
    {
        $this->xmlReader = new MatchTableReader($fileName);

        $this->pageUid = $competition->getProperty('pid');
        $this->initMatches($competition);

        $start = microtime(TRUE);
        $cnt = 0;
        $syncInfo = [
            'match' => [
                'new' => 0,
                'updated' => 0
            ],
            'team' => [
                'new' => 0,
                'updated' => 0
            ],
        ];
        $data = [
            self::TABLE_TEAMS => [],
            self::TABLE_GAMES => [],
            self::TABLE_COMPETITION => [],
        ];
        foreach ($this->xmlReader->getMatches() as $paarung) {
            $cnt ++;
            try {
                $this->handleMatch($data, $paarung, $competition, $syncInfo);
                if ($cnt % 40 == 0) {
                    // Speichern
                    $this->persist($data);
                    // Wettbewerb neu laden, da ggf. neue Teams drin stehen
                    $competition->reset();
                }
            } catch (\Exception $e) {
                \tx_rnbase_util_Logger::fatal('Error handle match!', 'dfbsync', [
                    'msg' => $e->getMessage()
                ]);
            }
        }

        // Die restlichen Spiele speichern
        $this->persist($data);
        $this->stats['total']['time'] = intval(microtime(true) - $start) . 's';
        $this->stats['total']['matches'] = $cnt;

        $info['syncinfo'] = $syncInfo;
        $info['syncstats'] = $this->stats;
    }

    /**
     *
     * @param array $data
     * @param Paarung $paarung
     * @param \tx_cfcleague_models_Competition $competition
     * @param string $dflCompetitionId
     * @param array $info
     */
    private function handleMatch(&$data, Paarung $paarung, $competition, &$info)
    {
        // Das Spiel suchen und ggf. anlegen
        $extId = $paarung->getId();
        $matchUid = 'NEW_' . $extId;
        if (array_key_exists($extId, $this->matchMap)) {
            $matchUid = $this->matchMap[$extId];
            $info['match']['updated'] += 1;
        } else {
            $info['match']['new'] += 1;
        }

        $data[self::TABLE_GAMES][$matchUid]['pid'] = $this->pageUid;
        $data[self::TABLE_GAMES][$matchUid]['extid'] = $extId;
        $data[self::TABLE_GAMES][$matchUid]['competition'] = $competition->getUid();
        $data[self::TABLE_GAMES][$matchUid]['round'] = $paarung->getSpieltag();
        $data[self::TABLE_GAMES][$matchUid]['round_name'] = $paarung->getSpieltag() . '. Spieltag';
        // Es muss ein lokaler Timestamp gesetzt werden
        $kickoff = $paarung->getDatum();
        $data[self::TABLE_GAMES][$matchUid]['date'] = ($kickoff->getTimestamp() + $kickoff->getOffset());
        $data[self::TABLE_GAMES][$matchUid]['stadium'] = $paarung->getStadionName();
        $data[self::TABLE_GAMES][$matchUid]['home'] = $this->findTeam($paarung->getHeim(), $data, $competition);
        $data[self::TABLE_GAMES][$matchUid]['guest'] = $this->findTeam($paarung->getGast(), $data, $competition);
        $data[self::TABLE_GAMES][$matchUid]['status'] = $this->getMatchStatus($paarung);
        $data[self::TABLE_GAMES][$matchUid]['goals_home_2'] = $paarung->getToreHeim();
        $data[self::TABLE_GAMES][$matchUid]['goals_guest_2'] = $paarung->getToreGast();

    }

    /**
     * Liefert die UID des Teams, oder einen NEW_-Key
     *
     * @param string $dflId
     * @param [] $data
     * @param \tx_cfcleague_models_Competition $competition
     * @return string
     */
    private function findTeam($extId, &$data, $competition)
    {
        $uid = 'NEW_' . $extId;
        if (! array_key_exists($extId, $this->teamMap)) {
            // Das Team ist noch nicht im Cache, also in der DB suchen
            /* @var $teamSrv \tx_cfcleague_services_Teams */
            $teamSrv = \tx_cfcleague_util_ServiceRegistry::getTeamService();
            $fields = [];
            $fields['TEAM.EXTID'][OP_EQ_NOCASE] = $extId;
            $fields['TEAM.PID'][OP_EQ_INT] = $competition->getPid();

            $options = ['what' => 'uid',];
            $ret = $teamSrv->searchTeams($fields, $options);
            if (! empty($ret)) {
                $this->teamMap[$extId] = $ret[0]['uid'];
                $uid = $this->teamMap[$extId];
            } else {
                // In uid steht jetzt NEW_
                // Team anlegen, falls es noch nicht in der Data-Map liegt
                if (! array_key_exists($uid, $data[self::TABLE_TEAMS])) {
                    $data[self::TABLE_TEAMS][$uid] = $this->loadTeamData($extId);
                }
            }
            // Sicherstellen, daß das Team im Wettbewerb ist
            $this->addTeamToCompetition($uid, $data, $competition);
        } else {
            $uid = $this->teamMap[$extId];
        }
        return $uid;
    }

    /**
     * Stellt sicher, daß das Team im Wettbewerb gespeichert wird.
     * Hier gibt es aber noch ein Todo: es wird nicht geprüft, ob die neue ID schon
     * in den TCE-Data liegt. Dadurch wird so mehrfach hinzugefügt. Das hat aber praktisch
     * keine Auswirkung, da die TCE das selbst korrigiert. Das könnte sich zukünftig aber
     * ändern...
     *
     * @param mixed $teamUid
     * @param array $data
     * @param \tx_cfcleague_models_Competition $competition
     */
    private function addTeamToCompetition($teamUid, &$data, $competition)
    {
        $add = TRUE;
        if ($competition->getProperty('teams')) {
            $teamUids = array_flip(\Tx_Rnbase_Utility_Strings::trimExplode(',', $competition->getProperty('teams')));
            $add = ! (array_key_exists($teamUid, $teamUids));
        }
        if (! $add) {
            return;
        }
        // Das geht bestimmt auch kürzer...
        // Das Team in den Wettbewerb legen
        if (isset($data[self::TABLE_COMPETITION][$competition->getUid()]['teams'])) {
            $data[self::TABLE_COMPETITION][$competition->getUid()]['teams'] .= ',' . $teamUid;
        } else {
            // Das erste Team
            if ($competition->getProperty('teams')) {
                $data[self::TABLE_COMPETITION][$competition->getUid()]['teams'] = $competition->getProperty('teams');
                $data[self::TABLE_COMPETITION][$competition->getUid()]['teams'] .= ',' . $teamUid;
            }
            else {
                $data[self::TABLE_COMPETITION][$competition->getUid()]['teams'] = $teamUid;
            }
        }
    }

    private function loadTeamData($extId)
    {
        if (array_key_exists($extId, $this->xmlReader->getTeams())) {
            /* @var $team Team */
            $team = $this->xmlReader->getTeams()[$extId];
            return [
                'pid' => $this->pageUid,
                'extid' => $extId,
                'club' => $this->lookupClubUid($team),
                'name' => $team->getName(),
                'short_name' => $team->getName(),
            ];
        }
        throw new \Exception('Team not found: ' . $extId);
    }

    private function lookupClubUid(Team $team)
    {
        $clubUid = 0;
        if ($team->getClubId()) {
            /* @var $matchSrv \tx_cfcleague_services_Teams */
            $clubSrv = \tx_cfcleague_util_ServiceRegistry::getTeamService();
            $fields = $options = [];
            $fields['CLUB.EXTID'][OP_EQ] = $team->getClubId();
            $rows = $clubSrv->searchClubs($fields, $options);
            if (!empty($rows)) {
                $clubUid = $rows[0]->getUid();
            }
        }
        return $clubUid;
    }

    private function getMatchStatus(Paarung $paarung)
    {
        $dfbStatus = $paarung->getStatus();
        $t3Status = \tx_cfcleague_models_Match::MATCH_STATUS_OPEN;

        if ($dfbStatus >= 600) {
            $t3Status = \tx_cfcleague_models_Match::MATCH_STATUS_FINISHED;
        }
        elseif ($dfbStatus >= 500) {
            $t3Status = \tx_cfcleague_models_Match::MATCH_STATUS_INVALID;
        }

        return $t3Status;
    }

    private function persist(&$data)
    {
        $start = microtime(TRUE);

        $tce = \Tx_Rnbase_Database_Connection::getInstance()->getTCEmain($data);
        $tce->process_datamap();

        $this->stats['chunks'][] = [
            'time' => intval(microtime(true) - $start) . 's',
            'matches' => count($data[self::TABLE_GAMES]),
        ];

        $data[self::TABLE_TEAMS] = [];
        $data[self::TABLE_GAMES] = [];
        $data[self::TABLE_COMPETITION] = [];
    }

    /**
     * Lädt die vorhandenen Spiele des Wettbewerbs in die matchMap
     *
     * @param \tx_cfcleague_models_Competition $competition
     */
    private function initMatches(\tx_cfcleague_models_Competition $competition)
    {
        $fields = $options = [];
        /* @var $matchSrv \tx_cfcleague_services_Match */
        $matchSrv = \tx_cfcleague_util_ServiceRegistry::getMatchService();
        $fields['MATCH.COMPETITION'][OP_EQ_INT] = $competition->getUid();
        $options['what'] = 'uid,extid';
        $options['orderby'] = 'uid asc';
        $options['callback'] = [
            $this,
            'cbAddMatch'
        ];
        $matchSrv->search($fields, $options);
    }

    public function cbAddMatch($record)
    {
        $this->matchMap[$record['extid']] = $record['uid'];
    }
}