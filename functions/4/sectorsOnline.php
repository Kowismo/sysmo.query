<?php
class sectorsOnline {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $clientList = $ezApp->clientList();
        foreach($cfg['channels'] as $channelId => $item) {
            $clans = $mongoDB->clanChannels->find(['clanType' => $item['sectorType']])->toArray();
            $onlineClients = 0;
            $maxClients = 0;
            if(count($clans) > 0) {
                foreach($clans as $clan) {
                    $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($clan['clanGroup'], $names = true));
                    foreach($serverGroupClientList as $c) {
                        if(isset($c['client_unique_identifier'])) {
                            $maxClients++;
                            foreach($clientList as $client) {
                                if($client['client_unique_identifier'] == $c['client_unique_identifier']) {
                                    $onlineClients++;
                                }
                            }
                        }
                    }
                    $academy = $mongoDB->clanChannels->findOne(['mainGuild' => $clan['clanGroup']]);
                    if($academy != null) {
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($academy['clanGroup'], $names = true));
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['client_unique_identifier'])) {
                                $maxClients++;
                                foreach($clientList as $client) {
                                    if($client['client_unique_identifier'] == $c['client_unique_identifier']) {
                                        $onlineClients++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
            $channelName = str_replace(['%onlineClients%', '%maxClients%'], [$onlineClients, $maxClients], $item['channelName']);
            if($channelInfo['channel_name'] != $channelName) {
                $ts->channelEdit($channelId, ['channel_name' => $channelName]);
            }
        }
    }
}