<?php
class adminsStatus {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $channels = $mongoDB->adminChannels->find()->toArray();
        if(count($channels) > 0) {
            foreach($channels as $item) {
                $mongoInfo = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $item['clientUniqueIdentifier']]);
                if($mongoInfo != null) {
                    $clientid = $ts->getElement('data', $ts->clientGetIds($item['clientUniqueIdentifier']));
                    if(!empty($clientid)) {
                        $clientInfo = $ts->getElement('data', $ts->clientInfo($clientid[0]['clid']));
                        if($clientInfo['client_input_muted'] || $clientInfo['client_output_muted'] || floor($clientInfo['client_idle_time'] / 1000) > 300) {
                            $clientStatus = $cfg['descriptions']['status']['userAway'];
                        } else {
                            $clientStatus = $cfg['descriptions']['status']['userOnline'];
                        }
                    } else {
                        $clientStatus = $cfg['descriptions']['status']['userOffline'];
                    }
                    $channelName = str_replace(['%clientNickname%', '%adminStatus%'], [$mongoInfo['clientNickname'], $clientStatus], $cfg['channelName']);
                    $channelInfo = $ts->getElement('data', $ts->channelInfo($item['channelId']));
                    if($channelInfo) {
                        if($channelInfo['channel_name'] != $channelName) {
                            $ts->channelEdit($item['channelId'], ['channel_name' => $channelName]);
                        }
                    } else {
                        $mongoDB->adminChannels->deleteOne(['channelId' => $item['channelId']]);
                    }
                }
            }
        }
    }
}