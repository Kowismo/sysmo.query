 <?php
class onlineFromGroup {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $guilds = $mongoDB->clanChannels->find(['clanStatus' => true])->toArray();
        if(count($guilds) > 0) {
            foreach($guilds as $guild) {
                $cfg['channels'][$guild['onlineId']] = [
                    'upHeader' => str_replace(['%clanName%', '%connections%', '%timeSpent%', '%timeSpentAfk%', '%recordOnline%', '%points%', '%recordName%', '%connectionsName%', '%pointsName%'], [$guild['clanName'], $guild['connections'], $ezApp->timeConverter($guild['timeSpent']), $ezApp->timeConverter($guild['timeSpentAfk']), $guild['recordOnline'], $guild['points'], $ezApp->getNameByNumber($guild['recordOnline'], 'person', 'people', 'persons'), $ezApp->getNameByNumber($guild['connections'], 'connection', 'connections', 'connections '), $ezApp->getNameByNumber($guild['points'], 'point', 'points', 'points ')], $cfg['descriptions']['upHeader']),
                    'channelName' => isset($cfg['db'][$guild['clanType']]) ? $cfg['db'][$guild['clanType']] : $cfg['db']['default'],
                    'groupId' => $guild['clanGroup'],
                    'downFooter' => $cfg['descriptions']['downFooter'],
                    'recordOnline' => $guild['recordOnline'],
                    'isGuild' => true,
                    'isAcademy' => $guild['isAcademy'],
                ];
            }
        }
        $clientList = $ezApp->clientList();
        foreach($cfg['channels'] as $channelId => $item) {
            $onlineClients = 0;
            $maxClients = 0;
            $onlineUsers = [];
            $offlineUsers = [];
            if(is_array($item['groupId'])) {
                if(!empty($item['groupId'])) {
                    foreach($item['groupId'] as $groupId) {
                        $serverGroupClientList = $ezApp->serverGroupClientList($groupId);
                        if($serverGroupClientList) {
                            foreach($serverGroupClientList as $c) {
                                if(isset($c['client_unique_identifier'])) {
                                    $continue = false;
                                    foreach($clientList as $client) {
                                        if($client['client_unique_identifier'] == $c['client_unique_identifier']) {
                                            $continue = true;
                                            if(!array_key_exists($client['client_unique_identifier'], $onlineUsers)) {
                                                $onlineClients++;
                                                $onlineUsers[] = [
                                                    'clid' => $client['clid'],
                                                    'client_nickname' => $client['client_nickname'],
                                                    'client_unique_identifier' => $client['client_unique_identifier'],
                                                ];
                                            }
                                        }
                                    }
                                    if(!$continue) {
                                        $clientDbInfo = $ts->getElement('data', $ts->clientDbInfo($c['cldbid']));
                                        $offlineUsers[] = [
                                            'client_nickname' => $clientDbInfo['client_nickname'],
                                            'client_lastconnected' => date('d/m/Y H:i:s', $clientDbInfo['client_lastconnected']),
                                        ];
                                    }
                                    $maxClients++;
                                }
                            }
                        }
                    }
                }
            } else {
                $serverGroupClientList = $ezApp->serverGroupClientList($item['groupId']);
                if($serverGroupClientList) {
                    foreach($serverGroupClientList as $c) {
                        if(isset($c['client_unique_identifier'])) {
                            $continue = false;
                            foreach($clientList as $client) {
                                if($client['client_unique_identifier'] == $c['client_unique_identifier']) {
                                    $continue = true;
                                    if(!array_key_exists($client['client_unique_identifier'], $onlineUsers)) {
                                        $onlineClients++;
                                        $onlineUsers[] = [
                                            'clid' => $client['clid'],
                                            'client_nickname' => $client['client_nickname'],
                                            'client_unique_identifier' => $client['client_unique_identifier'],
                                        ];
                                    }
                                }
                            }
                            if(!$continue) {
                                $clientDbInfo = $ts->getElement('data', $ts->clientDbInfo($c['cldbid']));
                                $offlineUsers[] = [
                                    'client_nickname' => $clientDbInfo['client_nickname'],
                                    'client_lastconnected' => date('d/m/Y H:i:s', $clientDbInfo['client_lastconnected']),
                                ];
                            }
                            $maxClients++;
                        }
                    }
                }
            }
            if(isset($item['isGuild'])) {
                if(!$item['isAcademy']) {
                    if($item['recordOnline'] < $onlineClients) {
                        $mongoDB->clanChannels->updateOne(['clanGroup' => (int)$item['groupId']], ['$set' => ['recordOnline' => $onlineClients]]);
                    }
                }
            }
            $desc = $item['upHeader'];
            if($maxClients) {
                if($maxClients < 65) {
                    foreach($onlineUsers as $user) {
                        $desc .= str_replace(['%clientId%'], [$ezApp->createId($user)], $cfg['descriptions']['onlineClient']);
                    }
                    foreach($offlineUsers as $user) {
                        $desc .= str_replace(['%clientNickname%', '%userTime%'], [$user['client_nickname'], $user['client_lastconnected']], $cfg['descriptions']['offlineClient']);
                    }
                } else {
                    $desc .= $cfg['descriptions']['limitReached'];
                }
            } else {
                $desc .= $cfg['descriptions']['emptyGroup'];
            }
            $desc .= $item['downFooter'];
            $channelName = str_replace(['%groupId%', '%online%', '%maxClients%', '%%%'], [is_array($item['groupId']) ? $item['groupId'][0] : $item['groupId'], $onlineClients, $maxClients,  ($maxClients == 0 ? 0 : round($onlineClients/$maxClients*100))], $item['channelName']);
            $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
            if($channelInfo) {
                if($channelInfo['channel_name'] != $channelName) {
                    $ts->channelEdit($channelId, ['channel_name' => $channelName]);
                }
                if($channelInfo['channel_description'] != $desc) {
                    $ts->channelEdit($channelId, ['channel_description' => $desc]);
                }
            }
        }
    }
}