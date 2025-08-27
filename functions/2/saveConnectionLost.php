<?php
class saveConnectionLost {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            if(isset($clientInfo['reasonid']) && $clientInfo['reasonid'] == 3) {
                $req = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']]);
                if($req != null) {
                    $mongoDB->serverClients->updateOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']], ['$set' => ['connectionLost' => $req['connectionLost'] + 1]]);
                }
            }
        }
    }
}