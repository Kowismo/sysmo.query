<?php
class groupAddingEvents {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp, $groupId = null, $action = null) {
        // Log-Verzeichnis definieren
        $logDir = '/home/query/logs/groupAddingEvents';
        
        // Sicherstellen, dass das Log-Verzeichnis existiert
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Log-Datei erstellen
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        
        // Grundlegende Infos loggen
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Processing groupAddingEvents for: " . $clientInfo['client_nickname'] . "\n", FILE_APPEND);
        
        try {
            // Wenn groupId und action nicht mitgegeben wurden, dann ist es ein normaler Aufruf
            // ohne spezifisches Trigger-Event
            if($groupId === null || $action === null) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - No specific trigger provided, skipping\n", FILE_APPEND);
                return;
            }
            
            // Wenn keine Events konfiguriert sind, dann Standard-Events verwenden (aus Screenshots)
            $events = isset($cfg['events']) ? $cfg['events'] : [
                // Wenn Gruppe 369 hinzugefügt wird, füge Gruppe 8 hinzu
                [
                    'triggerGroup' => 369,
                    'action' => 'add',
                    'groupsToAdd' => [8],
                    'groupsToRemove' => []
                ],
                // Wenn Gruppe 377 hinzugefügt wird, entferne Gruppe 369
                [
                    'triggerGroup' => 377,
                    'action' => 'add',
                    'groupsToAdd' => [],
                    'groupsToRemove' => [369]
                ],
                // Wenn Gruppe 9 hinzugefügt wird, entferne Gruppe 8 (mit Blacklist für Gruppen 283, 391)
                [
                    'triggerGroup' => 9,
                    'action' => 'add',
                    'groupsToAdd' => [],
                    'groupsToRemove' => [8],
                    'blacklistedGroups' => [283, 391]
                ]
            ];
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Checking events for trigger group: $groupId, action: $action\n", FILE_APPEND);
            
            // Passende Events suchen und verarbeiten
            foreach($events as $event) {
                if((int)$event['triggerGroup'] === (int)$groupId && $event['action'] === $action) {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Found matching event for group $groupId\n", FILE_APPEND);
                    
                    // Überprüfen ob der Client in einer der Blacklist-Gruppen ist
                    if(isset($event['blacklistedGroups']) && !empty($event['blacklistedGroups'])) {
                        if($ezApp->inGroup($event['blacklistedGroups'], $clientInfo['client_servergroups'])) {
                            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Client is in blacklisted group, skipping event\n", FILE_APPEND);
                            continue;
                        }
                    }
                    
                    // Hinzuzufügende Gruppen
                    if(isset($event['groupsToAdd']) && !empty($event['groupsToAdd'])) {
                        foreach($event['groupsToAdd'] as $addGroup) {
                            if(!$ezApp->inGroup([$addGroup], $clientInfo['client_servergroups'])) {
                                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Adding group $addGroup to client\n", FILE_APPEND);
                                
                                $result = $ts->serverGroupAddClient($addGroup, $clientInfo['client_database_id']);
                                $success = isset($result['success']) && $result['success'];
                                
                                // "duplicate entry" Fehler als Erfolg werten
                                if(!$success && isset($result['errors']) && is_array($result['errors'])) {
                                    foreach($result['errors'] as $error) {
                                        if(strpos($error, 'duplicate entry') !== false) {
                                            $success = true;
                                            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Group already assigned (detected from error), skipping\n", FILE_APPEND);
                                            break;
                                        }
                                    }
                                }
                                
                                if($success) {
                                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Successfully added group $addGroup\n", FILE_APPEND);
                                    $ezApp->createLog($mongoDB, __CLASS__, $clientInfo['client_unique_identifier'], 
                                                     $clientInfo['client_nickname'], "Added triggered group: $addGroup");
                                } else {
                                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Failed to add group. Error: " . json_encode($result) . "\n", FILE_APPEND);
                                }
                            } else {
                                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Client already has group $addGroup\n", FILE_APPEND);
                            }
                        }
                    }
                    
                    // Zu entfernende Gruppen
                    if(isset($event['groupsToRemove']) && !empty($event['groupsToRemove'])) {
                        foreach($event['groupsToRemove'] as $removeGroup) {
                            if($ezApp->inGroup([$removeGroup], $clientInfo['client_servergroups'])) {
                                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Removing group $removeGroup from client\n", FILE_APPEND);
                                
                                $result = $ts->serverGroupDeleteClient($removeGroup, $clientInfo['client_database_id']);
                                $success = isset($result['success']) && $result['success'];
                                
                                if($success) {
                                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Successfully removed group $removeGroup\n", FILE_APPEND);
                                    $ezApp->createLog($mongoDB, __CLASS__, $clientInfo['client_unique_identifier'], 
                                                     $clientInfo['client_nickname'], "Removed triggered group: $removeGroup");
                                } else {
                                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Failed to remove group. Error: " . json_encode($result) . "\n", FILE_APPEND);
                                }
                            } else {
                                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Client doesn't have group $removeGroup\n", FILE_APPEND);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Fehler protokollieren
            $errorMsg = "EXCEPTION: " . $e->getMessage() . "\n";
            $errorMsg .= "TRACE: " . $e->getTraceAsString() . "\n";
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - $errorMsg", FILE_APPEND);
        }
    }
}