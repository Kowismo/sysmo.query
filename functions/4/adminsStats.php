<?php
class adminsStats {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        if(!empty($cfg['adminsGroups'])) {
            if(!file_exists('cache/adminsStats.json')) {
                file_put_contents('cache/adminsStats.json', '[]');
            }
            $jsonFile = json_decode(file_get_contents('cache/adminsStats.json'), true);
            $jsonFileToCheck = $jsonFile;
            $adminsToCheck = [];
            foreach($cfg['adminsGroups'] as $groupId) {
                $groupList = $ezApp->serverGroupClientList($groupId);
                if(isset($groupList[0]['cldbid'])) {
                    foreach($groupList as $groupClient) {
                        $adminsToCheck[] = $groupClient['cldbid'];
                        if(!array_key_exists($groupClient['cldbid'], $jsonFile))
                            $jsonFile[$groupClient['cldbid']] = ['addedGroups' => [], 'removedGroups' => [], 'timeSpentOnCp' => 0];
                    }
                }
            }
            foreach($jsonFile as $adminDbid => $adminStats) {
                if(!in_array($adminDbid, $adminsToCheck))
                    unset($jsonFile[$adminDbid]);
                    unset($adminsToCheck[$adminDbid]);
            }
            $clientList = $ts->getElement('data', $ts->clientList('-times -uid -voice'));
            if($clientList) {
                foreach($clientList as $client) {
                    if($client['client_type'] == 1) continue;
                    if(in_array($client['client_database_id'], $adminsToCheck)) {
                        $clientIdleTime = floor($client['client_idle_time'] / 1000);
                        if(in_array($client['cid'], $cfg['helpChannels'])) {
                            if(!$client['client_input_muted'] && !$client['client_output_muted'] && $clientIdleTime < 60) {
                                $jsonFile[$client['client_database_id']]['timeSpentOnCp'] = $jsonFile[$client['client_database_id']]['timeSpentOnCp'] + $ezApp->convertInterval($cfg['interval']);
                            }
                        }
                    }
                }
            }
            $logs = $ts->getElement('data', $ts->logView(30, 1, 0, 0));
            if($logs) {
                foreach($logs as $log) {
                    if (strstr($log['l'], 'was added to servergroup') !== false) {
                        $cldbid = self::getDbid($log['l']);
                        if(in_array($cldbid, $adminsToCheck)) {
                            if(!array_key_exists(self::getDate($log['l']), $jsonFile[$cldbid]['addedGroups'])) {
                                $jsonFile[$cldbid]['addedGroups'][self::getDate($log['l'])] = self::getGroupId($log['l']);
                            }
                        }
                    }
                    if (strstr($log['l'], 'was removed from servergroup') !== false) {
                        $cldbid = self::getDbid($log['l']);
                        if(in_array($cldbid, $adminsToCheck)) {
                            if(!array_key_exists(self::getDate($log['l']), $jsonFile[$cldbid]['removedGroups'])) {
                                $jsonFile[$cldbid]['removedGroups'][self::getDate($log['l'])] = self::getGroupId($log['l']);
                            }
                        }
                    }
                }
            }
            if($jsonFileToCheck != $jsonFile) {
                file_put_contents('cache/adminsStats.json', json_encode($jsonFile));
            }
            $desc = $cfg['descriptions']['upHeader'];
            foreach($jsonFile as $adminDbid => $adminStats) {
                $search = $mongoDB->serverClients->findOne(['clientDatabaseId' => $adminDbid]);
                if($search) {
                    $helpCount = $mongoDB->adminStats->findOne(['clientUniqueIdentifier' => $search['clientUniqueIdentifier']]);
                    if($helpCount) {
                        $desc .= str_replace(['%clientId%', '%helpCount%', '%addedGroups%', '%removedGroups%', '%timeSpentOnCp%'], ['[url=client://0/' . $search['clientUniqueIdentifier'] . ']' . $search['clientNickname'] . '[/url]', $helpCount['helpCount'], count($adminStats['addedGroups']), count($adminStats['removedGroups']), $ezApp->timeConverter($adminStats['timeSpentOnCp'])], $cfg['descriptions']['userLine']);
                    }
                }
            }
            $desc .= $cfg['descriptions']['downFooter'];
            $channelInfo = $ts->getElement('data', $ts->channelInfo($cfg['channelId']));
            if($channelInfo['channel_description'] != $desc) {
                $ts->channelEdit($cfg['channelId'], ['channel_description' => $desc]);
            }
        } 
    }
    private static function getDate($log){
        preg_match('/(([0-9]*)(.)([0-9]*)(.)([0-9]*)(.)([0-9]*)(.)([0-9]*)(.)([0-9]*))/', $log, $matches);
        return $matches[0];
    }
    private static function getDbid($log){
        return (int)explode("'(id:", $log)[3];
    }
    private static function getGroupId($log){
        return (int)explode("'(id:", $log)[2];
    }
}