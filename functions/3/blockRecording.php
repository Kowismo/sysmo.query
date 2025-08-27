<?php
class blockRecording {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                if($client['client_is_recording']) {
                    $ts->clientKick($client['clid'], 'server', $cfg['messages']['toUser']);
                    $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'został wyrzucony za nagrywanie');
                }
            }
        }
    }
}
