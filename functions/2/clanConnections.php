<?php
class clanConnections {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            $userClans = $ezApp->getUserClans($clientInfo['client_servergroups'], $mongoDB);
            if(!empty($userClans) && count($userClans) > 0) {
                foreach($userClans as $clan) {
                    $mongoDB->clanChannels->updateOne(['clanGroup' => $clan['clanGroup']], ['$set' => ['connections' => $clan['connections'] + 1]]);
                }
            }
        }
    }
}