<?php
class newUsersTodaySave {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        $clientDbInfo = $ts->getElement('data', $ts->clientDbInfo($clientInfo['client_database_id']));
        if($clientDbInfo['client_totalconnections'] == 0) {
            $mongoDB->newUsersToday->insertOne([
                'clientNickname' => $clientInfo['client_nickname'],
                'clientUniqueIdentifier' => $clientInfo['client_unique_identifier'],
                'joinHour' => date('H:i'),
                'joinDate' => date('d/m/Y'),
            ]);
        }
    }
}