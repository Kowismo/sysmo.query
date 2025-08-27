<?php
class clanCache {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $clientList = $ts->getElement('data', $ts->clientList('-uid -away -voice -times -groups -info -icon -country -ip'));
        $jsonData = [];
        $guilds = $mongoDB->clanChannels->find(['clanStatus' => true])->toArray();
        if(count($guilds) > 0) {
            foreach($guilds as $guild) {
                $onlineClients = [];
                $offlineClients = [];
                $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($guild['clanGroup'], $names = true));
                if($serverGroupClientList) {
                    foreach($serverGroupClientList as $c) {
                        if(isset($c['cldbid'])) {
                            $continue = false;
                            $channelGroup = $ts->getElement('data', $ts->channelGroupClientList($guild['mainId'], $c['cldbid']));
                            if(!$channelGroup) {
                                $channelGroup[0]['cgid'] = $cfg['defaultGroup'];
                            }
                            foreach($clientList as $client) {
                                if($c['cldbid'] == $client['client_database_id']) {
                                    $continue = true;
                                    if(!array_key_exists($client['client_unique_identifier'], $onlineClients)) {
                                        $onlineClients[$client['client_database_id']][] = array_merge($client, $channelGroup[0]);
                                    }
                                }
                            }
                            if(!$continue) {
                                $client = $ts->getElement('data', $ts->clientDbInfo($c['cldbid']));
                                $offlineClients[$c['cldbid']][] = array_merge($client, $channelGroup[0]);
                            }
                        }
                    }
                    $jsonData[$guild['clanGroup']][] = ['onlineClients' => $onlineClients, 'offlineClients' => $offlineClients];
                }
            }
        }
        file_put_contents('cache/guildCache.json', json_encode($jsonData));
    }
}