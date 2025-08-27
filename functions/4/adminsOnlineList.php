<?php
class adminsOnlineList {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $serverGroupList = $ezApp->serverGroupList();
        $clientList = $ezApp->clientList();
        foreach($cfg['channels'] as $channelId => $item) {
            $desc = $item['upHeader'];
            $adminsList = '';
            $i = 0;
            foreach($item['adminsGroups'] as $groupId) {
                $onlineUsers = [];
                $awayUsers = [];
                $serverGroupClientList = $ezApp->serverGroupClientList($groupId);
                foreach($serverGroupClientList as $c) {
                    if(isset($c['client_unique_identifier'])) {
                        foreach($clientList as $client) {
                            if($c['client_unique_identifier'] == $client['client_unique_identifier'] && !$ezApp->inGroup($item['ignoredGroups'], $client['client_servergroups'])) {
                                $i++;
                                if($client['client_input_muted'] || $client['client_output_muted'] || floor($client['client_idle_time'] / 1000) > 300) {
                                    $awayUsers[] = [
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
                    }
                }
                if(count($onlineUsers) > 0) {
                    foreach($onlineUsers as $user) {
                        $adminsList .= str_replace(['%clientId%', '%adminStatus%', '%userTime%', '%groupName%'], [$ezApp->createId($user), $user['adminStatus'], $user['userTime'], $ezApp->getGroupName($groupId, $serverGroupList)], $cfg['descriptions']['main']['userLine']);
                    }
                }
                if(count($awayUsers) > 0) {
                    foreach($awayUsers as $user) {
                        $adminsList .= str_replace(['%clientId%', '%adminStatus%', '%userTime%', '%groupName%'], [$ezApp->createId($user), $user['adminStatus'], $user['userTime'], $ezApp->getGroupName($groupId, $serverGroupList)], $cfg['descriptions']['main']['userLine']);
                    }
                }
            }
            if(strlen($adminsList) > 0) {
                $desc .= $adminsList . $item['downFooter'];
            } else {
                $desc .= $cfg['descriptions']['main']['noAdmins'] . $item['downFooter'];
            }
            $channelName = str_replace(['%count%', '%name%'], [$i, $ezApp->getNameByNumber($i, 'person', 'people', 'persons')], $item['channelName']);
            $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
            if($channelInfo['channel_name'] != $channelName) {
                $ts->channelEdit($channelId, ['channel_name' => $channelName]);
            }
            if($channelInfo['channel_description'] != $desc) {
                $ts->channelEdit($channelId, ['channel_description' => $desc]);
            }
        }
    }
}