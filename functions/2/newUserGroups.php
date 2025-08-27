<?php
class newUserGroups {
    public function __construct($ts, $clientData, $mongoDB, $cfg, $ezApp) {
        // Nur bei echten Connects (nicht bei Bots)
        if($clientData['client_type'] == 0 && !$ezApp->inGroup($cfg['ignoredGroups'], $clientData['client_servergroups'])) {
            
            // Prüfe ob User bereits wichtige Gruppen hat
            $hasVerified = $ezApp->inGroup([377], $clientData['client_servergroups']); // Verified
            $hasAnyLevel = $ezApp->inGroup([9,10,11,12,13,14,264,15,265,266,17,263,18,19,20,21,22,23,24,25,26,27,28,29,30,31], $clientData['client_servergroups']); // Alle Level
            
            // NUR wenn User noch KEINE wichtigen Gruppen hat
            if(!$hasVerified && !$hasAnyLevel) {
                
                // Füge New Gruppe hinzu
                if(!$ezApp->inGroup([8], $clientData['client_servergroups'])) {
                    $ts->serverGroupAddClient(8, $clientData['client_database_id']);
                }
                
                // Füge Unverified Gruppe hinzu  
                if(!$ezApp->inGroup([369], $clientData['client_servergroups'])) {
                    $ts->serverGroupAddClient(369, $clientData['client_database_id']);
                }
            }
        }
    }
}