<?php
class clanChecker {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $guildInfo = $mongoDB->clanChannels->findOne(['clanStatus' => false]);
        if($guildInfo != null) {
            $ts->serverEdit(['virtualserver_antiflood_points_tick_reduce' => $cfg['floodWhenDelete']]);
            sleep(1);
            if($cfg['musicBots']['enabled']) {
                if(!empty($guildInfo['musicBots'])) {
                    if(count($guildInfo['musicBots']) > 0) {
                        foreach($guildInfo['musicBots'] as $bot) {
                            if($bot != null) {
                                $clientId = $ts->getElement('data', $ts->clientGetIds($bot));
                                if(!empty($clientId)) {
                                    $clientInfo = $ts->getElement('data', $ts->clientInfo($clientId[0]['clid']));
                                    if($clientInfo['cid'] != $cfg['musicBots']['channelId']) {
                                        $ts->clientMove($clientId[0]['clid'], $cfg['musicBots']['channelId']);
                                    }
                                    $generatedName = $ezApp->generateString(10);
                                    foreach($cfg['musicBots']['sendCommands'] as $command) {
                                        $ts->sendMessage(1, $clientId[0]['clid'], str_replace(['%channelId%', '%generatedName%'], [$cfg['musicBots']['channelId'], $generatedName], $command));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $haveAcademy = $mongoDB->clanChannels->findOne(['mainGuild' => $guildInfo['clanGroup']]);
            if($haveAcademy != null) {
                foreach($haveAcademy['allCids'] as $cid) {
                    $ts->channelDelete($cid, 1);
                }
                $ts->serverGroupDelete($haveAcademy['clanGroup']);
                $socketInfo = $mongoDB->botData->findOne(['type' => 'socketClid']);
                $clientInfo = $ts->getElement('data', $ts->clientInfo($socketInfo['clid']));
                if($clientInfo) {
                    if($clientInfo['client_type'] == 1) {
                        $ts->sendMessage(1, $socketInfo['clid'], 'del:' . $haveAcademy['clanGroup']);
                    }
                }
                sleep(1);
                $mongoDB->clanChannels->deleteOne(['clanGroup' => $haveAcademy['clanGroup']]);
            }

            $allCids = @array_merge((array)$guildInfo['allCids'], (array)$guildInfo['additionalCids']);
            foreach(array_reverse($allCids) as $cid) {
                $ts->channelDelete($cid, 1);
            }
            $ts->serverGroupDelete($guildInfo['clanGroup']);
            $mongoDB->clanRecrutations->deleteMany(['clanGroup' => $guildInfo['clanGroup']]);
            $socketInfo = $mongoDB->botData->findOne(['type' => 'socketClid']);
            $clientInfo = $ts->getElement('data', $ts->clientInfo($socketInfo['clid']));
            if($clientInfo) {
                if($clientInfo['client_type'] == 1) {
                    $ts->sendMessage(1, $socketInfo['clid'], 'del:' . $guildInfo['clanGroup']);
                }
            }
            sleep(1);
            $ts->serverEdit(['virtualserver_antiflood_points_tick_reduce' => $cfg['floodAfterDelete']]);
            $mongoDB->clanChannels->deleteOne(['clanGroup' => $guildInfo['clanGroup']]);
            $mongoDB->leaderLogs->deleteMany(['clanGroup' => $guildInfo['clanGroup']]);    
            $mongoDB->recrutationWeb->deleteOne(['clanGroup' => $guildInfo['clanGroup']]);
        }
    }
}