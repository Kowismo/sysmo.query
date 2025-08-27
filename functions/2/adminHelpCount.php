<?php
class adminHelpCount {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(isset($clientInfo['invokerid'])) {
            $adminInfo = $ts->getElement('data', $ts->clientInfo($clientInfo['invokerid']));
            if($ezApp->inGroup($cfg['adminsGroups'], $adminInfo['client_servergroups'])) {
                $search = $mongoDB->adminStats->findOne(['clientUniqueIdentifier' => $adminInfo['client_unique_identifier']]);
                if($search) {
                    $mongoDB->adminStats->updateOne(['clientUniqueIdentifier' => $adminInfo['client_unique_identifier']], ['$set' => ['helpCount' => $search['helpCount'] + 1, 'clientNickname' => $adminInfo['client_nickname']]]);
                } else {
                    $mongoDB->adminStats->insertOne([
                        'clientUniqueIdentifier' => $adminInfo['client_unique_identifier'],
                        'clientNickname' => $adminInfo['client_nickname'],
                        'helpCount' => 1,
                    ]);
                }
            }
        }
    }
}