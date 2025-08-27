<?php
class populationStatistics {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        
        // Ignorierte Gruppen
        $ignoredGroups = [283, 391];
        
        try {
            // Aktueller Online-Count (ohne ignorierte Gruppen)
            $currentOnline = $this->getCurrentOnline($ts, $ignoredGroups);
            
            // Prüfe und aktualisiere Rekorde
            $this->checkGlobalRecord($mongoDB, $currentOnline);
            $this->checkDailyRecord($mongoDB, $currentOnline);
            
            // Population Statistics Channel aktualisieren
            $this->updatePopulationChannel($ts, $mongoDB, $cfg, $currentOnline, $ezApp);
            
        } catch (Exception $e) {
            // Silent error handling
        }
    }
    
    private function getCurrentOnline($ts, $ignoredGroups) {
        try {
            $clientList = $ts->getElement('data', $ts->clientList());
            if (!is_array($clientList)) return 0;
            
            $validClients = 0;
            $seenIPs = [];
            
            foreach ($clientList as $client) {
                if ($client['client_type'] == 1) continue;
                
                $clientGroups = explode(',', $client['client_servergroups'] ?? '');
                if (!empty(array_intersect($clientGroups, $ignoredGroups))) continue;
                
                $clientIP = $client['connection_client_ip'] ?? null;
                if ($clientIP && in_array($clientIP, $seenIPs)) continue;
                
                $validClients++;
                if ($clientIP) $seenIPs[] = $clientIP;
            }
            
            return $validClients;
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function checkGlobalRecord($mongoDB, $currentOnline) {
        try {
            $globalRecord = $mongoDB->populationStats->findOne(['type' => 'globalRecord']);
            
            if (!$globalRecord || $currentOnline > ($globalRecord['record'] ?? 0)) {
                $mongoDB->populationStats->replaceOne(
                    ['type' => 'globalRecord'],
                    [
                        'type' => 'globalRecord',
                        'record' => $currentOnline,
                        'timestamp' => time(),
                        'date' => date('Y-m-d H:i:s')
                    ],
                    ['upsert' => true]
                );
            }
        } catch (Exception $e) {
            // Silent error handling
        }
    }
    
    private function checkDailyRecord($mongoDB, $currentOnline) {
        try {
            $today = date('Y-m-d');
            $dailyRecord = $mongoDB->populationStats->findOne([
                'type' => 'dailyRecord',
                'date' => $today
            ]);
            
            if (!$dailyRecord || $currentOnline > ($dailyRecord['record'] ?? 0)) {
                $mongoDB->populationStats->replaceOne(
                    ['type' => 'dailyRecord', 'date' => $today],
                    [
                        'type' => 'dailyRecord',
                        'date' => $today,
                        'record' => $currentOnline,
                        'timestamp' => time(),
                        'hour' => date('H:i')
                    ],
                    ['upsert' => true]
                );
            }
        } catch (Exception $e) {
            // Silent error handling
        }
    }
    
    private function updatePopulationChannel($ts, $mongoDB, $cfg, $currentOnline, $ezApp) {
        try {
            $channelId = $cfg['channelId'];
            
            $globalRecord = $mongoDB->populationStats->findOne(['type' => 'globalRecord']);
            $todayRecord = $mongoDB->populationStats->findOne([
                'type' => 'dailyRecord', 
                'date' => date('Y-m-d')
            ]);
            
            $globalRecordValue = $globalRecord['record'] ?? 0;
            $globalRecordDate = $globalRecord['date'] ?? date('Y-m-d H:i:s');
            $todayRecordValue = $todayRecord['record'] ?? 0;
            $todayRecordHour = $todayRecord['hour'] ?? '00:00';
            
            $channelName = str_replace(
                ['%onlineClients%', '%g_record%', '%t_record%'],
                [$currentOnline, $globalRecordValue, $todayRecordValue],
                $cfg['name'] ?? '[cspacer]📊 Population Statistics 📊'
            );
            
            $channelTopic = str_replace(
                ['%onlineClients%', '%recordDate%', '%g_record%', '%t_record%'],
                [$globalRecordValue, $globalRecordDate, $globalRecordValue, $todayRecordValue],
                $cfg['topic'] ?? 'Record: %g_record% Date: %recordDate%'
            );
            
            $statsTable = $this->generateStatsTable($mongoDB, $cfg);
            $channelDescription = str_replace(
                ['%stats%', '%onlineClients%', '%online%'],
                [$statsTable, $currentOnline, $currentOnline],
                $cfg['description'] ?? '%stats%'
            );
            
            $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
            
            if ($channelInfo['channel_name'] != $channelName) {
                $ts->channelEdit($channelId, ['channel_name' => $channelName]);
            }
            if ($channelInfo['channel_topic'] != $channelTopic) {
                $ts->channelEdit($channelId, ['channel_topic' => $channelTopic]);
            }
            if ($channelInfo['channel_description'] != $channelDescription) {
                $ts->channelEdit($channelId, ['channel_description' => $channelDescription]);
            }
            
        } catch (Exception $e) {
            // Silent error handling
        }
    }
    
    private function generateStatsTable($mongoDB, $cfg) {
        $days = $cfg['descriptionDays'] ?? 30;
        
        $stats = "[hr][center]\n";
        $stats .= "[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n";
        $stats .= "[/center][hr][center][size=10]\n";
        $stats .= "[b][color=#7be24c]📊 POPULATION STATISTICS 📊[/color][/b]\n\n";
        
        $stats .= "[table]\n";
        $stats .= "[tr][th][center]Date[/center][/th]";
        $stats .= "[th][center]Unique Visits[/center][/th]";
        $stats .= "[th][center]Record[/center][/th][/tr]\n";
        
        for ($i = 0; $i <= $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $displayDate = date('d.m.y', strtotime($date));
            
            $visits = $mongoDB->populationStats->countDocuments([
                'type' => 'uniqueVisit',
                'date' => $date
            ]);
            
            $dailyRecord = $mongoDB->populationStats->findOne([
                'type' => 'dailyRecord',
                'date' => $date
            ]);
            $record = $dailyRecord['record'] ?? 0;
            
            $stats .= "[tr]";
            $stats .= "[td][center]{$displayDate}[/center][/td]";
            $stats .= "[td][center][color=#7be24c][b]{$visits}[/b][/color][/center][/td]";
            $stats .= "[td][center][color=#7be24c][b]{$record}[/b][/color][/center][/td]";
            $stats .= "[/tr]\n";
        }
        
        $stats .= "[/table]\n";
        $stats .= "[/size][/center][hr][right][size=12]Powered by: [url=https://sysmo.pro][color=#9319bf]SYSMO.PRO[/color][/url][/right]";
        
        return $stats;
    }
    
    public function handleClientConnect($ts, $mongoDB, $clientInfo) {
        try {
            $ignoredGroups = [283, 391];
            $clientGroups = explode(',', $clientInfo['client_servergroups'] ?? '');
            if (!empty(array_intersect($clientGroups, $ignoredGroups))) {
                return;
            }
            
            $uid = $clientInfo['client_unique_identifier'];
            $ip = $clientInfo['connection_client_ip'] ?? null;
            $today = date('Y-m-d');
            
            if (!$ip) return;
            
            $existingVisit = $mongoDB->populationStats->findOne([
                'type' => 'uniqueVisit',
                'date' => $today,
                '$or' => [
                    ['uid' => $uid],
                    ['ip' => $ip]
                ]
            ]);
            
            if (!$existingVisit) {
                $mongoDB->populationStats->insertOne([
                    'type' => 'uniqueVisit',
                    'date' => $today,
                    'uid' => $uid,
                    'ip' => $ip,
                    'timestamp' => time(),
                    'nickname' => $clientInfo['client_nickname'] ?? 'Unknown'
                ]);
            }
            
        } catch (Exception $e) {
            // Silent error handling
        }
    }
}
?>