<?php
class adminsList {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $serverGroupList = $ezApp->serverGroupList();
        $clientList = $ezApp->clientList();
        foreach($cfg['channels'] as $channelId => $item) {
            $desc = $item['upHeader'];
            foreach($item['adminsGroups'] as $groupId) {
                $desc .= str_replace(['%groupName%'], [$ezApp->getGroupName($groupId, $serverGroupList)], $cfg['descriptions']['main']['groupLine']);
                $serverGroupClientList = $ezApp->serverGroupClientList($groupId);
                $onlineUsers = [];
                $offlineUsers = [];
                foreach($serverGroupClientList as $c) {
                    if(isset($c['client_unique_identifier'])) {
                        $continue = false;
                        foreach($clientList as $client) {
                            if($c['client_unique_identifier'] == $client['client_unique_identifier']) {
                                $continue = true;
                                if($client['client_input_muted'] || $client['client_output_muted'] || floor($client['client_idle_time'] / 1000) > 300) {
                                    $onlineUsers[] = [
                                        'client_nickname' => $client['client_nickname'],
                                        'client_unique_identifier' => $client['client_unique_identifier'],
                                        'adminStatus' => $cfg['descriptions']['status']['userAway'],
                                        'userTime' => $ezApp->timeConverter(floor($client['client_idle_time'] / 1000)),
                                    ];
                                } else {
                                    $onlineUsers[] = [
                                        'client_nickname' => $client['client_nickname'],
                                        'client_unique_identifier' => $client['client_unique_identifier'],
                                        'adminStatus' => $cfg['descriptions']['status']['userOnline'],
                                        'userTime' => $ezApp->timeConverter(floor(time() - $client['client_lastconnected'])),
                                    ];
                                }
                            }
                        }
                        if(!$continue) {
                            $clientDbInfo = $ts->getElement('data', $ts->clientDbInfo($c['cldbid']));
                            $offlineUsers[] = [
                                'client_nickname' => $clientDbInfo['client_nickname'],
                                'client_unique_identifier' => $clientDbInfo['client_unique_identifier'],
                                'adminStatus' => $cfg['descriptions']['status']['userOffline'],
                                'userTime' => date('d/m/Y H:i:s',$clientDbInfo['client_lastconnected']),
                            ];
                        }
                    }
                }
                if(count($onlineUsers) > 0 || count($offlineUsers) > 0) {
                    $desc .= str_replace(['%allInGroup%', '%name%'], [count($onlineUsers) + count($offlineUsers), $ezApp->getNameByNumber(count($onlineUsers) + count($offlineUsers), 'person', 'people', 'persons')], $cfg['descriptions']['main']['allCountLine']);
                    $desc .= str_replace(['%adminsOnline%', '%name%'], [count($onlineUsers), $ezApp->getNameByNumber(count($onlineUsers), 'person', 'people', 'persons')], $cfg['descriptions']['main']['onlineCountLine']);
                    foreach($onlineUsers as $user) {
                        $desc .= str_replace(['%clientId%', '%adminStatus%', '%userTime%'], [$ezApp->createId($user), $user['adminStatus'], $user['userTime']], $cfg['descriptions']['main']['userLine']);
                    }
                    foreach($offlineUsers as $user) {
                        $desc .= str_replace(['%clientId%', '%adminStatus%', '%userTime%'], [$ezApp->createId($user), $user['adminStatus'], $user['userTime']], $cfg['descriptions']['main']['userLine']);
                    }
                } else {
                    $desc .= $cfg['descriptions']['main']['noAdmins'];
                }
            }
            $desc .= $item['downFooter'];
            $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
            if($channelInfo['channel_description'] != $desc) {
                $ts->channelEdit($channelId, ['channel_description' => $desc]);
            }
        }
    }
}