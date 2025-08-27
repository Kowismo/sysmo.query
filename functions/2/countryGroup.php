<?php
class countryGroup {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        try {
            if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
                // 1. Logo-Gruppe zuweisen
                $logoGroup = 435; // ID der temporären Gruppe mit Logo
                $this->assignGroup($ts, $clientInfo, $logoGroup);
                
                // 2. Länderspezifische Gruppe zuweisen
                if(isset($clientInfo['client_country']) && isset($cfg['options'][$clientInfo['client_country']])) {
                    $countryGroup = $cfg['options'][$clientInfo['client_country']];
                    $this->assignGroup($ts, $clientInfo, $countryGroup);
                }
            }
        } catch (Exception $e) {
            // Silent error handling
        }
    }
    
    /**
     * Hilfsfunktion zum Zuweisen einer Gruppe
     */
    private function assignGroup($ts, $clientInfo, $groupId) {
        // Verbesserte Prüfung der Gruppenmitgliedschaft
        $userGroups = explode(',', $clientInfo['client_servergroups']);
        $hasGroup = false;
        foreach($userGroups as $group) {
            if((int)$group === (int)$groupId) {
                $hasGroup = true;
                break;
            }
        }
        
        if(!$hasGroup) {
            $addResult = $ts->serverGroupAddClient($groupId, $clientInfo['client_database_id']);
            
            // Zusätzliche Fehlerbehandlung für "duplicate entry"
            if(!$ts->succeeded($addResult) && isset($addResult['errors']) && is_array($addResult['errors'])) {
                $isDuplicate = false;
                foreach($addResult['errors'] as $error) {
                    if(strpos($error, 'duplicate entry') !== false) {
                        $isDuplicate = true;
                        break;
                    }
                }
                // Bei nicht-duplicate Fehlern wird nichts geloggt, läuft silent
            }
        }
    }
}