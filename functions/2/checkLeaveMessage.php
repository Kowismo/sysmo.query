<?php
class checkLeaveMessage {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            if(isset($clientInfo['reasonid']) && $clientInfo['reasonid'] == 8) {
                foreach($cfg['badWords'] as $item) {
                    if(strstr(strtolower($clientInfo['reasonmsg']), strtolower($item))) {
                        $msg = str_replace('%FRAZE%', $item, $cfg['messages']['toClient']);
                        $ts->banAddByIp($clientInfo['connection_client_ip'], $cfg['banTime'], $msg);
                        $ts->banAddByUid($clientInfo['client_unique_identifier'], $cfg['banTime'], $msg);
                        $clientList = $ezApp->clientList();
                        foreach($clientList as $client) {
                            if($client['client_type'] == 0 && $ezApp->inGroup($cfg['adminsGroups'], $client['client_servergroups'])) {
                                $ts->sendMessage(1, $client['clid'], str_replace(['%clientId%', '%FRAZE%'], [$ezApp->createId($clientInfo), $item], $cfg['messages']['toAdmin']));
                            }
                        }
                        break;
                    }
                }
            }
        }
    }
}