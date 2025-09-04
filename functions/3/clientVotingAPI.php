<?php
class clientVotingAPI {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        // TeamSpeak-Servers.org API Check
        if(isset($cfg['teamspeakServers']) && $cfg['teamspeakServers']['enabled']) {
            $this->checkTeamspeakServersVotes($ts, $cfg['teamspeakServers'], $mongoDB, $ezApp);
        }
        
        // TopG.org API Check
        if(isset($cfg['topg']) && $cfg['topg']['enabled']) {
            $this->checkTopGVotes($ts, $cfg['topg'], $mongoDB, $ezApp);
        }
        
        // Voting Groups vergeben
        $this->updateVotingGroups($ts, $cfg, $mongoDB, $ezApp);
    }

    private function checkTeamspeakServersVotes($ts, $cfg, $mongoDB, $ezApp) {
        $apiUrl = "https://teamspeak-servers.org/api/?object=servers&element=voters&key=" . $cfg['apiKey'] . "&month=current&format=json&limit=1000";
        
        // Debug Log
        $this->createLog($cfg, "Checking TeamSpeak-Servers.org API: " . $apiUrl);
        
        $response = $this->makeApiRequest($apiUrl);
        if($response === false) {
            $this->createLog($cfg, "TeamSpeak-Servers.org API request failed");
            return;
        }
        
        $this->createLog($cfg, "API Response: " . $response);
        
        $data = json_decode($response, true);
        if(!$data) {
            $this->createLog($cfg, "Invalid JSON response from TeamSpeak-Servers.org API");
            return;
        }
        
        // Prüfe API-Struktur
        if(!isset($data['voters']) || !is_array($data['voters'])) {
            $this->createLog($cfg, "No voters array found in API response. Structure: " . print_r($data, true));
            return;
        }
        
        $voters = $data['voters'];
        $this->createLog($cfg, "Found " . count($voters) . " voters from TeamSpeak-Servers.org");
        
        foreach($voters as $voter) {
            if(!isset($voter['nickname']) || !isset($voter['votes'])) {
                $this->createLog($cfg, "Invalid voter structure: " . print_r($voter, true));
                continue;
            }
            
            $nickname = trim($voter['nickname']);
            $currentVotes = intval($voter['votes']);
            
            if(empty($nickname) || $currentVotes <= 0) {
                $this->createLog($cfg, "Skipping invalid voter: " . $nickname . " with " . $currentVotes . " votes");
                continue;
            }
            
            $this->createLog($cfg, "Processing voter: " . $nickname . " with " . $currentVotes . " total votes");
            
            // Hole gespeicherte Vote-Anzahl für diesen User
            $existingData = $mongoDB->teamspeakServersVotes->findOne([
                'nickname' => $nickname,
                'month' => date('Y-m') // Aktueller Monat
            ]);
            
            $previousVotes = 0;
            if($existingData) {
                $previousVotes = intval($existingData['votes']);
            }
            
            $this->createLog($cfg, "User " . $nickname . " - Previous: " . $previousVotes . ", Current: " . $currentVotes);
            
            // Berechne neue Votes
            $newVotes = $currentVotes - $previousVotes;
            
            if($newVotes > 0) {
                $this->createLog($cfg, "User " . $nickname . " has " . $newVotes . " new votes!");
                
                // Finde Client über Username
                $clientUID = $this->findClientUIDByUsername($ts, $mongoDB, $nickname);
                
                if($clientUID) {
                    // Füge die neuen Votes hinzu (nicht die Gesamtanzahl!)
                    for($i = 0; $i < $newVotes; $i++) {
                        $this->incrementUserVotes($mongoDB, $clientUID, 'teamspeak-servers', $nickname);
                    }
                    
                    $this->createLog($cfg, "Added " . $newVotes . " new votes for: " . $nickname . " (UID: " . $clientUID . ")");
                    
                    // Versuche Nachricht zu senden wenn Client online
                    $client = $this->findOnlineClientByUsername($ts, $nickname);
                    if($client && $newVotes > 0 && isset($cfg['messages']['voteReceived'])) {
                        $message = str_replace(
                            ['%username%', '%source%', '%newVotes%'],
                            [$nickname, 'TeamSpeak-Servers.org', $newVotes],
                            implode("\n", $cfg['messages']['voteReceived'])
                        );
                        $ts->sendMessage(1, $client['clid'], $message);
                    }
                } else {
                    $this->createLog($cfg, "Could not find UID for username: " . $nickname);
                }
                
                // Speichere/Update die aktuelle Vote-Anzahl
                if($existingData) {
                    $mongoDB->teamspeakServersVotes->updateOne(
                        ['_id' => $existingData['_id']],
                        ['$set' => [
                            'votes' => $currentVotes,
                            'lastUpdate' => time(),
                            'newVotesAdded' => $newVotes
                        ]]
                    );
                } else {
                    $mongoDB->teamspeakServersVotes->insertOne([
                        'nickname' => $nickname,
                        'votes' => $currentVotes,
                        'month' => date('Y-m'),
                        'firstSeen' => time(),
                        'lastUpdate' => time(),
                        'newVotesAdded' => $newVotes
                    ]);
                }
            } else {
                $this->createLog($cfg, "No new votes for: " . $nickname . " (same count: " . $currentVotes . ")");
                
                // Update last check time even if no new votes
                if($existingData) {
                    $mongoDB->teamspeakServersVotes->updateOne(
                        ['_id' => $existingData['_id']],
                        ['$set' => ['lastUpdate' => time()]]
                    );
                } else {
                    // Erster Check für diesen User in diesem Monat
                    $mongoDB->teamspeakServersVotes->insertOne([
                        'nickname' => $nickname,
                        'votes' => $currentVotes,
                        'month' => date('Y-m'),
                        'firstSeen' => time(),
                        'lastUpdate' => time(),
                        'newVotesAdded' => 0
                    ]);
                    
                    // Für ersten Import: Füge alle bisherigen Votes hinzu
                    $clientUID = $this->findClientUIDByUsername($ts, $mongoDB, $nickname);
                    if($clientUID) {
                        for($i = 0; $i < $currentVotes; $i++) {
                            $this->incrementUserVotes($mongoDB, $clientUID, 'teamspeak-servers', $nickname);
                        }
                        $this->createLog($cfg, "Initial import: Added " . $currentVotes . " votes for: " . $nickname);
                    }
                }
            }
        }
        
        $this->createLog($cfg, "TeamSpeak-Servers.org vote check completed");
    }
    
    private function checkTopGVotes($ts, $cfg, $mongoDB, $ezApp) {
        $this->createLog($cfg, "Checking TopG votes...");
        
        $unprocessedVotes = $mongoDB->votingData->find([
            'source' => 'topg',
            'processed' => false
        ])->toArray();
        
        $this->createLog($cfg, "Found " . count($unprocessedVotes) . " unprocessed TopG votes");
        
        foreach($unprocessedVotes as $vote) {
            $username = $vote['username'];
            $this->createLog($cfg, "Processing TopG vote for username: " . $username);
            
            // Finde Client UID
            $clientUID = $this->findClientUIDByUsername($ts, $mongoDB, $username);
            
            if($clientUID) {
                $this->incrementUserVotes($mongoDB, $clientUID, 'topg', $username);
                $this->createLog($cfg, "New TopG vote processed for: " . $username . " (UID: " . $clientUID . ")");
                
                // Markiere als verarbeitet
                $mongoDB->votingData->updateOne(
                    ['_id' => $vote['_id']],
                    ['$set' => ['processed' => true, 'processedAt' => time()]]
                );
                
                // Sende Nachricht an User wenn online
                $client = $this->findOnlineClientByUsername($ts, $username);
                if($client && isset($cfg['messages']['voteReceived'])) {
                    $message = str_replace(
                        ['%username%', '%source%'],
                        [$username, 'TopG.org'],
                        implode("\n", $cfg['messages']['voteReceived'])
                    );
                    $ts->sendMessage(1, $client['clid'], $message);
                }
            } else {
                $this->createLog($cfg, "Could not find UID for TopG vote: " . $username);
                
                // Markiere trotzdem als verarbeitet
                $mongoDB->votingData->updateOne(
                    ['_id' => $vote['_id']],
                    ['$set' => ['processed' => true, 'processedAt' => time(), 'note' => 'UID not found']]
                );
            }
        }
    }
    
    private function updateVotingGroups($ts, $cfg, $mongoDB, $ezApp) {
        // Aktualisiere Voting-Gruppen für alle Clients (online und offline)
        $allVoters = $mongoDB->clientVotes->find()->toArray();
        
        $this->createLog($cfg, "Updating voting groups for " . count($allVoters) . " voters");
        
        foreach($allVoters as $voter) {
            $clientUID = $voter['clientUniqueIdentifier'];
            $totalVotes = $voter['totalVotes'];
            $currentVoteLevel = isset($voter['voteLevel']) ? $voter['voteLevel'] : 0;
            
            // Hole Client-Daten aus der Datenbank
            $clientData = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $clientUID]);
            if(!$clientData) {
                $this->createLog($cfg, "No client data found for UID: " . $clientUID);
                continue;
            }
            
            $clientDbId = $clientData['clientDatabaseId'];
            
            // Finde das höchste Level, das der User verdient hat
            $highestEligibleLevel = 0;
            $highestLevelData = null;
            
            foreach($cfg['voteLevels'] as $level => $levelData) {
                if($totalVotes >= $levelData['votesRequired'] && $level > $highestEligibleLevel) {
                    $highestEligibleLevel = $level;
                    $highestLevelData = $levelData;
                }
            }
            
            // Wenn User ein höheres Level verdient hat als aktuell
            if($highestEligibleLevel > $currentVoteLevel) {
                $this->createLog($cfg, "Client " . $voter['clientNickname'] . " qualifies for level " . $highestEligibleLevel . " (has " . $totalVotes . " votes, current level: " . $currentVoteLevel . ")");
                
                // Entferne ALLE niedrigeren Vote-Gruppen
                foreach($cfg['voteLevels'] as $level => $levelData) {
                    if($level < $highestEligibleLevel) {
                        try {
                            $ts->serverGroupDeleteClient($levelData['groupId'], $clientDbId);
                            $this->createLog($cfg, "Removed lower vote group " . $levelData['groupId'] . " (level " . $level . ")");
                        } catch(Exception $e) {
                            // Ignoriere Fehler wenn Gruppe nicht zugewiesen war
                        }
                    }
                }
                
                // Füge die höchste Vote-Gruppe hinzu
                try {
                    $ts->serverGroupAddClient($highestLevelData['groupId'], $clientDbId);
                    $this->createLog($cfg, "Added highest vote group " . $highestLevelData['groupId'] . " (level " . $highestEligibleLevel . ") to client dbid " . $clientDbId);
                    
                    // Update in DB
                    $mongoDB->clientVotes->updateOne(
                        ['clientUniqueIdentifier' => $clientUID],
                        ['$set' => ['voteLevel' => $highestEligibleLevel]]
                    );
                    
                    // Sende Nachricht wenn Client online
                    $onlineClient = $this->findOnlineClientByUID($ts, $clientUID);
                    if($onlineClient) {
                        $message = str_replace(
                            ['%votes%', '%level%', '%groupName%'],
                            [$totalVotes, $highestEligibleLevel, $highestLevelData['groupName']],
                            implode("\n", $cfg['messages']['voteLevelUp'])
                        );
                        $ts->sendMessage(1, $onlineClient['clid'], $message);
                    }
                    
                    $ezApp->createLog($mongoDB, __CLASS__, $clientUID,
                                   $voter['clientNickname'], 'reached vote level ' . $highestEligibleLevel . ' with ' . $totalVotes . ' votes');
                } catch(Exception $e) {
                    $this->createLog($cfg, "Error adding group: " . $e->getMessage());
                }
            }
        }
    }
    
    private function incrementUserVotes($mongoDB, $clientUID, $source, $nickname = '') {
        if(empty($clientUID)) {
            $this->createLog(['debug' => true], "Cannot increment votes: empty clientUID for " . $nickname);
            return false;
        }
        
        $existing = $mongoDB->clientVotes->findOne(['clientUniqueIdentifier' => $clientUID]);
        
        if($existing == null) {
            // Hole Client Database ID
            $clientData = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $clientUID]);
            $clientDbId = $clientData ? $clientData['clientDatabaseId'] : 0;
            
            // Erstelle neuen Eintrag
            $mongoDB->clientVotes->insertOne([
                'clientUniqueIdentifier' => $clientUID,
                'clientDatabaseId' => $clientDbId,
                'clientNickname' => $nickname,
                'totalVotes' => 1,
                'voteLevel' => 0,
                'votesBySource' => [$source => 1],
                'lastVote' => time(),
                'createdAt' => time()
            ]);
        } else {
            // Update bestehenden Eintrag
            $newTotal = $existing['totalVotes'] + 1;
            $votesBySource = isset($existing['votesBySource']) ? $existing['votesBySource'] : [];
            $votesBySource[$source] = isset($votesBySource[$source]) ? $votesBySource[$source] + 1 : 1;
            
            $updateData = [
                'totalVotes' => $newTotal,
                'votesBySource' => $votesBySource,
                'lastVote' => time()
            ];
            
            // Update nickname if provided and different
            if(!empty($nickname) && (!isset($existing['clientNickname']) || $existing['clientNickname'] != $nickname)) {
                $updateData['clientNickname'] = $nickname;
            }
            
            $mongoDB->clientVotes->updateOne(
                ['clientUniqueIdentifier' => $clientUID],
                ['$set' => $updateData]
            );
        }
        
        return true;
    }
    
    private function findClientUIDByUsername($ts, $mongoDB, $username) {
        // Prüfe zuerst ob User online ist (da Voter meist online sind)
        $onlineClient = $this->findOnlineClientByUsername($ts, $username);
        
        if($onlineClient && isset($onlineClient['client_unique_identifier'])) {
            $currentUID = $onlineClient['client_unique_identifier'];
            $currentDbId = isset($onlineClient['client_database_id']) ? intval($onlineClient['client_database_id']) : 0;
            
            // Prüfe ob es bereits Votes für diesen Username mit anderer UID gibt
            $existingVotesEntry = $mongoDB->clientVotes->findOne(['clientNickname' => $username]);
            
            if($existingVotesEntry &&
               ($existingVotesEntry['clientUniqueIdentifier'] !== $currentUID ||
                $existingVotesEntry['clientDatabaseId'] !== $currentDbId)) {
                // User hat Votes mit alter UID ODER alter Database ID -> übertrage
                $oldUID = $existingVotesEntry['clientUniqueIdentifier'];
                
                // Verwende Online Database ID (prioritär), Fallback serverClients (neueste)
                $newDbId = $currentDbId; // Online Session
                if (!$newDbId) {
                    $currentClientData = $mongoDB->serverClients->findOne(
                        ['clientUniqueIdentifier' => $currentUID],
                        ['sort' => ['_id' => -1]]
                    );
                    $newDbId = $currentClientData ? $currentClientData['clientDatabaseId'] : 0;
                }
                
                // Aktualisiere clientVotes mit neuer UID + korrekter Database ID
                $mongoDB->clientVotes->updateOne(
                    ['_id' => $existingVotesEntry['_id']],
                    ['$set' => [
                        'clientUniqueIdentifier' => $currentUID,
                        'clientDatabaseId' => $newDbId
                    ]]
                );
                
                $this->createLog(['debug' => true], "Transferred votes for $username from UID $oldUID to $currentUID (DB ID: $currentDbId)");
                
                // Vote-Gruppe neu vergeben auf neue Database ID
                $voteLevel = isset($existingVotesEntry['voteLevel']) ? intval($existingVotesEntry['voteLevel']) : 0;
                if($voteLevel > 0 && isset($cfg['voteLevels'][$voteLevel])) {
                    try {
                        $ts->serverGroupAddClient($cfg['voteLevels'][$voteLevel]['groupId'], $currentDbId);
                        $this->createLog(['debug' => true], "Re-assigned vote group {$cfg['voteLevels'][$voteLevel]['groupId']} to new UID $currentUID");
                    } catch(Exception $e) {
                        $this->createLog(['debug' => true], "Error re-assigning vote group: " . $e->getMessage());
                    }
                }
            }
            
            return $currentUID;
        }
        
        // Fallback: User nicht online - suche in DB (von hinten nach vorne = neueste zuerst)
        $clientData = $mongoDB->serverClients->findOne(
            ['clientNickname' => $username],
            ['sort' => ['_id' => -1]]
        );
        if($clientData && isset($clientData['clientUniqueIdentifier'])) {
            return $clientData['clientUniqueIdentifier'];
        }
        
        // Case-insensitive Fallback (auch neueste zuerst)
        $clientData = $mongoDB->serverClients->findOne([
            'clientNickname' => new MongoDB\BSON\Regex('^' . preg_quote($username, '/') . '$', 'i')
        ], ['sort' => ['_id' => -1]]);
        if($clientData && isset($clientData['clientUniqueIdentifier'])) {
            return $clientData['clientUniqueIdentifier'];
        }
        
        return null;
    }
    
    private function findOnlineClientByUsername($ts, $username) {
        $clientList = $ts->getElement('data', $ts->clientList());
        if(!is_array($clientList)) return null;
        
        // Exakte Suche
        foreach($clientList as $client) {
            if(isset($client['client_nickname']) && $client['client_nickname'] == $username) {
                return $client;
            }
        }
        
        // Fallback: Case-insensitive Suche
        foreach($clientList as $client) {
            if(isset($client['client_nickname']) && strtolower($client['client_nickname']) == strtolower($username)) {
                return $client;
            }
        }
        
        return null;
    }
    
    private function findOnlineClientByUID($ts, $uid) {
        $clientList = $ts->getElement('data', $ts->clientList());
        if(!is_array($clientList)) return null;
        
        foreach($clientList as $client) {
            if(isset($client['client_unique_identifier']) && $client['client_unique_identifier'] == $uid) {
                return $client;
            }
        }
        
        return null;
    }
    
    private function makeApiRequest($url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'user_agent' => 'TeamSpeak-Query-Bot/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        return $response;
    }
    
    private function createLog($cfg, $message) {
        if(isset($cfg['debug']) && $cfg['debug']) {
            $logDir = '/home/query/logs/votingAPI';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
        }
    }
}