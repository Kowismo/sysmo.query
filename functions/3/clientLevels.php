<?php
class clientLevels {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                $req = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $client['client_unique_identifier']]);
                if($req != null) {
                    if(!isset($req['level']) || !isset($cfg['levels'][$req['level']])) {
                        // Benutzer hat noch kein Level oder ungültiges Level
                        if($req['timeSpent'] + $req['timeSpentAfk'] >= $cfg['levels'][1]['timeSpent']) {
                            // Füge Level 1 Gruppe hinzu
                            $ts->serverGroupAddClient($cfg['levels'][1]['groupId'], $client['client_database_id']);
                            
                            // Entferne New Gruppe (ID 8) NUR wenn User sie auch hat
                            if($ezApp->inGroup([8], $client['client_servergroups'])) {
                                $ts->serverGroupDeleteClient(8, $client['client_database_id']);
                                $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'achieved level 1 and New group removed');
                            } else {
                                $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'achieved level 1 (no New group to remove)');
                            }
                            
                            $ts->sendMessage(1, $client['clid'], $cfg['messages']['firstLevel']);
                            $mongoDB->serverClients->updateOne(['clientUniqueIdentifier' => $client['client_unique_identifier']], ['$set' => ['level' => 1]]);
                        }
                    }
                    else if(isset($cfg['levels'][$req['level'] + 1]) && $req['timeSpent'] + $req['timeSpentAfk'] >= $cfg['levels'][$req['level'] + 1]['timeSpent']) {
                        // Entferne alte Level Gruppe
                        $ts->serverGroupDeleteClient($cfg['levels'][$req['level']]['groupId'], $client['client_database_id']);
                        
                        // Füge neue Level Gruppe hinzu
                        $ts->serverGroupAddClient($cfg['levels'][$req['level'] + 1]['groupId'], $client['client_database_id']);
                        
                        $ts->sendMessage(1, $client['clid'], str_replace(['%0%', '%1%'], [$req['level'], $req['level'] + 1], $cfg['messages']['nextLevels']));
                        $mongoDB->serverClients->updateOne(['clientUniqueIdentifier' => $client['client_unique_identifier']], ['$set' => ['level' => $req['level'] + 1]]);
                        $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'leveled up to ' . ($req['level'] + 1));
                    }
                }
            }
        }
    }
}