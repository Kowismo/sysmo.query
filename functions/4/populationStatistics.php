<?php
file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] PopulationStatistics.php loaded at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

class PopulationStatistics {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        
        file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] PopulationStatistics constructor called\n", FILE_APPEND);
        file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Channel ID: " . ($cfg['channelId'] ?? 'NOT SET') . "\n", FILE_APPEND);
        file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Enabled: " . ($cfg['enabled'] ?? 'NOT SET') . "\n", FILE_APPEND);
        
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
            echo "[PopulationStats] Error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Ermittelt aktuelle Online-Anzahl ohne ignorierte Gruppen
     */
    private function getCurrentOnline($ts, $ignoredGroups) {
        try {
            file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Getting client list...\n", FILE_APPEND);
            $clientList = $ts->getElement('data', $ts->clientList());
            file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Client list type: " . gettype($clientList) . "\n", FILE_APPEND);
            
            if (!is_array($clientList)) {
                file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Client list is not array, returning 0\n", FILE_APPEND);
                return 0;
            }
            
            file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Found " . count($clientList) . " total clients\n", FILE_APPEND);
            
            $validClients = 0;
            $seenIPs = [];
            
            foreach ($clientList as $client) {
                // Query-Clients ignorieren
                if ($client['client_type'] == 1) continue;
                
                // Ignorierte Gruppen prüfen
                $clientGroups = explode(',', $client['client_servergroups'] ?? '');
                if (!empty(array_intersect($clientGroups, $ignoredGroups))) continue;
                
                // Duplicate IP Check (optional)
                $clientIP = $client['connection_client_ip'] ?? null;
                if ($clientIP && in_array($clientIP, $seenIPs)) continue;
                
                $validClients++;
                if ($clientIP) $seenIPs[] = $clientIP;
                file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Valid client: " . ($client['client_nickname'] ?? 'Unknown') . "\n", FILE_APPEND);
            }
            
            file_put_contents('/home/query/logs/population_debug.log', "[DEBUG] Total valid clients: {$validClients}\n", FILE_APPEND);
            return $validClients;
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Prüft und aktualisiert globalen Rekord
     */
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
                file_put_contents('/home/query/logs/population_debug.log', "[PopulationStats] New global record: {$currentOnline}\n", FILE_APPEND);
            }
        } catch (Exception $e) {
            file_put_contents('/home/query/logs/population_debug.log', "[PopulationStats] Global record error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
    
    /**
     * Prüft und aktualisiert täglichen Rekord
     */
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
                echo "[PopulationStats] New daily record for {$today}: {$currentOnline}\n";
            }
        } catch (Exception $e) {
            echo "[PopulationStats] Daily record error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Population Statistics Channel aktualisieren
     */
    private function updatePopulationChannel($ts, $mongoDB, $cfg, $currentOnline, $ezApp) {
        try {
            $channelId = $cfg['channelId'];
            
            // Hole Rekord-Daten
            $globalRecord = $mongoDB->populationStats->findOne(['type' => 'globalRecord']);
            $todayRecord = $mongoDB->populationStats->findOne([
                'type' => 'dailyRecord', 
                'date' => date('Y-m-d')
            ]);
            
            $globalRecordValue = $globalRecord['record'] ?? 0;
            $globalRecordDate = $globalRecord['date'] ?? date('Y-m-d H:i:s');
            $todayRecordValue = $todayRecord['record'] ?? 0;
            $todayRecordHour = $todayRecord['hour'] ?? '00:00';
            
            // Channel Name
            $channelName = str_replace(
                ['%onlineClients%', '%g_record%', '%t_record%'],
                [$currentOnline, $globalRecordValue, $todayRecordValue],
                $cfg['name'] ?? '[cspacer]📊 Population Statistics 📊'
            );
            
            // Channel Topic  
            $channelTopic = str_replace(
                ['%onlineClients%', '%recordDate%', '%g_record%', '%t_record%'],
                [$globalRecordValue, $globalRecordDate, $globalRecordValue, $todayRecordValue],
                $cfg['topic'] ?? 'Record: %g_record% Date: %recordDate%'
            );
            
            // Channel Description mit Statistik-Tabelle
            $statsTable = $this->generateStatsTable($mongoDB, $cfg);
            $channelDescription = str_replace(
                ['%stats%', '%onlineClients%', '%online%'],
                [$statsTable, $currentOnline, $currentOnline],
                $cfg['description'] ?? '%stats%'
            );
            
            // Channel aktualisieren (wie in deinen anderen Scripts)
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
            echo "[PopulationStats] Channel update error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Generiert SYSMO.PRO Style Statistik-Tabelle
     */
    private function generateStatsTable($mongoDB, $cfg) {
        $days = $cfg['descriptionDays'] ?? 30;
        
        // SYSMO.PRO Style Header (wie in deiner config)
        $stats = "[hr][center]\n";
        $stats .= "[img]https://sysmo.pro/images/czpNFsJUyubhE73k8q3smTrgdYsrMp58wr9PBp3DR4GhMXEJxf/6m5WHZgEqrP54emn2u.png[/img]\n";
        $stats .= "[/center][hr][center][size=10]\n";
        $stats .= "[b][color=#7be24c]📊 POPULATION STATISTICS 📊[/color][/b]\n\n";
        
        // Tabelle Header
        $stats .= "[table]\n";
        $stats .= "[tr][th][center]Date[/center][/th]";
        $stats .= "[th][center]Unique Visits[/center][/th]";
        $stats .= "[th][center]Record[/center][/th][/tr]\n";
        
        // Daten für die letzten X Tage
        for ($i = 0; $i <= $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $displayDate = date('d.m.y', strtotime($date));
            
            // Unique Visits für diesen Tag
            $visits = $mongoDB->populationStats->countDocuments([
                'type' => 'uniqueVisit',
                'date' => $date
            ]);
            
            // Rekord für diesen Tag
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
    
    /**
     * Client-Verbindung verarbeiten für Unique Visits
     * Diese Funktion kann in notifycliententerview aufgerufen werden
     */
    public function handleClientConnect($ts, $mongoDB, $clientInfo) {
        try {
            // Ignorierte Gruppen prüfen
            $ignoredGroups = [283, 391];
            $clientGroups = explode(',', $clientInfo['client_servergroups'] ?? '');
            if (!empty(array_intersect($clientGroups, $ignoredGroups))) {
                return;
            }
            
            $uid = $clientInfo['client_unique_identifier'];
            $ip = $clientInfo['connection_client_ip'] ?? null;
            $today = date('Y-m-d');
            
            if (!$ip) return;
            
            // Prüfe ob heute schon besucht (UID oder IP)
            $existingVisit = $mongoDB->populationStats->findOne([
                'type' => 'uniqueVisit',
                'date' => $today,
                '$or' => [
                    ['uid' => $uid],
                    ['ip' => $ip]
                ]
            ]);
            
            if (!$existingVisit) {
                // Neuer Unique Visit
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
            echo "[PopulationStats] Client connect error: " . $e->getMessage() . "\n";
        }
    }
}