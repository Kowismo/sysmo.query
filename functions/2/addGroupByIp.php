<?php
class addGroupByIp {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            if(isset($cfg['options'][$clientInfo['connection_client_ip']])) {
                foreach($cfg['options'][$clientInfo['connection_client_ip']] as $group) {
                    if(!$ezApp->inGroup($group, $clientInfo['client_servergroups'])) {
                        $ts->serverGroupAddClient($group, $clientInfo['client_database_id']);
                    }
                }
            }
        }
    }
}