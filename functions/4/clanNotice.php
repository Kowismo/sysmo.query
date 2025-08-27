<?php
class clanNotice {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $guilds = $mongoDB->clanChannels->find(['clanStatus' => true, 'recrutationStatus' => 1, 'recrutationStatus' => 2, 'isAcademy' => false])->toArray();
        if(count($guilds) > 0) {
            $item = $guilds[rand(0, count($guilds) - 1)];
            $ts->channelEdit($cfg['channelId'], ['channel_name' => str_replace(['%clanName%'], [$item['clanName']], $cfg['channelName']), 'channel_description' => str_replace(['%clanName%', '%recrutationId%'], [$item['clanName'], $item['recrutationId']], $cfg['descriptions']['main'])]);
        } else {
            $ts->channelEdit($cfg['channelId'], ['channel_name' => str_replace(['%clanName%'], ['NONE'], $cfg['channelName']), 'channel_description' => $cfg['descriptions']['noClans']]); 
        }
    }
}