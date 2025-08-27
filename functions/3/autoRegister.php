<?php
class autoRegister {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                if(!$ezApp->inGroup($cfg['groupId'], $client['client_servergroups'])) {
                    $req = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $client['client_unique_identifier']]);
                    if($req != null) {
                        if($req['timeSpent'] >= $cfg['timeSpent']) {
                            // Füge Verified Gruppe hinzu
                            $ts->serverGroupAddClient($cfg['groupId'], $client['client_database_id']);
                            
                            // Entferne Unverified Gruppe (ID 369) NUR wenn User sie auch hat
                            if($ezApp->inGroup([369], $client['client_servergroups'])) {
                                $ts->serverGroupDeleteClient(369, $client['client_database_id']);
                                $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'has been verified and Unverified group removed');
                            } else {
                                $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'has been verified (no Unverified group to remove)');
                            }
                            
                            foreach($cfg['messages']['toUser'] as $message) {
                                $ts->sendMessage(1, $client['clid'], str_replace(['%clientNickname%'], [$client['client_nickname']], $message));
                            }
                        }
                    }
                }
            }
        }
    }
}