<?php
class antiChannelHopping {
    private static $clients = []; // Statisches Array für User-Daten
    private static $lastChannels = []; // Letzte Channel-Positionen

    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        // Hole aktuelle Client-Liste
        $clientList = $ts->getElement('data', $ts->clientList('-uid -away -voice -times -groups -info -icon -country -ip -badges'));
        $currentTime = time();
        
        if($clientList) {
            foreach($clientList as $client) {
                // Nur echte User, keine Bots
                if($client['client_type'] == 0 && !$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups'])) {
                    
                    $clientUID = $client['client_unique_identifier'];
                    $currentChannel = $client['cid'];
                    
                    // Prüfe ob User Channel gewechselt hat
                    if(isset(self::$lastChannels[$clientUID])) {
                        $lastChannel = self::$lastChannels[$clientUID];
                        
                        // Channel-Wechsel erkannt!
                        if($lastChannel != $currentChannel) {
                            $this->handleChannelMove($ts, $client, $cfg, $mongoDB, $ezApp, $currentTime);
                        }
                    }
                    
                    // Aktualisiere letzte Channel-Position
                    self::$lastChannels[$clientUID] = $currentChannel;
                }
            }
        }
        
        // Cleanup alte Einträge (Memory-Management)
        $this->cleanupOldEntries($currentTime, $cfg['timeWindow']);
    }
    
    private function handleChannelMove($ts, $client, $cfg, $mongoDB, $ezApp, $currentTime) {
        $clientUID = $client['client_unique_identifier'];
        
        // Prüfe ob User-Daten bereits existieren
        if(!isset(self::$clients[$clientUID])) {
            // Erstelle neue User-Daten
            self::$clients[$clientUID] = [
                'count' => 0,
                'firstMove' => $currentTime,
                'nickname' => $client['client_nickname']
            ];
        }
        
        // Erhöhe Move-Counter
        self::$clients[$clientUID]['count']++;
        
        // Prüfe ob Time-Window abgelaufen ist
        $timeWindow = $currentTime - self::$clients[$clientUID]['firstMove'];
        
        if($timeWindow >= $cfg['timeWindow']) {
            // Time-Window abgelaufen -> Reset Counter
            self::$clients[$clientUID] = [
                'count' => 1, // Aktueller Move zählt
                'firstMove' => $currentTime,
                'nickname' => $client['client_nickname']
            ];
        }
        
        // DEBUG Log
        file_put_contents('/home/query/logs/DEBUG_ANTI_HOPPING.log', 
            date('Y-m-d H:i:s') . " - User: " . $client['client_nickname'] . 
            " | Moves: " . self::$clients[$clientUID]['count'] . 
            " | Time: " . $timeWindow . "s\n", FILE_APPEND);
        
        // Prüfe ob Limit überschritten
        if(self::$clients[$clientUID]['count'] >= $cfg['maxMoves']) {
            // KICK USER!
            $ts->clientKick($client['clid'], 5, $cfg['kickMessage']); // 5 = Server Kick
            
            // Log erstellen
            file_put_contents('/home/query/logs/antiChannelHopping.log', 
                date('Y-m-d H:i:s') . " - KICKED: " . $client['client_nickname'] . 
                " (" . $cfg['maxMoves'] . " moves in " . $cfg['timeWindow'] . " seconds)\n", FILE_APPEND);
            
            // Lösche User-Daten
            unset(self::$clients[$clientUID]);
            unset(self::$lastChannels[$clientUID]);
            
            // Optional: Admin benachrichtigen
            if($cfg['notifyAdmins']) {
                $this->notifyAdmins($ts, $cfg, $client, $ezApp);
            }
        }
    }
    
    private function notifyAdmins($ts, $cfg, $client, $ezApp) {
        $adminClients = $ts->getElement('data', $ts->clientList());
        if($adminClients) {
            foreach($adminClients as $admin) {
                if($admin['client_type'] == 0 && $ezApp->inGroup($cfg['adminGroups'], $admin['client_servergroups'])) {
                    $message = str_replace(
                        ['%clientNickname%', '%moves%', '%time%'], 
                        [$client['client_nickname'], $cfg['maxMoves'], $cfg['timeWindow']], 
                        $cfg['adminMessage']
                    );
                    $ts->sendMessage(1, $admin['clid'], $message);
                }
            }
        }
    }
    
    private function cleanupOldEntries($currentTime, $timeWindow) {
        // Cleanup User-Daten
        foreach(self::$clients as $uid => $data) {
            if(($currentTime - $data['firstMove']) > ($timeWindow * 2)) {
                unset(self::$clients[$uid]);
            }
        }
        
        // Cleanup Channel-Tracking (nach 5 Minuten)
        if(count(self::$lastChannels) > 200) {
            self::$lastChannels = array_slice(self::$lastChannels, -100, null, true);
        }
    }
}