<?php
class nickProtection {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                foreach($cfg['messages']['badWords'] as $word) {
                    if(strpos($client['client_nickname'], $word) !== false) {
                        $ts->clientKick($client['clid'], 'server', str_replace(['%badWord%'], [$word], $cfg['messages']['toUser']));
                        $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'został wyrzucony za nieprawidłowy nick');
                        return;
                    }
                }
            }
        }
    }
}
