<?php
class checkUserPerms {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                $permsList = $ts->getElement('data', $ts->clientPermList($client['client_database_id'], true));
                if(!empty($permsList)) {
                    $removeList = [];
                    foreach($permsList as $perm) {
                        if(!in_array($perm['permsid'], $cfg['allowedPerms'])) {
                            $removeList[] = $perm['permsid'];
                        }
                    }
                    if(!empty($removeList)) {
                        $ts->clientDelPerm($client['client_database_id'], $removeList);
                        foreach($ezApp->clientList() as $adminClient) {
                            if($adminClient['client_type'] == 0 && $ezApp->inGroup($cfg['adminsGroups'], $adminClient['client_servergroups'])) {
                                $ts->sendMessage(1, $adminClient['clid'], str_replace(['%clientId%', '%removedPerms%'], [$ezApp->createId($client), implode(', ', $removeList)], $cfg['messages']['toAdmin']));
                            }
                        }
                    }
                }
            }
        }
    }
}
