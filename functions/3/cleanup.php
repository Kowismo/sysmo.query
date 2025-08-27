<?php
class cleanup {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $cleanupCount = 0;
        $errorCount = 0;
        
        foreach($ezApp->clientList() as $client) {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $client['client_servergroups']) && $client['client_type'] == 0) {
                
                $hasVerified = $ezApp->inGroup([377], $client['client_servergroups']); // Verified
                $hasUnverified = $ezApp->inGroup([369], $client['client_servergroups']); // Unverified  
                $hasNew = $ezApp->inGroup([8], $client['client_servergroups']); // New
                $hasAnyLevel = $ezApp->inGroup([9,10,11,12,13,14,264,15,265,266,17,263,18,19,20,21,22,23,24,25,26,27,28,29,30,31], $client['client_servergroups']); // Alle Level
                
                $removed = [];
                
                // Wenn User Verified ist aber noch Unverified hat -> Unverified entfernen
                if($hasVerified && $hasUnverified) {
                    $ts->serverGroupDeleteClient(369, $client['client_database_id']);
                    $removed[] = 'Unverified (369)';
                    $cleanupCount++;
                }
                
                // Wenn User ein Level hat aber noch New Gruppe -> New entfernen
                if($hasAnyLevel && $hasNew) {
                    $ts->serverGroupDeleteClient(8, $client['client_database_id']);
                    $removed[] = 'New (8)';
                    $cleanupCount++;
                }
                
                // Wenn User Verified ist aber noch New hat (extra check)
                if($hasVerified && $hasNew && !$hasAnyLevel) {
                    // New nur entfernen wenn User auch ein Level haben sollte
                    // Prüfe ob User genug Zeit für Level 1 hat
                    $req = $mongoDB->serverClients->findOne(['clientUniqueIdentifier' => $client['client_unique_identifier']]);
                    if($req != null && ($req['timeSpent'] + $req['timeSpentAfk']) >= 3600) {
                        $ts->serverGroupDeleteClient(8, $client['client_database_id']);
                        $removed[] = 'New (8) - should have level';
                        $cleanupCount++;
                    }
                }
                
                if(!empty($removed)) {
                    $ezApp->createLog($mongoDB, __CLASS__, $client['client_unique_identifier'], $client['client_nickname'], 'CLEANUP: Removed groups: ' . implode(', ', $removed));
                }
            }
        }
        
        $ezApp->createLog($mongoDB, __CLASS__, 'SYSTEM', 'SYSTEM', 'Cleanup completed. Fixed ' . $cleanupCount . ' group assignments.');
    }
}