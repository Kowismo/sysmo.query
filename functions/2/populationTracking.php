<?php
class populationTracking {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        
        // Ignorierte Gruppen prüfen
        $clientGroups = explode(',', $clientInfo['client_servergroups'] ?? '');
        if (!empty(array_intersect($clientGroups, $cfg['ignoredGroups']))) {
            return;
        }
        
        $uid = $clientInfo['client_unique_identifier'];
        $ip = $clientInfo['connection_client_ip'] ?? null;
        $today = date('Y-m-d');
        
        if (!$ip) return;
        
        try {
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
            // Silent error handling
        }
    }
}
?>