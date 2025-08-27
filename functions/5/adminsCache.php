<?php
class adminsCache {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $clientList = $ts->getElement('data', $ts->clientList('-uid -away -voice -times -groups -info -icon -country -ip'));
        $jsonData = [];
        foreach($cfg['adminsGroups'] as $group) {
            $onlineClients = [];
            $offlineClients = [];
            $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($group, $names = true));
            foreach($serverGroupClientList as $c) {
                if(isset($c['cldbid'])) {
                    $continue = false;
                    foreach($clientList as $client) {
                        if($c['cldbid'] == $client['client_database_id']) {
                            $continue = true;
                            $onlineClients[$client['client_database_id']][] = $client;
                        }
                    }
                    if(!$continue) {
                        $client = $ts->getElement('data', $ts->clientDbInfo($c['cldbid']));
                        $offlineClients[$c['cldbid']][] = $client;
                    }
                }
            }
            $jsonData[$group][] = [
                'onlineClients' => $onlineClients,
                'offlineClients' => $offlineClients,
            ];
        }
        file_put_contents('cache/adminCache.json', json_encode($jsonData));
    }
}