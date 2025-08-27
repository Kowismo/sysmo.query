<?php
class saveClients {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                $clientInfo = $ts->getElement('data', $ts->clientInfo($client['clid']));
                if($clientInfo) {
                    $connectionTime = (int)floor($clientInfo['connection_connected_time'] / 1000);
                    $clientIdleTime = (int)floor($clientInfo['client_idle_time'] / 1000);
                    $user = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']]);
                    if($user == null) {
                        $mongoDB->serverClients->insertOne([
                            'clientUniqueIdentifier' => $clientInfo['client_unique_identifier'],
                            'clientDatabaseId' => (int)$clientInfo['client_database_id'],
                            'clientNickname' => $clientInfo['client_nickname'],
                            'clientMyTeamspeakId' => $clientInfo['client_myteamspeak_id'],
                            'clientDescription' => $clientInfo['client_description'],
                            'clientServergroups' => $clientInfo['client_servergroups'],
                            'clientBase64HashClientUID' => $clientInfo['client_base64HashClientUID'],
                            'clientCountry' => $clientInfo['client_country'],
                            'connections' => (int)$clientInfo['client_totalconnections'],
                            'connectionTime' => $connectionTime,
                            'timeSpent' => 0,
                            'timeSpentAfk' => 0,
                            'level' => 0,
                            'points' => 0,
                            'connectionLost' => 0,
                            'logStatus' => true,
                            'lastLogin' => null,
                            'socialMedia' => null,
                            'firstName' => null,
                            'birthDate' => null,
                            'aboutMe' => null,
                            'profielSettingsStatus' => 0,
                            'accountsConnecting' => null,
                            // Neue Felder für monatliche und jährliche Aktivität
                            'monthlyActivity' => 0,
                            'yearlyActivity' => 0,
                            'lastConnectionDate' => time(),
                        ]);
                    } else {
                        $updateMany = [];
                        if((int)$user['clientDatabaseId'] != (int)$clientInfo['client_database_id']) {
                            $updateMany['clientDatabaseId'] = (int)$clientInfo['client_database_id'];
                        }
                        if($user['clientNickname'] != $clientInfo['client_nickname']) {
                            $updateMany['clientNickname'] = $clientInfo['client_nickname'];
                        }
                        if($user['clientMyTeamspeakId'] != $clientInfo['client_myteamspeak_id']) {
                            $updateMany['clientMyTeamspeakId'] = $clientInfo['client_myteamspeak_id'];
                        }
                        if($user['clientDescription'] != $clientInfo['client_description']) {
                            $updateMany['clientDescription'] = $clientInfo['client_description'];
                        }
                        if($user['clientServergroups'] != $clientInfo['client_servergroups']) {
                            $updateMany['clientServergroups'] = $clientInfo['client_servergroups'];
                        }
                        if($user['clientCountry'] != $clientInfo['client_country']) {
                            $updateMany['clientCountry'] = $clientInfo['client_country'];
                        }
                        if($user['connections'] < (int)$clientInfo['client_totalconnections']) {
                            $updateMany['connections'] = (int)$clientInfo['client_totalconnections'];
                        }
                        if($user['connectionTime'] < $connectionTime) {
                            $updateMany['connectionTime'] = $connectionTime;
                        }
                        
                        // Aktualisiere lastConnectionDate für alle aktiven Benutzer
                        $updateMany['lastConnectionDate'] = time();
                        
                        // Benutzer zählt als aktiv, solange weder Mikrofon noch Lautsprecher deaktiviert sind
                        // Die Idle-Zeit und Stummschaltung werden ignoriert, nur die Hardware-Verfügbarkeit zählt
                        if($clientInfo['client_input_hardware'] && $clientInfo['client_output_hardware']) {
                            // Zeitinkrement basierend auf dem Intervall
                            $timeIncrement = $ezApp->convertInterval($cfg['interval']);
                            
                            $updateMany['timeSpent'] = $user['timeSpent'] + $timeIncrement;
                            $updateMany['points'] = $user['points'] + $cfg['coinsAmount'];
                            
                            // Inkrementiere auch die monatliche und jährliche Aktivität
                            $updateMany['monthlyActivity'] = isset($user['monthlyActivity']) ? $user['monthlyActivity'] + $timeIncrement : $timeIncrement;
                            $updateMany['yearlyActivity'] = isset($user['yearlyActivity']) ? $user['yearlyActivity'] + $timeIncrement : $timeIncrement;
                        }
                        
                        // AFK-Zeit wird wie bisher durch Stummschaltung oder Idle-Zeit getriggert
                        if($clientInfo['client_input_muted'] || $clientInfo['client_output_muted'] || $clientIdleTime > 60) {
                            $updateMany['timeSpentAfk'] = $user['timeSpentAfk'] + $ezApp->convertInterval($cfg['interval']);
                        }
                        
                        if(!empty($updateMany)) {
                            $mongoDB->serverClients->updateOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']], ['$set' => $updateMany]);
                        }
                    }
                }
            }
        }
    }
}