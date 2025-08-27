<?php
class privateChannelsTeleport {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            $channelInfo = $mongoDB->privateChannels->findOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']]);
            if($channelInfo != null) {
                if($channelInfo['autoTeleport']) {
                    $ts->clientMove($clientInfo['clid'], $channelInfo['channelId']);
                }
            }
        }
    }
}