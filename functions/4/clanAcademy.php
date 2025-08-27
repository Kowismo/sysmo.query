<?php
class clanAcademy {#{"_id":{"$oid":"614a3e07ae570403244966b2"},"type":"createAcademy","clanGroup":127}
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $getApi = $mongoDB->websiteApi->findOne(['type' => 'createAcademy']);
        if($getApi != null) {
            $mongoDB->websiteApi->deleteOne(['clanGroup' => $getApi['clanGroup']]);
            $req = $mongoDB->clanChannels->findOne(['mainGuild' => $getApi['clanGroup']]);
            if($req == null) {
                $guildInfo = $mongoDB->clanChannels->findOne(['clanGroup' => $getApi['clanGroup'], 'clanStatus' => true, 'isAcademy' => false]);
                if($guildInfo != null) {
                    $guildGroup = $ts->getElement('data', $ts->serverGroupCopy($cfg['sourceGroup'], 0,  str_replace(['%clanName%'], [$guildInfo['clanName']], $cfg['academyName']), 1));
                    if($guildGroup) {
                        $channelOnline = $ts->getElement('data', $ts->channelCreate([
                            'channel_name' => str_replace(['%academyGroup%'], [$guildGroup['sgid']], $cfg['online']),
                            'channel_flag_permanent' => 1,
                            'channel_maxclients'=> 0,
                            'channel_maxfamilyclients'=> 0,
                            'channel_flag_maxclients_unlimited'=> 0,
                            'channel_flag_maxfamilyclients_unlimited'=> 0,
                            'channel_flag_maxfamilyclients_inherited'=> 0,
                            'channel_order' => $guildInfo['onlineId'],
                        ]));
                        if($channelOnline) {
                            $channelInfo = $ts->getElement('data', $ts->channelInfo($guildInfo['groupChangerId']));
                            if($channelInfo) {
                                $channelGroupChanger = $ts->getElement('data', $ts->channelCreate([
                                    'channel_name' => str_replace(['%academyName%'], [str_replace(['%clanName%'], [$guildInfo['clanName']], $cfg['academyName'])], $cfg['groupChanger']),
                                    'channel_flag_permanent' => 1,
                                    'channel_maxclients'=> 0,
                                    'channel_maxfamilyclients'=> 0,
                                    'channel_flag_maxclients_unlimited'=> 0,
                                    'channel_flag_maxfamilyclients_unlimited'=> 0,
                                    'channel_flag_maxfamilyclients_inherited'=> 0,
                                    'cpid' => $channelInfo['pid'],
                                ]));
                                $mongoDB->clanChannels->insertOne([
                                    'mainGuild' => $guildInfo['clanGroup'],
                                    'clanType' => 'Academy',
                                    'clanName' => str_replace(['%clanName%'], [$guildInfo['clanName']], $cfg['academyName']),
                                    'clanGroup' => (int)$guildGroup['sgid'],
                                    'clientUniqueIdentifier' => $guildInfo['clientUniqueIdentifier'],
                                    'numerationId' => (int)$guildInfo['numerationId'],
                                    'onlineId' => (int)$channelOnline['cid'],
                                    'cometId' => (int)$guildInfo['cometId'],
                                    'groupChangerId' => (int)$channelGroupChanger['cid'],
                                    'musicChangerId' => (int)$guildInfo['musicChangerId'],
                                    'mainId' => (int)$guildInfo['mainId'],
                                    'recrutationId' => (int)$guildInfo['recrutationId'],
                                    'lastId' => (int)$guildInfo['lastId'],
                                    'allCids' => [(int)$channelOnline['cid'], (int)$channelGroupChanger['cid']],
                                    'musicBots' => [],
                                    'additionalCids' => [],
                                    'clanStatus' => true,
                                    'recrutationStatus' => true,
                                    'visibilityChannelStatus' => true,
                                    'isAcademy' => true,
                                    'recordOnline' => 0,
                                    'timeSpent' => 0,
                                    'timeSpentAfk' => 0,
                                    'connections' => 0,
                                    'points' => 0,
                                ]);
                                sleep(1);
                                $socketInfo = $mongoDB->botData->findOne(['type' => 'socketClid']);
                                $clientInfo = $ts->getElement('data', $ts->clientInfo($socketInfo['clid']));
                                if($clientInfo) {
                                    if($clientInfo['client_type'] == 1) {
                                        $ts->sendMessage(1, $socketInfo['clid'], 'add:' . $guildGroup['sgid']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}