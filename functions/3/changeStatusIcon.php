<?php
class changeStatusIcon {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                $clientIdleTime = (int)floor($client['client_idle_time'] / 1000);
                if($clientIdleTime >= $cfg['options']['afk']['time']) {
                    $ts->clientAddPerm($client['client_database_id'], ['i_icon_id' => ["". $cfg['options']['afk']['icon'] . "", "0", "0"]]);
                } elseif($clientIdleTime >= $cfg['options']['idle']['time'] && $clientIdleTime <= $cfg['options']['afk']['time']) {
                    $ts->clientAddPerm($client['client_database_id'], ['i_icon_id' => ["". $cfg['options']['idle']['icon'] . "", "0", "0"]]);
                } else {
                    $ts->clientAddPerm($client['client_database_id'], ['i_icon_id' => ["". $cfg['options']['online']['icon'] . "", "0", "0"]]);
                }
            }
        }
    }
}