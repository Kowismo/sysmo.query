<?php
class clanChannelCommand {
    public function __construct($ts, $clientInfo, $args, $cfg, $mongoDB, $ezApp) {
        if(isset($args[1])) {
            switch($args[1]) {
                case 'add':
                    $clanInfo = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$args[2], 'isAcademy' => false]);
                    if($clanInfo != null) {
                        if(count($clanInfo['additionalCids']) < 6) {
                            $createdChannel = $ts->getElement('data', $ts->channelCreate([
                                'channel_name' => str_replace(['%i%', '%clanName%'], [count($clanInfo['additionalCids']) + 1, $clanInfo['clanName']], $cfg['channelName']),
                                'channel_flag_permanent' => 1,
                                'channel_maxclients'=> 0,
                                'channel_maxfamilyclients'=> 0,
                                'channel_flag_maxclients_unlimited'=> 0,
                                'channel_flag_maxfamilyclients_unlimited'=> 0,
                                'channel_flag_maxfamilyclients_inherited'=> 0,
                                'channel_order' => $clanInfo['allCids'][count($clanInfo['allCids']) - 1],
                            ]));
                            if($createdChannel) {
                                $clanInfo['additionalCids'][] = (int)$createdChannel['cid'];
                                $mongoDB->clanChannels->updateOne(['clanGroup' => $clanInfo['clanGroup']], ['$set' => ['additionalCids' => $clanInfo['additionalCids']]]);
                            }
                        } else {
                            $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['maxChannels']);
                        }
                    } else {
                        $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['guildNotExist']);
                    }
                    break;
                case 'del':
                    $clanInfo = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$args[2], 'isAcademy' => false]);
                    if($clanInfo != null) {
                        $ts->channelDelete($clanInfo['additionalCids'][count($clanInfo['additionalCids']) - 1], 1);
                        unset($clanInfo['additionalCids'][count($clanInfo['additionalCids']) - 1]);
                        $mongoDB->clanChannels->updateOne(['clanGroup' => $clanInfo['clanGroup']], ['$set' => ['additionalCids' => $clanInfo['additionalCids']]]);
                    } else {
                        $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['guildNotExist']);
                    }
                    break;
                default:
                $ts->sendMessage(1, $clientInfo['invokerid'], $cfg['messages']['commandUsage']);
                    break;
            }
        }
    }
}