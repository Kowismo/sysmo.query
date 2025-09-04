<?php
class votingStats {
    private $ignoredGroups = [];
    private $ignoredGroupsForAwards = [];
    private $ignoredUIDs = [];
    
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        // Debug-Log erstellen
        $logDir = '/home/query/logs/votingStats';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - votingStats processing started\n", FILE_APPEND);
        
        // Top Voters Channel aktualisieren
        if(isset($cfg['topVoters']) && $cfg['topVoters']['enabled']) {
            // Setze ignoredUIDs und ignoredGroups
            $this->ignoredUIDs = isset($cfg['topVoters']['ignoredUIDs']) ? $cfg['topVoters']['ignoredUIDs'] : [];
            $this->ignoredGroups = isset($cfg['ignoredGroups']) ? $cfg['ignoredGroups'] : [];
            $this->ignoredGroupsForAwards = isset($cfg['ignoredGroupsForAwards']) ? $cfg['ignoredGroupsForAwards'] : [];
            
            $this->updateTopVoters($ts, $cfg['topVoters'], $mongoDB, $ezApp, $logFile);
        }
        
        // Monthly Voters Channel aktualisieren
        if(isset($cfg['monthlyVoters']) && $cfg['monthlyVoters']['enabled']) {
            // Verwende die gleichen ignoredGroups wie bei topVoters
            $this->ignoredGroups = isset($cfg['ignoredGroups']) ? $cfg['ignoredGroups'] : [];
            $this->ignoredGroupsForAwards = isset($cfg['ignoredGroupsForAwards']) ? $cfg['ignoredGroupsForAwards'] : [];
            
            $this->updateMonthlyVoters($ts, $cfg['monthlyVoters'], $mongoDB, $ezApp, $logFile);
        }
        
        // Voting Overview Channel aktualisieren
        if(isset($cfg['votingOverview']) && $cfg['votingOverview']['enabled']) {
            $this->updateVotingOverview($ts, $cfg['votingOverview'], $mongoDB, $ezApp, $logFile);
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - votingStats processing finished\n", FILE_APPEND);
    }
    
    private function updateTopVoters($ts, $cfg, $mongoDB, $ezApp, $logFile) {
        $desc = $cfg['descriptions']['upHeader'];
        
        // Hole Top Voters aus der Datenbank
        $topVoters = $mongoDB->clientVotes->find(
            [], 
            ['sort' => ['totalVotes' => -1]]
        )->toArray();
        
        // Filtere nur ignoredUIDs heraus (nicht ignoredGroups - damit Admin sichtbar ist)
        $filteredVoters = [];
        foreach($topVoters as $voter) {
            // Überprüfe nur ignoredUIDs
            if(in_array($voter['clientUniqueIdentifier'], $this->ignoredUIDs)) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Filtered out voter by UID: " . $voter['clientNickname'] . "\n", FILE_APPEND);
                continue;
            }
            
            // NICHT mehr die Gruppen überprüfen - damit Admin sichtbar ist
            // if($this->isClientInIgnoredGroup($mongoDB, $voter['clientUniqueIdentifier'])) {
            //     continue;
            // }
            
            $filteredVoters[] = $voter;
        }
        
        // Limitiere auf recordsLimit
        $filteredVoters = array_slice($filteredVoters, 0, $cfg['recordsLimit']);
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Found " . count($filteredVoters) . " voters for top list (from " . count($topVoters) . " total)\n", FILE_APPEND);
        
        // KORRIGIERTE AWARD-LOGIK: Finde ersten ELIGIBLE User für Award
        if($cfg['awardsEnabled'] && isset($cfg['groupId']) && !empty($filteredVoters)) {
            $awardGiven = false;
            for($i = 0; $i < count($filteredVoters); $i++) {
                $voter = $filteredVoters[$i];
                
                // Prüfe ob dieser User für Awards eligible ist
                if(!$this->isClientInIgnoredGroup($mongoDB, $voter['clientUniqueIdentifier'], $this->ignoredGroupsForAwards)) {
                    // Dieser User bekommt den Award!
                    $this->updateAwardGroup($ts, $cfg['groupId'], $voter, $mongoDB, $logFile);
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Awarded top voter group to " . $voter['clientNickname'] . " (position " . ($i+1) . " in list)\n", FILE_APPEND);
                    $awardGiven = true;
                    break;
                } else {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Skipped award for " . $voter['clientNickname'] . " (position " . ($i+1) . ") - in ignored group for awards\n", FILE_APPEND);
                }
            }
            
            if(!$awardGiven) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - No eligible user found for top voter award\n", FILE_APPEND);
            }
        }
        
        if(empty($filteredVoters)) {
            $desc .= $cfg['descriptions']['noVoters'];
        } else {
            for($i = 0; $i < count($filteredVoters); $i++) {
                $voter = $filteredVoters[$i];
                
                // Zeile zur Beschreibung hinzufügen
                $clientLink = '[url=client://0/' . $voter['clientUniqueIdentifier'] . ']' . $voter['clientNickname'] . '[/url]';
                $desc .= str_replace(
                    ['%i%', '%clientId%', '%value%'], 
                    [$i+1, $clientLink, $voter['totalVotes']], 
                    $cfg['descriptions']['userLine']
                );
            }
        }
        
        $desc .= $cfg['descriptions']['downFooter'];
        
        // Channel-Beschreibung aktualisieren
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['channelId']));
        if(isset($channelInfo['channel_description']) && $channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['channelId'], ['channel_description' => $desc]);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Updated top voters channel description\n", FILE_APPEND);
        }
    }
    
    private function updateMonthlyVoters($ts, $cfg, $mongoDB, $ezApp, $logFile) {
        $currentMonth = date('Y-m');
        $startOfMonth = strtotime($currentMonth . '-01 00:00:00');
        
        $desc = $cfg['descriptions']['upHeader'];
        
        // UID-basierte monatliche Votes - löst Inkonsistenzen mit All-Time Listen
        $monthlyVotesByUID = [];
        
        // 1. Hole TeamSpeak-Servers.org Votes für diesen Monat
        $tsServersVotes = $mongoDB->teamspeakServersVotes->find([
            'month' => date('Y-m')
        ])->toArray();
        
        foreach($tsServersVotes as $vote) {
            $username = $vote['nickname'];
            $votes = intval($vote['votes']);
            
            // Finde UID für diesen Username
            $clientUID = $this->findClientUIDByUsername($mongoDB, $username);
            if($clientUID) {
                if(!isset($monthlyVotesByUID[$clientUID])) {
                    $monthlyVotesByUID[$clientUID] = [
                        'votes' => 0,
                        'nickname' => $username
                    ];
                }
                $monthlyVotesByUID[$clientUID]['votes'] += $votes;
            }
        }
        
        // 2. Hole TopG Votes vom aktuellen Monat
        $topgVotes = $mongoDB->votingData->aggregate([
            ['$match' => [
                'voteTime' => ['$gte' => $startOfMonth],
                'processed' => true,
                'source' => 'topg'
            ]],
            ['$group' => [
                '_id' => '$username',
                'votes' => ['$sum' => 1]
            ]]
        ])->toArray();
        
        foreach($topgVotes as $vote) {
            $username = $vote['_id'];
            $votes = $vote['votes'];
            
            // Finde UID für diesen Username
            $clientUID = $this->findClientUIDByUsername($mongoDB, $username);
            if($clientUID) {
                if(!isset($monthlyVotesByUID[$clientUID])) {
                    $monthlyVotesByUID[$clientUID] = [
                        'votes' => 0,
                        'nickname' => $username
                    ];
                }
                $monthlyVotesByUID[$clientUID]['votes'] += $votes;
            }
        }
        
        // Sortiere nach Votes
        uasort($monthlyVotesByUID, function($a, $b) {
            return $b['votes'] - $a['votes'];
        });
        
        // Limitiere auf recordsLimit
        $monthlyVotesByUID = array_slice($monthlyVotesByUID, 0, $cfg['recordsLimit'], true);
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Found " . count($monthlyVotesByUID) . " monthly voters (UID-based aggregation)\n", FILE_APPEND);
        
        // KORRIGIERTE AWARD-LOGIK: Finde ersten ELIGIBLE User für Monthly Award
        if($cfg['awardsEnabled'] && isset($cfg['groupId']) && !empty($monthlyVotesByUID)) {
            $position = 1;
            $awardGiven = false;
            
            foreach($monthlyVotesByUID as $clientUID => $data) {
                if(!$this->isClientInIgnoredGroup($mongoDB, $clientUID, $this->ignoredGroupsForAwards)) {
                    // Dieser User bekommt den Monthly Award!
                    $this->updateMonthlyAward($ts, $cfg['groupId'], $data['nickname'], $mongoDB, $logFile);
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Awarded monthly top voter group to " . $data['nickname'] . " (UID: $clientUID, position " . $position . " in list)\n", FILE_APPEND);
                    $awardGiven = true;
                    break;
                } else {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Skipped monthly award for " . $data['nickname'] . " (UID: $clientUID, position " . $position . ") - in ignored group for awards\n", FILE_APPEND);
                }
                $position++;
            }
            
            if(!$awardGiven) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - No eligible user found for monthly top voter award\n", FILE_APPEND);
            }
        }
        
        if(empty($monthlyVotesByUID)) {
            $desc .= $cfg['descriptions']['noVoters'];
        } else {
            $position = 1;
            
            foreach($monthlyVotesByUID as $clientUID => $data) {
                $clientLink = '[url=client://0/' . $clientUID . ']' . $data['nickname'] . '[/url]';
                
                $desc .= str_replace(
                    ['%i%', '%clientId%', '%value%'],
                    [$position, $clientLink, $data['votes']],
                    $cfg['descriptions']['userLine']
                );
                
                $position++;
            }
        }
        
        $desc .= $cfg['descriptions']['downFooter'];
        
        // Channel-Beschreibung aktualisieren
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['channelId']));
        if(isset($channelInfo['channel_description']) && $channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['channelId'], ['channel_description' => $desc]);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Updated monthly voters channel description\n", FILE_APPEND);
        }
    }
    
		private function updateVotingOverview($ts, $cfg, $mongoDB, $ezApp, $logFile) {
        $desc = $cfg['descriptions']['upHeader'];
        
        // Statistiken sammeln - OHNE IGNORIERTE BENUTZER (da ignoredGroups leer ist)
        $totalVotersData = $mongoDB->clientVotes->find()->toArray();
        $totalVotes = 0;
        $totalVoters = count($totalVotersData);
        
        foreach($totalVotersData as $voter) {
            $totalVotes += $voter['totalVotes'];
        }
        
        // Today's votes (nur aus votingData) - OHNE Filterung
        $todayVotes = $mongoDB->votingData->countDocuments([
            'voteTime' => ['$gte' => strtotime('today')],
            'processed' => true
        ]);
        
        // This month's votes - KOMBINIERT aus beiden Quellen, OHNE Filterung
        $thisMonthVotes = 0;
        
        // TeamSpeak-Servers.org Votes für diesen Monat
        $tsServersMonthly = $mongoDB->teamspeakServersVotes->find([
            'month' => date('Y-m')
        ])->toArray();
        
        foreach($tsServersMonthly as $voter) {
            $thisMonthVotes += intval($voter['votes']);
        }
        
        // TopG Votes für diesen Monat
        $thisMonthVotes += $mongoDB->votingData->countDocuments([
            'voteTime' => ['$gte' => strtotime(date('Y-m-01'))],
            'processed' => true,
            'source' => 'topg'
        ]);
        
        // Votes by Source - aus clientVotes aggregieren, OHNE Filterung
        $sourceStats = '';
        $votesBySource = [];
        
        // Sammle Votes aus clientVotes
        foreach($totalVotersData as $voter) {
            if(isset($voter['votesBySource'])) {
                foreach($voter['votesBySource'] as $source => $count) {
                    if(!isset($votesBySource[$source])) {
                        $votesBySource[$source] = 0;
                    }
                    $votesBySource[$source] += $count;
                }
            }
        }
        
        // Formatiere Source Stats
        foreach($votesBySource as $source => $count) {
            $sourceName = $source == 'teamspeak-servers' ? 'TeamSpeak-Servers.org' : 
                         ($source == 'topg' ? 'TopG.org' : $source);
            $sourceStats .= str_replace(
                ['%source%', '%count%'],
                [$sourceName, $count],
                $cfg['descriptions']['sourceLine']
            );
        }
        
        // Falls keine Votes by Source
        if(empty($sourceStats)) {
            $sourceStats = '• [color=#7be24c]No votes recorded yet[/color]\n';
        }
        
        // Top Voter des Monats - kombiniert, OHNE Filterung
        $monthlyVotesArray = [];
        
        // TeamSpeak-Servers Votes
        foreach($tsServersMonthly as $vote) {
            $username = $vote['nickname'];
            $votes = intval($vote['votes']);
            if(!isset($monthlyVotesArray[$username])) {
                $monthlyVotesArray[$username] = 0;
            }
            $monthlyVotesArray[$username] += $votes;
        }
        
        // TopG Votes
        $topgMonthly = $mongoDB->votingData->aggregate([
            ['$match' => [
                'voteTime' => ['$gte' => strtotime(date('Y-m-01'))],
                'processed' => true,
                'source' => 'topg'
            ]],
            ['$group' => [
                '_id' => '$username',
                'votes' => ['$sum' => 1]
            ]]
        ])->toArray();
        
        foreach($topgMonthly as $vote) {
            $username = $vote['_id'];
            if(!isset($monthlyVotesArray[$username])) {
                $monthlyVotesArray[$username] = 0;
            }
            $monthlyVotesArray[$username] += $vote['votes'];
        }
        
        // Finde Top Voter
        arsort($monthlyVotesArray);
        $topVoterName = !empty($monthlyVotesArray) ? array_key_first($monthlyVotesArray) : 'None';
        $topVoterVotes = !empty($monthlyVotesArray) ? reset($monthlyVotesArray) : 0;
        
        // Beschreibung zusammenbauen
        $desc .= str_replace(
            ['%totalVotes%', '%totalVoters%', '%todayVotes%', '%monthVotes%', '%topVoter%', '%topVoterVotes%', '%sourceStats%'],
            [$totalVotes, $totalVoters, $todayVotes, $thisMonthVotes, $topVoterName, $topVoterVotes, $sourceStats],
            $cfg['descriptions']['mainContent']
        );
        
        // WICHTIG: downFooter hinzufügen!
        $desc .= $cfg['descriptions']['downFooter'];
        
        // Channel-Beschreibung aktualisieren
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['channelId']));
        if(isset($channelInfo['channel_description']) && $channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['channelId'], ['channel_description' => $desc]);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Updated voting overview channel description\n", FILE_APPEND);
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Voting stats: Total=" . $totalVotes . ", Today=" . $todayVotes . ", Month=" . $thisMonthVotes . " (total voters: " . $totalVoters . ")\n", FILE_APPEND);
    }
    
    // GEÄNDERT: Funktion nimmt jetzt ignoredGroups als Parameter
    private function isClientInIgnoredGroup($mongoDB, $clientUID, $ignoredGroups = null) {
        // Falls keine ignoredGroups übergeben, verwende die Standard ignoredGroups
        if($ignoredGroups === null) {
            $ignoredGroups = $this->ignoredGroups;
        }
        
        if(empty($ignoredGroups) || empty($clientUID)) {
            return false;
        }
        
        $clientData = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $clientUID]);
        if(!$clientData || !isset($clientData['clientServergroups'])) {
            return false;
        }
        
        $clientGroups = explode(',', $clientData['clientServergroups']);
        
        foreach($ignoredGroups as $ignoredGroup) {
            if(in_array($ignoredGroup, $clientGroups)) {
                return true;
            }
        }
        
        return false;
    }
    
    // NEUE FUNKTION: Finde Client UID über Username
    private function findClientUIDByUsername($mongoDB, $username) {
        // Suche zuerst in clientVotes
        $clientData = $mongoDB->clientVotes->findOne(['clientNickname' => $username]);
        if($clientData && isset($clientData['clientUniqueIdentifier'])) {
            return $clientData['clientUniqueIdentifier'];
        }
        
        // Suche in serverClients
        $clientData = $mongoDB->serverClients->findOne(['clientNickname' => $username]);
        if($clientData && isset($clientData['clientUniqueIdentifier'])) {
            return $clientData['clientUniqueIdentifier'];
        }
        
        // Case-insensitive Suche
        $clientData = $mongoDB->serverClients->findOne([
            'clientNickname' => new MongoDB\BSON\Regex('^' . preg_quote($username, '/') . '$', 'i')
        ]);
        if($clientData && isset($clientData['clientUniqueIdentifier'])) {
            return $clientData['clientUniqueIdentifier'];
        }
        
        return null;
    }
    
    private function updateAwardGroup($ts, $groupId, $winner, $mongoDB, $logFile) {
        // Entferne Award von allen anderen
        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($groupId, $names = true));
        if(is_array($serverGroupClientList)) {
            foreach($serverGroupClientList as $client) {
                if(isset($client['cldbid']) && $client['cldbid'] != $winner['clientDatabaseId']) {
                    $ts->serverGroupDeleteClient($groupId, $client['cldbid']);
                }
            }
        }
        
        // Füge Award zum aktuellen Gewinner hinzu (wenn nicht schon vorhanden)
        $hasGroup = false;
        if(is_array($serverGroupClientList)) {
            foreach($serverGroupClientList as $client) {
                if(isset($client['cldbid']) && $client['cldbid'] == $winner['clientDatabaseId']) {
                    $hasGroup = true;
                    break;
                }
            }
        }
        
        if(!$hasGroup) {
            // Hole die Client Database ID vom Winner
            $clientData = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $winner['clientUniqueIdentifier']]);
            if($clientData && isset($clientData['clientDatabaseId'])) {
                $ts->serverGroupAddClient($groupId, $clientData['clientDatabaseId']);
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Awarded top voter group to " . $winner['clientNickname'] . "\n", FILE_APPEND);
            }
        }
    }
    
    private function updateMonthlyAward($ts, $groupId, $winnerName, $mongoDB, $logFile) {
        // Finde den Winner
        $winnerData = $mongoDB->clientVotes->findOne(['clientNickname' => $winnerName]);
        if(!$winnerData) {
            $winnerData = $mongoDB->serverClients->findOne(['clientNickname' => $winnerName]);
        }
        
        if(!$winnerData || !isset($winnerData['clientDatabaseId'])) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Could not find database ID for monthly winner: " . $winnerName . "\n", FILE_APPEND);
            return;
        }
        
        // Entferne Award von allen anderen
        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($groupId, $names = true));
        if(is_array($serverGroupClientList)) {
            foreach($serverGroupClientList as $client) {
                if(isset($client['cldbid']) && $client['cldbid'] != $winnerData['clientDatabaseId']) {
                    $ts->serverGroupDeleteClient($groupId, $client['cldbid']);
                }
            }
        }
        
        // Füge Award zum Winner hinzu
        $hasGroup = false;
        if(is_array($serverGroupClientList)) {
            foreach($serverGroupClientList as $client) {
                if(isset($client['cldbid']) && $client['cldbid'] == $winnerData['clientDatabaseId']) {
                    $hasGroup = true;
                    break;
                }
            }
        }
        
        if(!$hasGroup) {
            $ts->serverGroupAddClient($groupId, $winnerData['clientDatabaseId']);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Awarded monthly top voter group to " . $winnerName . "\n", FILE_APPEND);
        }
    }
}
?>