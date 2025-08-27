<?php
class createClan {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $getApi = $mongoDB->websiteApi->findOne(['type' => 'createClan']);
        if($getApi != null) {
            $mongoDB->websiteApi->deleteOne(['clientUniqueIdentifier' => $getApi['clientUniqueIdentifier']]);
            $checkGuild = $mongoDB->clanChannels->findOne(['clientUniqueIdentifier' => $getApi['clientUniqueIdentifier']]);
            if($checkGuild == null) {
                $clientId = $ts->getElement('data', $ts->clientGetIds($getApi['clientUniqueIdentifier']));
                if(!empty($clientId)) {
                    $clientInfo = $ts->getElement('data', $ts->clientInfo($clientId[0]['clid']));
                    if($clientInfo) {
                        $allGuilds = $mongoDB->clanChannels->find(['clanType' => $getApi['clanType'], 'clanStatus' => true, 'isAcademy' => false], ['sort' => ['_id' => -1], 'limit' => 1])->toArray();
                        $item = $cfg['templates'][$getApi['clanType']];
                        $guildNum = count($allGuilds);
                        $lastId = ($guildNum > 0 ? $allGuilds[$guildNum - 1]['lastId'] : $item['createOrder']);
                        $generatedString = $ezApp->generateString(4);
                        $numerationId = null;
                        $onlineId = null;
                        $cometId = null;
                        $groupChangerId = null;
                        $musicChangerId = null;
                        $mainId = null;
                        $recrutationId = null;
                        $allCids = [];
                        $allMusicBots = [];
                        $ts->serverEdit(['virtualserver_antiflood_points_tick_reduce' => $cfg['floodWhenCreate']]);
                        sleep(1);
                        foreach($item['channels'] as $channel) {
                            $ch = $ts->channelCreate([
                                'channel_name' => str_replace(['%clanType%', '%clanName%', '%generatedString%'], [$getApi['clanType'], $getApi['clanName'], $generatedString], $channel['channelName']),
                                'channel_description' => str_replace(['%clanName%', '%channelCreated%', '%clientId%'], [$getApi['clanName'], date('d/m/Y H:i'), $ezApp->createId($clientInfo)], $channel['channelDescription']),
                                'channel_flag_permanent' => 1,
                                'channel_maxclients'=> 0,
                                'channel_maxfamilyclients'=> 0,
							    'channel_codec_quality' => 10,
                                'channel_flag_maxclients_unlimited'=> 0,
                                'channel_flag_maxfamilyclients_unlimited'=> 0,
                                'channel_flag_maxfamilyclients_inherited'=> 0,
                                'channel_order' => $lastId,
                            ]);
                            if(isset($channel['channelOption'])) {
                                if($channel['channelOption'] == 'numeration') {
                                    $numerationId = $ch['data']['cid'];
                                } else if($channel['channelOption'] == 'online') {
                                    $onlineId = $ch['data']['cid'];
                                } else if($channel['channelOption'] == 'comet') {
                                    $cometId = $ch['data']['cid'];
                                } else if($channel['channelOption'] == 'groupChanger') {
                                    $groupChangerId = $ch['data']['cid'];
                                } else if($channel['channelOption'] == 'musicChanger') {
                                    $musicChangerId = $ch['data']['cid'];
                                }  else if($channel['channelOption'] == 'main') {
                                    $mainId = $ch['data']['cid'];
                                } else if($channel['channelOption'] == 'recrutation') {
                                    $recrutationId = $ch['data']['cid'];
                                }
                            }
                            if(isset($channel['subChannels'])) {
                                foreach($channel['subChannels'] as $subchannel) {
                                    $subch = $ts->channelCreate([
                                        'channel_name' => str_replace(['%clanType%', '%clanName%', '%generatedString%'], [$getApi['clanType'], $getApi['clanName'], $generatedString], $subchannel['channelName']),
                                        'channel_description' => str_replace(['%clanName%', '%channelCreated%', '%clientId%'], [$getApi['clanName'], date('d/m/Y H:i'), $ezApp->createId($clientInfo)], $subchannel['channelDescription']),
                                        'channel_flag_permanent' => 1,
                                        'channel_maxclients'=> 0,
                                        'channel_maxfamilyclients'=> 0,
							            'channel_codec_quality' => 10,
                                        'channel_flag_maxclients_unlimited'=> 0,
                                        'channel_flag_maxfamilyclients_unlimited'=> 0,
                                        'channel_flag_maxfamilyclients_inherited'=> 0,
                                        'cpid' => $ch['data']['cid'],
                                    ]);
                                    if(isset($subchannel['channelOption'])) {
                                        if($subchannel['channelOption'] == 'numeration') {
                                            $numerationId = $subch['data']['cid'];
                                        } else if($subchannel['channelOption'] == 'online') {
                                            $onlineId = $subch['data']['cid'];
                                        } else if($subchannel['channelOption'] == 'comet') {
                                            $cometId = $subch['data']['cid'];
                                        } else if($subchannel['channelOption'] == 'groupChanger') {
                                            $groupChangerId = $subch['data']['cid'];
                                        } else if($subchannel['channelOption'] == 'musicChanger') {
                                            $musicChangerId = $subch['data']['cid'];
                                        }  else if($subchannel['channelOption'] == 'main') {
                                            $mainId = $subch['data']['cid'];
                                        } else if($subchannel['channelOption'] == 'recrutation') {
                                            $recrutationId = $subch['data']['cid'];
                                        }
                                    }
                                    usleep(80000);
                                }
                            }
                            $ts->setClientChannelGroup($item['channelGroup'], $ch['data']['cid'], $clientInfo['client_database_id']);
                            $allCids[] = (int)$ch['data']['cid'];
                            $lastId = $ch['data']['cid'];
                            usleep(80000);
                        }
                        if($item['musicBots']['enabled']) {
                            $bots = [];
                            $channelClientList = $ts->getElement('data', $ts->channelClientList($item['musicBots']['channelId'], '-uid -groups'));
                            foreach($channelClientList as $client) {
                                if($ezApp->inGroup($item['musicBots']['groupId'], $client['client_servergroups'])) {
                                    $bots[] = [
                                        'clid' => $client['clid'],
                                        'client_unique_identifier' => $client['client_unique_identifier'],
                                    ];
                                }
                            }
                            if(!empty($bots)) {
                                for($i = 0; $i < $item['musicBots']['musicBotCount']; $i++) {
                                    if(isset($bots[$i])) {
                                        foreach($item['musicBots']['sendCommands'] as $message) {
                                            $ts->sendMessage(1, $bots[$i]['clid'], str_replace(['%i%', '%clanName%', '%channelId%'], [$i + 1, $getApi['clanName'], $mainId], $message));
                                        }
                                        $ts->clientMove($bots[$i]['clid'], $mainId);
                                        $allMusicBots[] = $bots[$i]['client_unique_identifier'];
                                    }
                                }
                            }
                        }
                        $ts->channelEdit($recrutationId, [
                            'channel_maxclients' => 1,
                            'channel_maxfamilyclients' => 1,
                            'channel_flag_maxclients_unlimited'=> 1,
                            'channel_flag_maxfamilyclients_unlimited'=> 1,
                        ]);
                        $guildGroup = $ts->serverGroupCopy($item['sourceGroup'], 0, $getApi['clanName'], 1);
                        $ts->clientMove($clientId[0]['clid'], $mainId);
                        $ts->serverGroupAddClient($guildGroup['data']['sgid'], $clientInfo['client_database_id']);
                        $mongoDB->clanChannels->insertOne([
                            'clanType' => $getApi['clanType'],
                            'clanName' => $getApi['clanName'],
                            'clanGroup' => (int)$guildGroup['data']['sgid'],
                            'clientUniqueIdentifier' => $getApi['clientUniqueIdentifier'],
                            'numerationId' => (int)$numerationId,
                            'onlineId' => (int)$onlineId,
                            'cometId' => (int)$cometId,
                            'groupChangerId' => (int)$groupChangerId,
                            'musicChangerId' => (int)$musicChangerId,
                            'mainId' => (int)$mainId,
                            'recrutationId' => (int)$recrutationId,
                            'lastId' => (int)$lastId,
                            'allCids' => $allCids,
                            'musicBots' => $allMusicBots,
                            'additionalCids' => [],
                            'clanStatus' => true,
                            'recrutationStatus' => 1,
                            'visibilityChannelStatus' => true,
                            'isAcademy' => false,
                            'recordOnline' => 0,
                            'timeSpent' => 0,
                            'timeSpentAfk' => 0,
                            'connections' => 0,
                            'points' => 0,
                        ]);
                        $mongoDB->clanRecrutations->insertOne([
                            'clientUniqueIdentifier' => $getApi['clientUniqueIdentifier'],
                            'clanGroup' => (int)$guildGroup['data']['sgid'],
                            'infoType' => 'poke',
                        ]);
                        $ezApp->createLog($mongoDB, __CLASS__, $clientInfo['client_unique_identifier'], $clientInfo['client_nickname'], 'received a clan channel named' . $getApi['clanName']);
                        sleep(1);
                        $ts->serverEdit(['virtualserver_antiflood_points_tick_reduce' => $cfg['floodAfterCreate']]);
                        $socketInfo = $mongoDB->botData->findOne(['type' => 'socketClid']);
                        $clientInfo = $ts->getElement('data', $ts->clientInfo($socketInfo['clid']));
                        if($clientInfo) {
                            if($clientInfo['client_type'] == 1) {
                                $ts->sendMessage(1, $socketInfo['clid'], 'add:' . $guildGroup['data']['sgid']);
                            }
                        }
                    }
                }
            }
        }
    }
}
#{"type":"createClan","clanType":"Kanał Gildyjny 50+","clanName":"WDA","clientUniqueIdentifier":"T1UsBal/a6mBW2LKEvAEPFvpev4="}