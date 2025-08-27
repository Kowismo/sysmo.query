<?php
class adminPoke {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        $clientList = $ezApp->clientList();
        if(isset($cfg['channels'][$clientInfo['ctid']])) {
            $i = 0;
            $allAdmins = [];
            $adminsList = '';
            foreach($clientList as $client) {
                if($client['client_type'] == 0 && $ezApp->inGroup($cfg['channels'][$clientInfo['ctid']]['pokeGroups'], $client['client_servergroups']) && !$ezApp->inGroup($cfg['channels'][$clientInfo['ctid']]['ignoredGroups'], $client['client_servergroups'])) {
                    $allAdmins[] = $client;
                    if($i == 0) {
                        $adminsList .= $ezApp->createId($client);
                    } else {
                        $adminsList .= ', ' . $ezApp->createId($client);
                    }
                    $i++;
                }
            }
            if(count($allAdmins) > 0) {
                foreach($allAdmins as $admin) {
                    foreach($cfg['messages']['toAdmin'] as $message) {
                        switch($cfg['channels'][$clientInfo['ctid']]['messageType']) {
                            case 'poke':
                                $ts->clientPoke($admin['clid'], str_replace(['%clientId%'], [$ezApp->createId($clientInfo)], $message));
                                break;
                            case 'pw':
                                $ts->sendMessage(1, $admin['clid'], str_replace(['%clientId%'], [$ezApp->createId($clientInfo)], $message));
                                break;
                        }
                    }
                }
                foreach($cfg['messages']['toUserWhenAdmin'] as $message) {
                    $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%', '%adminsList%'], [$clientInfo['client_nickname'], $adminsList], $message));
                }
            } else {
                foreach($cfg['messages']['toUserWhenNoAdmin'] as $message) {
                    $ts->sendMessage(1, $clientInfo['clid'], str_replace(['%clientNickname%'], [$clientInfo['client_nickname']], $message));
                }
                $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
            }
        }
    }
}