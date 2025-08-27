<?php
class clientPlatform {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        // Debug-Log erstellen
        $logDir = '/home/query/logs/clientPlatform';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - clientPlatform processing for: " . $clientInfo['client_nickname'] . "\n", FILE_APPEND);
        
        if(!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
            // Logging der Client-Informationen
            $logEntry = date('Y-m-d H:i:s') . " - Client info: " . 
                        ", Platform: " . (isset($clientInfo['client_platform']) ? $clientInfo['client_platform'] : 'UNKNOWN') . 
                        ", Groups: " . $clientInfo['client_servergroups'] . "\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            
            if(isset($clientInfo['client_platform']) && isset($cfg['options'][$clientInfo['client_platform']])) {
                $platformGroup = $cfg['options'][$clientInfo['client_platform']];
                
                // Verbesserte Prüfung, ob die Gruppe bereits zugewiesen ist
                $hasGroup = false;
                $clientGroups = explode(',', $clientInfo['client_servergroups']);
                foreach($clientGroups as $group) {
                    if((int)$group === (int)$platformGroup) {
                        $hasGroup = true;
                        break;
                    }
                }
                
                if(!$hasGroup) {
                    $result = $ts->serverGroupAddClient($platformGroup, $clientInfo['client_database_id']);
                    $success = isset($result['success']) && $result['success'];
                    
                    // Wenn ein "duplicate entry" Fehler auftritt, betrachten wir das nicht als Fehler
                    if(!$success && isset($result['errors']) && is_array($result['errors'])) {
                        foreach($result['errors'] as $error) {
                            if(strpos($error, 'duplicate entry') !== false) {
                                $success = true;
                                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Group already assigned (detected from error), skipping\n", FILE_APPEND);
                                break;
                            }
                        }
                    }
                    
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Adding platform group " . $platformGroup . 
                                      " for client " . $clientInfo['client_nickname'] . " - " . 
                                      ($success ? "SUCCESS" : "FAILED: " . json_encode($result)) . "\n", FILE_APPEND);
                    
                    if($success) {
                        $ezApp->createLog($mongoDB, __CLASS__, $clientInfo['client_unique_identifier'], 
                                         $clientInfo['client_nickname'], 'Added platform group: ' . $platformGroup);
                    }
                } else {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Client already has platform group " . 
                                      $platformGroup . "\n", FILE_APPEND);
                }
            } else {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - No matching platform group found for " . 
                                  (isset($clientInfo['client_platform']) ? $clientInfo['client_platform'] : 'UNKNOWN') . 
                                  "\n", FILE_APPEND);
            }
        } else {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Client in ignored group, skipping\n", FILE_APPEND);
        }
    }
}