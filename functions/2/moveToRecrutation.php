<?php
class moveToRecrutation {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            $clientNickname = explode('/', $clientInfo['client_nickname']);
            if(isset($clientNickname[1])) {
                $clanInfo = $mongoDB->clanChannels->findOne(['clanName' => $clientNickname[1]]);
                if($clanInfo != null) {
                    $ts->clientMove($clientInfo['clid'], $clanInfo['recrutationId']);
                }
            }
        }
    }
}