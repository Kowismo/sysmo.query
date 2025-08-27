<?php
class websiteApi {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $api = $mongoDB->websiteApi->find()->toArray();
        if(count($api) > 0) {
            foreach($api as $item) {
                switch($item['type']) {
                    case 'sendToken':
                        $client = $ts->getElement('data', $ts->clientGetIds($item['clientUniqueIdentifier']));
                        if(!empty($client)) {
                            $message = str_replace(['%token%', '%clientNickname%'], [$item['token'], $client[0]['name']], $cfg['sendToken']);
                            $ts->clientPoke((int)$client[0]['clid'], $message);
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'changeServerGroups':
                        if(!empty($item['groups'])) {
                            foreach($item['groupsList'] as $group) {
                                $ts->serverGroupDeleteClient($group, $item['clientDatabaseId']);
                            }
                            foreach($item['groups'] as $group) {
                                $ts->serverGroupAddClient($group, $item['clientDatabaseId']);
                            }
                        } else {
                            foreach($item['groupsList'] as $group) {
                                $ts->serverGroupDeleteClient($group, $item['clientDatabaseId']);
                            } 
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'changeAgeGroups':
                        foreach($item['ageGroupList'] as $group) {
                            $ts->serverGroupDeleteClient($group, $item['clientDatabaseId']);
                        }
                        $ts->serverGroupAddClient($item['ageGroup'], $item['clientDatabaseId']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'changeMiscGroups':
                        if(!empty($item['miscGroups'])) {
                            foreach($item['miscGroupsList'] as $group) {
                                $ts->serverGroupDeleteClient($group, $item['clientDatabaseId']);
                            }
                            foreach($item['miscGroups'] as $group) {
                                $ts->serverGroupAddClient($group, $item['clientDatabaseId']);
                            }
                        } else {
                            foreach($item['miscGroupsList'] as $group) {
                                $ts->serverGroupDeleteClient($group, $item['clientDatabaseId']);
                            } 
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'renameGuild':
                        $ts->serverGroupRename($item['clanGroup'], $item['newName']);
                        $guild = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$item['clanGroup']]);
                        if($guild != null) {
                            foreach((array)$guild['allCids'] as $ch) {
                                $channelInfo = $ts->getElement('data', $ts->channelInfo($ch));
                                $ts->channelEdit($ch, ['channel_name' => str_replace([$item['oldName']], [$item['newName']], $channelInfo['channel_name'])]);
                            }
                            foreach((array)$guild['musicBots'] as $bot) {
                                $clientid = $ts->getElement('data', $ts->clientGetIds($bot));
                                if(!empty($clientid)) {
                                    foreach($cfg['sendCommands'] as $command) {
                                        $ts->sendMessage(1, $clientid[0]['clid'], str_replace(['%botName%'], [str_replace([$item['oldName']], [$item['newName']], $clientid[0]['name'])], $command));
                                    }
                                }
                            }
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'sendMessageGuild':
                        $clientList = $ts->getElement('data', $ts->clientList('-groups'));
                        switch($item['messageType']) {
                            case 'poke':
                                foreach($clientList as $client) {
                                    if(in_array($item['clanGroup'], explode(',', $client['client_servergroups']))) {
                                        $ts->clientPoke($client['clid'], $item['messageText']);
                                    }
                                }
                                break;
                            case 'pw':
                                foreach($clientList as $client) {
                                    if(in_array($item['clanGroup'], explode(',', $client['client_servergroups']))) {
                                        $ts->sendMessage(1, $client['clid'], $item['messageText']);
                                    }
                                }
                                break;
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'moveGuild':
                        if(isset($item['moveDate'])) {
                            if($item['moveDate'] < time()) {
                                $clientList = $ts->getElement('data', $ts->clientList('-groups'));
                                foreach($clientList as $client) {
                                    if(in_array($item['clanGroup'], explode(',', $client['client_servergroups']))) {
                                        $ts->clientMove($client['clid'], $item['mainId']);
                                    }
                                }
                                $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                            }
                        } else {
                            $clientList = $ts->getElement('data', $ts->clientList('-groups'));
                            foreach($clientList as $client) {
                                if(in_array($item['clanGroup'], explode(',', $client['client_servergroups']))) {
                                    $ts->clientMove($client['clid'], $item['mainId']);
                                }
                            }
                            $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        }
                        break;
                    case 'removeUsersGuild':
                        $serverGroupClientList = $ts->getElement('data', $ts->serverGroupClientList($item['clanGroup']));
                        foreach($serverGroupClientList as $c) {
                            if(isset($c['cldbid']) && $c['cldbid'] != $item['leaderDbid']) {
                                $ts->serverGroupDeleteClient($item['clanGroup'], $c['cldbid']);
                            }
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'removeChannelGroupGuild':
                        foreach($item['channels'] as $cid) {
                            $ezApp->deleteSelectedChannelGroup($ts, $cid, $item['removeChannelGroup'], $cfg['defaultGroup']);
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'changeUserGroup':
                        $ts->setClientChannelGroup($item['groupToSet'], $item['mainId'], $item['userDbId']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'setPromotionGuild':
                        $ts->setClientChannelGroup($item['groupToSet'], $item['mainId'], $item['setPromotionDbid']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'setDegradationGuild':
                        $ts->setClientChannelGroup($item['groupToSet'], $item['mainId'], $item['setDegradationDbid']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'changeChannelGroup':
                        $ts->setClientChannelGroup($item['changeChannelGroup'], $item['channelMain'], $item['clientDbid']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'changeMusicAccessGuild':
                        $mongoDBInfo = $mongoDB->serverClients->findOne(['clientDatabaseId' => $item['changeMusicDbid']]);
                        if($mongoDBInfo != null) {
                            if(in_array($item['musicBotAccess'], (array)$mongoDBInfo['clientServergroups'])) {
                                $ts->serverGroupDeleteClient($item['musicBotAccess'], $item['changeMusicDbid']);
                            } else {
                                $ts->serverGroupAddClient($item['musicBotAccess'], $item['changeMusicDbid']);
                            }
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'removeUserGuild':
                        foreach($item['channels'] as $channel) {
                            $ts->setClientChannelGroup($cfg['defaultGroup'], $channel, $item['removeUserDbid']);
                        }
                        $ts->serverGroupDeleteClient($item['clanGroup'], $item['removeUserDbid']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'shopBuy':
                        switch($item['itemType']) {
                            case 'group':
                                $ts->serverGroupAddClient($item['data'], $item['clientDbid']);
                                $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                                break;
                            case 'badge':
                                $ts->serverGroupAddClient($item['data'], $item['clientDbid']);
                                $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                                break;
                            case 'ownGroup':
                                $serverGroup = $ts->serverGroupCopy($cfg['ownGroupCopy'], 0, $item['groupName'], 1);
                                $ts->serverGroupAddClient($serverGroup['data']['sgid'], $item['clientDbid']);
                                $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                                break;
                            case 'tempGroup':
                                $mongoDB->timeGroups->insertOne([
                                    'clientDbid' => $item['clientDbid'],
                                    'group' => $item['data'],
                                    'timeStart' => $item['timeStart'],
                                    'timeEnd' => $item['timeEnd'],
                                ]);
                                $ts->serverGroupAddClient($item['data'], $item['clientDbid']);
                                break;
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'guildBuyItem': 
                        switch ($item['itemType']) {
                            case 'minecraft_group':
                                $ts->serverGroupAddClient($item['toSet'], $item['leaderDbid']);
                                break;
                            case 'move_channel':
                                $ts->serverGroupAddClient($item['toSet'], $item['leaderDbid']);
                                break;
                            case 'host_msg':
                                $ts->serverGroupAddClient($item['toSet'], $item['leaderDbid']);
                                break;
                            case 'private_group':
                                $serverGroup = $ts->serverGroupCopy($item['toSet'], 0, 'Prywatna - '.$item['leaderDbid'], 1);
                                $ts->serverGroupAddClient($serverGroup['data']['sgid'], $item['leaderDbid']);
                                break;
                            case 'musicbot_guild':
                                $guild = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$item['clanGroup'], 'isAcademy' => false]);
                                $bots = [];
                                $channelClientList = $ts->getElement('data', $ts->channelClientList($cfg['musicBots']['channelId'], '-uid -groups'));
                                foreach($channelClientList as $client) {
                                    if($ezApp->inGroup($cfg['musicBots']['groupId'], $client['client_servergroups'])) {
                                        $bots[] = [
                                            'clid' => $client['clid'],
                                            'client_unique_identifier' => $client['client_unique_identifier'],
                                        ];
                                    }
                                }
                                if(!empty($bots)) {
                                    foreach($cfg['musicBots']['sendCommandsAdd'] as $message) {
                                        $ts->sendMessage(1, $bots[0]['clid'], str_replace(['%i%', '%clanName%', '%channelId%'], [count($guild['musicBots']) + 1, $guild['clanName'], $guild['mainId']], $message));
                                    }
                                    $ts->clientMove($bots[0]['clid'], $guild['mainId']);
                                    $guild['musicBots'][] = $bots[0]['client_unique_identifier'];
                                    $mongoDB->clanChannels->updateOne(['clanGroup' => $guild['clanGroup']], ['$set' => ['musicBots' => $guild['musicBots']]]);
                                }
                                break;
                            case 'image_guild':
                                $ts->serverGroupAddClient($item['toSet'], $item['leaderDbid']);
                                break;
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'deleteBan':
                        $ts->banDelete($item['banid']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'hideChannelsGuild':
                        $perm['i_channel_needed_subscribe_power'] = $cfg['neededSubscribePowerOn'];
                        $channels = $ezApp->getChannelsLeader($ts, $item['firstChannel'], $item['lastChannel']);
                        foreach($channels as $channel) {
                            $ts->channelAddPerm($channel['cid'], $perm);
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'showChannelsGuild':
                        $perm['i_channel_needed_subscribe_power'] = $cfg['neededSubscribePowerOff'];
                        $channels = $ezApp->getChannelsLeader($ts, $item['firstChannel'], $item['lastChannel']);
                        foreach($channels as $channel) {
                            $ts->channelAddPerm($channel['cid'], $perm);
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'addSpacer':
                        $clanInfo = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$item['clanGroup'], 'isAcademy' => false]);
                        if($clanInfo != null) {
                            if(count($clanInfo['additionalCids']) < 6) {
                                $createdChannel = $ts->getElement('data', $ts->channelCreate([
                                    'channel_name' => '[cspacer]'.$ezApp->generateString(6),
                                    'channel_flag_permanent' => 1,
                                    'channel_maxclients'=> 0,
                                    'channel_maxfamilyclients'=> 0,
                                    'channel_flag_maxclients_unlimited'=> 0,
                                    'channel_flag_maxfamilyclients_unlimited'=> 0,
                                    'channel_flag_maxfamilyclients_inherited'=> 0,
                                    'channel_order' => $clanInfo['allCids'][count($clanInfo['allCids']) - 1],
                                ]));
                                for($i=0; $i < $item['subsCount']; $i++) { 
                                    $subchannel = $ts->channelCreate([
                                        'channel_name' => $ezApp->generateString(4),
                                        'channel_flag_permanent' => 1,
                                        'channel_maxclients'=> 0,
                                        'channel_maxfamilyclients'=> 0,
                                        'channel_flag_maxclients_unlimited'=> 0,
                                        'channel_flag_maxfamilyclients_unlimited'=> 0,
                                        'channel_flag_maxfamilyclients_inherited'=> 0,
                                        'cpid' => $createdChannel['cid'],
                                    ]);
                                }
                                if($createdChannel) {
                                    $clanInfo['additionalCids'][] = (int)$createdChannel['cid'];
                                    $mongoDB->clanChannels->updateOne(['clanGroup' => $clanInfo['clanGroup']], ['$set' => ['additionalCids' => $clanInfo['additionalCids']]]);
                                }
                            }
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'reportToAdmin':
                        $guild = $mongoDB->clanChannels->findOne(['clanGroup' => (int)$item['clanGroup']]);
                        $clientList = $ezApp->clientList();
                        $allAdmins = [];
                        foreach($clientList as $client) {
                            if($client['client_type'] == 0 && $ezApp->inGroup($cfg['reportToAdmin']['pokeGroups'], $client['client_servergroups']) && !$ezApp->inGroup($cfg['reportToAdmin']['ignoredGroups'], $client['client_servergroups'])) {
                                $allAdmins[] = $client;
                            }
                        }
                        if(count($allAdmins) > 0) {
                            foreach($allAdmins as $admin) {
                                foreach($cfg['reportToAdmin']['msg'] as $message) {
                                    $ts->sendMessage(1, $admin['clid'], str_replace(['%guildName%'], [$guild['clanName']], $message));
                                }
                            }
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'reportToAdminUser':
                        $user = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => (string)$item['clientUniqueIdentifier']]);
                        $clientList = $ezApp->clientList();
                        $allAdmins = [];
                        foreach($clientList as $client) {
                            if($client['client_type'] == 0 && $ezApp->inGroup($cfg['reportToAdminUser']['pokeGroups'], $client['client_servergroups']) && !$ezApp->inGroup($cfg['reportToAdminUser']['ignoredGroups'], $client['client_servergroups'])) {
                                $allAdmins[] = $client;
                            }
                        }
                        if(count($allAdmins) > 0) {
                            foreach($allAdmins as $admin) {
                                foreach($cfg['reportToAdminUser']['msg'] as $message) {
                                    $ts->sendMessage(1, $admin['clid'], str_replace(['%userName%'], [$user['clientNickname']], $message));
                                }
                            }
                        }
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                    case 'iconGuild':
                        $debugger = $ts->uploadIcon($item['path']);
                        $iconID = $debugger['data'][0]['name'];
                        $iconID = str_replace("/icon_", "", $iconID);
                        $perm = [];
                        $perm['i_icon_id'] = ["$iconID", "0", "0"];
                        $ts->serverGroupAddPerm($item['clanGroup'], $perm);
                        unlink($item['path']);
                        $mongoDB->websiteApi->deleteOne(['_id' => $item['_id']]);
                        break;
                }
            }
        }
    }
}
