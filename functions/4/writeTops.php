<?php
class writeTops {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        # connections

        $desc = $cfg['connections']['descriptions']['upHeader'];
        $connections = $mongoDB->serverClients->find([], ['sort' => ['connections' => -1], 'limit' => $cfg['connections']['recordsLimit']])->toArray();
        for($i = 0; $i < $cfg['connections']['recordsLimit']; $i++) {
            if(isset($connections[$i])) {
                if($cfg['connections']['awardsEnabled']) {
                    if($i == 0) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['connections']['groupId'], $names = true));
                        if(!is_array($serverGroupClientList)) {
                            $serverGroupClientList = [];
                        }
                        $continue = false;
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid'])) {
                                if($connections[$i]['clientDatabaseId'] != $c['cldbid']) {
                                    $ts->serverGroupDeleteClient($cfg['connections']['groupId'], $c['cldbid']);
                                } else {
                                    $continue = true;
                                }
                            }
                        }
                        if(!$continue) {
                            $ts->serverGroupAddClient($cfg['connections']['groupId'], $connections[$i]['clientDatabaseId']);
                        }
                    }
                }
                $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $connections[$i]['clientUniqueIdentifier'] . ']' . $connections[$i]['clientNickname'] . '[/url]', $connections[$i]['connections']], $cfg['connections']['descriptions']['userLine']);
            }
        }
        $desc .= $cfg['connections']['descriptions']['downFooter'];
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['connections']['channelId']));
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['connections']['channelId'], ['channel_description' => $desc]);
        }

        # timeSpent

        $desc = $cfg['timeSpent']['descriptions']['upHeader'];
        $timeSpent = $mongoDB->serverClients->find([], ['sort' => ['timeSpent' => -1], 'limit' => $cfg['timeSpent']['recordsLimit']])->toArray();
        for($i = 0; $i < $cfg['timeSpent']['recordsLimit']; $i++) {
            if(isset($timeSpent[$i])) {
                if($cfg['timeSpent']['awardsEnabled']) {
                    if($i == 0) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['timeSpent']['groupId'], $names = true));
                        if(!is_array($serverGroupClientList)) {
                            $serverGroupClientList = [];
                        }
                        $continue = false;
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid'])) {
                                if($timeSpent[$i]['clientDatabaseId'] != $c['cldbid']) {
                                    $ts->serverGroupDeleteClient($cfg['timeSpent']['groupId'], $c['cldbid']);
                                } else {
                                    $continue = true;
                                }
                            }
                        }
                        if(!$continue) {
                            $ts->serverGroupAddClient($cfg['timeSpent']['groupId'], $timeSpent[$i]['clientDatabaseId']);
                        }
                    }
                }
                $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $timeSpent[$i]['clientUniqueIdentifier'] . ']' . $timeSpent[$i]['clientNickname'] . '[/url]', $ezApp->timeConverter($timeSpent[$i]['timeSpent'])], $cfg['timeSpent']['descriptions']['userLine']);
            }
        }
        $desc .= $cfg['timeSpent']['descriptions']['downFooter'];
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['timeSpent']['channelId']));
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['timeSpent']['channelId'], ['channel_description' => $desc]);
        }

        # timeSpentAfk

        $desc = $cfg['timeSpentAfk']['descriptions']['upHeader'];
        $timeSpentAfk = $mongoDB->serverClients->find([], ['sort' => ['timeSpentAfk' => -1], 'limit' => $cfg['timeSpentAfk']['recordsLimit']])->toArray();
        for($i = 0; $i < $cfg['timeSpentAfk']['recordsLimit']; $i++) {
            if(isset($timeSpentAfk[$i])) {
                if($cfg['timeSpentAfk']['awardsEnabled']) {
                    if($i == 0) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['timeSpentAfk']['groupId'], $names = true));
                        if(!is_array($serverGroupClientList)) {
                            $serverGroupClientList = [];
                        }
                        $continue = false;
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid'])) {
                                if($timeSpentAfk[$i]['clientDatabaseId'] != $c['cldbid']) {
                                    $ts->serverGroupDeleteClient($cfg['timeSpentAfk']['groupId'], $c['cldbid']);
                                } else {
                                    $continue = true;
                                }
                            }
                        }
                        if(!$continue) {
                            $ts->serverGroupAddClient($cfg['timeSpentAfk']['groupId'], $timeSpentAfk[$i]['clientDatabaseId']);
                        }
                    }
                }
                $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $timeSpentAfk[$i]['clientUniqueIdentifier'] . ']' . $timeSpentAfk[$i]['clientNickname'] . '[/url]', $ezApp->timeConverter($timeSpentAfk[$i]['timeSpentAfk'])], $cfg['timeSpentAfk']['descriptions']['userLine']);
            }
        }
        $desc .= $cfg['timeSpentAfk']['descriptions']['downFooter'];
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['timeSpentAfk']['channelId']));
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['timeSpentAfk']['channelId'], ['channel_description' => $desc]);
        }

        # connectionTime

        $desc = $cfg['connectionTime']['descriptions']['upHeader'];
        $connectionTime = $mongoDB->serverClients->find([], ['sort' => ['connectionTime' => -1], 'limit' => $cfg['connectionTime']['recordsLimit']])->toArray();
        for($i = 0; $i < $cfg['connectionTime']['recordsLimit']; $i++) {
            if(isset($connectionTime[$i])) {
                if($cfg['connectionTime']['awardsEnabled']) {
                    if($i == 0) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['connectionTime']['groupId'], $names = true));
                        if(!is_array($serverGroupClientList)) {
                            $serverGroupClientList = [];
                        }
                        $continue = false;
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid'])) {
                                if($connectionTime[$i]['clientDatabaseId'] != $c['cldbid']) {
                                    $ts->serverGroupDeleteClient($cfg['connectionTime']['groupId'], $c['cldbid']);
                                } else {
                                    $continue = true;
                                }
                            }
                        }
                        if(!$continue) {
                            $ts->serverGroupAddClient($cfg['connectionTime']['groupId'], $connectionTime[$i]['clientDatabaseId']);
                        }
                    }
                }
                $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $connectionTime[$i]['clientUniqueIdentifier'] . ']' . $connectionTime[$i]['clientNickname'] . '[/url]', $ezApp->timeConverter($connectionTime[$i]['connectionTime'])], $cfg['connectionTime']['descriptions']['userLine']);
            }
        }
        $desc .= $cfg['connectionTime']['descriptions']['downFooter'];
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['connectionTime']['channelId']));
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['connectionTime']['channelId'], ['channel_description' => $desc]);
        }

        # connectionLost

        $desc = $cfg['connectionLost']['descriptions']['upHeader'];
        $connectionLost = $mongoDB->serverClients->find([], ['sort' => ['connectionLost' => -1], 'limit' => $cfg['connectionLost']['recordsLimit']])->toArray();
        for($i = 0; $i < $cfg['connectionLost']['recordsLimit']; $i++) {
            if(isset($connectionLost[$i])) {
                if($cfg['connectionLost']['awardsEnabled']) {
                    if($i == 0) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['connectionLost']['groupId'], $names = true));
                        if(!is_array($serverGroupClientList)) {
                            $serverGroupClientList = [];
                        }
                        $continue = false;
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid'])) {
                                if($connectionLost[$i]['clientDatabaseId'] != $c['cldbid']) {
                                    $ts->serverGroupDeleteClient($cfg['connectionLost']['groupId'], $c['cldbid']);
                                } else {
                                    $continue = true;
                                }
                            }
                        }
                        if(!$continue) {
                            $ts->serverGroupAddClient($cfg['connectionLost']['groupId'], $connectionLost[$i]['clientDatabaseId']);
                        }
                    }
                }
                $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $connectionLost[$i]['clientUniqueIdentifier'] . ']' . $connectionLost[$i]['clientNickname'] . '[/url]', $connectionLost[$i]['connectionLost']], $cfg['connectionLost']['descriptions']['userLine']);
            }
        }
        $desc .= $cfg['connectionLost']['descriptions']['downFooter'];
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['connectionLost']['channelId']));
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['connectionLost']['channelId'], ['channel_description' => $desc]);
        }

        # level

        $desc = $cfg['level']['descriptions']['upHeader'];
        $level = $mongoDB->serverClients->find([], ['sort' => ['level' => -1], 'limit' => $cfg['level']['recordsLimit']])->toArray();
        for($i = 0; $i < $cfg['level']['recordsLimit']; $i++) {
            if(isset($level[$i])) {
                if($cfg['level']['awardsEnabled']) {
                    if($i == 0) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['level']['groupId'], $names = true));
                        if(!is_array($serverGroupClientList)) {
                            $serverGroupClientList = [];
                        }
                        $continue = false;
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid'])) {
                                if($level[$i]['clientDatabaseId'] != $c['cldbid']) {
                                    $ts->serverGroupDeleteClient($cfg['level']['groupId'], $c['cldbid']);
                                } else {
                                    $continue = true;
                                }
                            }
                        }
                        if(!$continue) {
                            $ts->serverGroupAddClient($cfg['level']['groupId'], $level[$i]['clientDatabaseId']);
                        }
                    }
                }
                $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $level[$i]['clientUniqueIdentifier'] . ']' . $level[$i]['clientNickname'] . '[/url]', $level[$i]['level']], $cfg['level']['descriptions']['userLine']);
            }
        }
        $desc .= $cfg['level']['descriptions']['downFooter'];
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['level']['channelId']));
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['level']['channelId'], ['channel_description' => $desc]);
        }

        # points

        $desc = $cfg['points']['descriptions']['upHeader'];
        $points = $mongoDB->serverClients->find([], ['sort' => ['points' => -1], 'limit' => $cfg['points']['recordsLimit']])->toArray();
        for($i = 0; $i < $cfg['points']['recordsLimit']; $i++) {
            if(isset($points[$i])) {
                if($cfg['points']['awardsEnabled']) {
                    if($i == 0) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['points']['groupId'], $names = true));
                        if(!is_array($serverGroupClientList)) {
                            $serverGroupClientList = [];
                        }
                        $continue = false;
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid'])) {
                                if($points[$i]['clientDatabaseId'] != $c['cldbid']) {
                                    $ts->serverGroupDeleteClient($cfg['points']['groupId'], $c['cldbid']);
                                } else {
                                    $continue = true;
                                }
                            }
                        }
                        if(!$continue) {
                            $ts->serverGroupAddClient($cfg['points']['groupId'], $points[$i]['clientDatabaseId']);
                        }
                    }
                }
                $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $points[$i]['clientUniqueIdentifier'] . ']' . $points[$i]['clientNickname'] . '[/url]', floor($points[$i]['points'])], $cfg['points']['descriptions']['userLine']);
            }
        }
        $desc .= $cfg['points']['descriptions']['downFooter'];
        $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['points']['channelId']));
        if($channelInfo['channel_description'] != $desc) {
            $ts->channelEdit($cfg['points']['channelId'], ['channel_description' => $desc]);
        }

        # Most active of the month
        if(isset($cfg['activeMonth']) && $cfg['activeMonth']['enabled']) {
            $desc = $cfg['activeMonth']['descriptions']['upHeader'];
            
            // Sortiere nach monthlyActivity
            $activeMonth = $mongoDB->serverClients->find(
                [], 
                ['sort' => ['monthlyActivity' => -1], 'limit' => $cfg['activeMonth']['recordsLimit']]
            )->toArray();
            
            for($i = 0; $i < $cfg['activeMonth']['recordsLimit']; $i++) {
                if(isset($activeMonth[$i])) {
                    if($cfg['activeMonth']['awardsEnabled']) {
                        if($i == 0) {
                            $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['activeMonth']['groupId'], $names = true));
                            if(!is_array($serverGroupClientList)) {
                                $serverGroupClientList = [];
                            }
                            $continue = false;
                            foreach($serverGroupClientList as $c) {
                                if(isset($c['cldbid'])) {
                                    if($activeMonth[$i]['clientDatabaseId'] != $c['cldbid']) {
                                        $ts->serverGroupDeleteClient($cfg['activeMonth']['groupId'], $c['cldbid']);
                                    } else {
                                        $continue = true;
                                    }
                                }
                            }
                            if(!$continue) {
                                $ts->serverGroupAddClient($cfg['activeMonth']['groupId'], $activeMonth[$i]['clientDatabaseId']);
                            }
                        }
                    }
                    $monthlyActivity = isset($activeMonth[$i]['monthlyActivity']) ? $ezApp->timeConverter($activeMonth[$i]['monthlyActivity']) : "0 seconds";
                    $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $activeMonth[$i]['clientUniqueIdentifier'] . ']' . $activeMonth[$i]['clientNickname'] . '[/url]', $monthlyActivity], $cfg['activeMonth']['descriptions']['userLine']);
                }
            }
            $desc .= $cfg['activeMonth']['descriptions']['downFooter'];
            $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['activeMonth']['channelId']));
            if($channelInfo['channel_description'] != $desc) {
                $ts->channelEdit($cfg['activeMonth']['channelId'], ['channel_description' => $desc]);
            }
        }

        # Most active of the year
        if(isset($cfg['activeYear']) && $cfg['activeYear']['enabled']) {
            $desc = $cfg['activeYear']['descriptions']['upHeader'];
            
            // Sortiere nach yearlyActivity
            $activeYear = $mongoDB->serverClients->find(
                [], 
                ['sort' => ['yearlyActivity' => -1], 'limit' => $cfg['activeYear']['recordsLimit']]
            )->toArray();
            
            for($i = 0; $i < $cfg['activeYear']['recordsLimit']; $i++) {
                if(isset($activeYear[$i])) {
                    if($cfg['activeYear']['awardsEnabled']) {
                        if($i == 0) {
                            $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($cfg['activeYear']['groupId'], $names = true));
                            if(!is_array($serverGroupClientList)) {
                                $serverGroupClientList = [];
                            }
                            $continue = false;
                            foreach($serverGroupClientList as $c) {
                                if(isset($c['cldbid'])) {
                                    if($activeYear[$i]['clientDatabaseId'] != $c['cldbid']) {
                                        $ts->serverGroupDeleteClient($cfg['activeYear']['groupId'], $c['cldbid']);
                                    } else {
                                        $continue = true;
                                    }
                                }
                            }
                            if(!$continue) {
                                $ts->serverGroupAddClient($cfg['activeYear']['groupId'], $activeYear[$i]['clientDatabaseId']);
                            }
                        }
                    }
                    $yearlyActivity = isset($activeYear[$i]['yearlyActivity']) ? $ezApp->timeConverter($activeYear[$i]['yearlyActivity']) : "0 seconds";
                    $desc .= str_replace(['%i%', '%clientId%', '%value%'], [$i+1, '[url=client://0/' . $activeYear[$i]['clientUniqueIdentifier'] . ']' . $activeYear[$i]['clientNickname'] . '[/url]', $yearlyActivity], $cfg['activeYear']['descriptions']['userLine']);
                }
            }
            $desc .= $cfg['activeYear']['descriptions']['downFooter'];
            $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['activeYear']['channelId']));
            if($channelInfo['channel_description'] != $desc) {
                $ts->channelEdit($cfg['activeYear']['channelId'], ['channel_description' => $desc]);
            }
        }

        # Hall of Fame - aktualisiere den Hall of Fame-Channel, wenn konfiguriert
        if(isset($cfg['hallOfFame']) && $cfg['hallOfFame']['enabled']) {
            $this->updateHallOfFame($ts, $cfg, $mongoDB, $ezApp);
        }
    }

    private function updateHallOfFame($ts, $cfg, $mongoDB, $ezApp) {
        // Hole die Hall of Fame-Konfiguration
        $hallOfFameConfig = $cfg['hallOfFame'];
        $channelId = $hallOfFameConfig['channelId'];
        
        // Hole bestehende Beschreibung
        $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
        $existingDesc = isset($channelInfo['channel_description']) ? $channelInfo['channel_description'] : '';
        
        // Hole die Einträge aus der Datenbank
        $monthlyArchives = $mongoDB->botData->find(['type' => 'monthlyTopArchive'], ['sort' => ['timestamp' => -1]])->toArray();
        $yearlyArchives = $mongoDB->botData->find(['type' => 'yearlyTopArchive'], ['sort' => ['timestamp' => -1]])->toArray();
        
        // Bereite die Beschreibung vor
        $newDesc = $hallOfFameConfig['descriptions']['header'] . "\n";
        $newDesc .= $hallOfFameConfig['descriptions']['subheader'] . "\n";
        
        // Füge jährliche Gewinner hinzu
        if(!empty($yearlyArchives)) {
            $newDesc .= $hallOfFameConfig['descriptions']['yearlyHeader'];
            
            $yearlyEntries = [];
            foreach($yearlyArchives as $archive) {
                if(isset($archive['data'][0])) {
                    $winner = $archive['data'][0];
                    $yearName = $archive['year'];
                    $winnerName = $winner['clientNickname'];
                    $activity = isset($winner['yearlyActivity']) ? $ezApp->timeConverter($winner['yearlyActivity']) : "unknown";
                    
                    $replacements = [
                        '%yearName%' => $yearName,
                        '%winnerName%' => $winnerName,
                        '%activity%' => $activity
                    ];
                    
                    $yearlyEntries[] = str_replace(array_keys($replacements), array_values($replacements), $hallOfFameConfig['descriptions']['yearlyEntryFormat']);
                }
            }
            
            // Beschränke die Anzahl der Einträge - aber mindestens 5
            $maxYearlyEntries = max(5, floor($hallOfFameConfig['maxEntries'] * 0.3)); // 30% für jährliche Einträge, min 5
            if(count($yearlyEntries) > $maxYearlyEntries) {
                $yearlyEntries = array_slice($yearlyEntries, 0, $maxYearlyEntries);
            }
            
            $newDesc .= implode("\n", $yearlyEntries) . "\n";
        }
        
        // Füge monatliche Gewinner hinzu
        if(!empty($monthlyArchives)) {
            $newDesc .= $hallOfFameConfig['descriptions']['monthlyHeader'];
            
            $monthlyEntries = [];
            foreach($monthlyArchives as $archive) {
                if(isset($archive['data'][0])) {
                    $winner = $archive['data'][0];
                    $monthName = $archive['monthName'];
                    $winnerName = $winner['clientNickname'];
                    $activity = isset($winner['monthlyActivity']) ? $ezApp->timeConverter($winner['monthlyActivity']) : "unknown";
                    
                    $replacements = [
                        '%monthName%' => $monthName,
                        '%winnerName%' => $winnerName,
                        '%activity%' => $activity
                    ];
                    
                    $monthlyEntries[] = str_replace(array_keys($replacements), array_values($replacements), $hallOfFameConfig['descriptions']['monthlyEntryFormat']);
                }
            }
            
            // Beschränke die Anzahl der Einträge - aber mindestens 10
            $maxMonthlyEntries = max(10, floor($hallOfFameConfig['maxEntries'] * 0.7)); // 70% für monatliche Einträge, min 10
            if(count($monthlyEntries) > $maxMonthlyEntries) {
                $monthlyEntries = array_slice($monthlyEntries, 0, $maxMonthlyEntries);
            }
            
            $newDesc .= implode("\n", $monthlyEntries) . "\n";
        }
        
        // Füge Footer hinzu
        $timestamp = date('Y-m-d H:i');
        $newDesc .= str_replace('%timestamp%', $timestamp, $hallOfFameConfig['descriptions']['footer']);
        
        // Aktualisiere den Kanal, wenn sich die Beschreibung geändert hat
        if($newDesc != $existingDesc) {
            $ts->channelEdit($channelId, ['channel_description' => $newDesc]);
        }
    }
}