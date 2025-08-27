<?php
class debugTeleportScanner {
    public function __construct($ts, $mongoDB, $cfg, $ezApp) {
        try {
            $logDir = '/home/query/logs/debugTeleportScanner';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logFile = $logDir . '/' . date('Y-m-d') . '_debug.log';
            $logMessage = function($message) use ($logFile) {
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[$timestamp] $message\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
                echo $logEntry; // Auch in Console ausgeben
            };
            
            $logMessage("=== DEBUG TELEPORT SCANNER ===");
            
            // 1. Alle Channel Groups anzeigen
            $logMessage("1. ALLE CHANNEL GROUPS:");
            $channelGroups = $ts->getElement('data', $ts->channelGroupList());
            
            if ($channelGroups) {
                foreach ($channelGroups as $group) {
                    $cgid = $group['cgid'] ?? 'unknown';
                    $name = $group['name'] ?? 'unknown';
                    $logMessage("   Group ID: $cgid = $name");
                }
            } else {
                $logMessage("   ERROR: Could not get channel groups!");
            }
            
            $logMessage("");
            
            // 2. Alle Online Clients und ihre Channel Groups anzeigen
            $logMessage("2. ONLINE CLIENTS UND IHRE CHANNEL GROUPS:");
            $onlineClients = $ts->getElement('data', $ts->clientList());
            
            if ($onlineClients) {
                foreach ($onlineClients as $client) {
                    $clid = $client['clid'] ?? 0;
                    $nickname = $client['client_nickname'] ?? 'unknown';
                    $clientUID = $client['client_unique_identifier'] ?? '';
                    
                    // Ignoriere Query-Clients
                    if (isset($client['client_type']) && $client['client_type'] == 1) {
                        continue;
                    }
                    
                    $logMessage("   Client: $nickname ($clientUID)");
                    
                    // Client Info abrufen
                    $clientInfo = $ts->getElement('data', $ts->clientInfo($clid));
                    if ($clientInfo) {
                        $currentChannelId = $clientInfo['cid'] ?? 0;
                        $currentChannelGroupId = $clientInfo['client_channel_group_id'] ?? 0;
                        
                        $logMessage("     Current Channel: $currentChannelId");
                        $logMessage("     Current Channel Group: $currentChannelGroupId");
                        
                        // Channel Group Name finden
                        if ($channelGroups) {
                            foreach ($channelGroups as $group) {
                                if (($group['cgid'] ?? 0) == $currentChannelGroupId) {
                                    $groupName = $group['name'] ?? 'unknown';
                                    $logMessage("     Channel Group Name: $groupName");
                                    
                                    // Prüfe ob es "+Teleport" enthält
                                    if (strpos($groupName, '+Teleport') !== false || strpos($groupName, 'Teleport') !== false) {
                                        $logMessage("     *** TELEPORT GROUP FOUND! ***");
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    
                    $logMessage("");
                }
            }
            
            $logMessage("");
            
            // 3. Alle Channels durchgehen und Channel Group Assignments anzeigen
            $logMessage("3. CHANNEL GROUP ASSIGNMENTS PRO CHANNEL:");
            $channels = $ts->getElement('data', $ts->channelList());
            
            if ($channels) {
                foreach ($channels as $channel) {
                    $channelId = $channel['cid'] ?? 0;
                    $channelName = $channel['channel_name'] ?? 'unknown';
                    
                    $logMessage("   Channel: $channelName (ID: $channelId)");
                    
                    try {
                        $channelGroupClients = $ts->getElement('data', 
                            $ts->channelGroupClientList(null, $channelId, null)
                        );
                        
                        if ($channelGroupClients && count($channelGroupClients) > 0) {
                            foreach ($channelGroupClients as $cgClient) {
                                $cgClientUID = $cgClient['client_unique_identifier'] ?? '';
                                $cgid = $cgClient['cgid'] ?? 0;
                                $cldbid = $cgClient['cldbid'] ?? 0;
                                
                                // Channel Group Name finden
                                $groupName = 'unknown';
                                if ($channelGroups) {
                                    foreach ($channelGroups as $group) {
                                        if (($group['cgid'] ?? 0) == $cgid) {
                                            $groupName = $group['name'] ?? 'unknown';
                                            break;
                                        }
                                    }
                                }
                                
                                $logMessage("     UID: $cgClientUID, Group: $cgid ($groupName)");
                                
                                // Prüfe ob es Teleport-Group ist
                                if (strpos($groupName, '+Teleport') !== false || strpos($groupName, 'Teleport') !== false) {
                                    $logMessage("     *** TELEPORT ASSIGNMENT FOUND! ***");
                                }
                            }
                        } else {
                            $logMessage("     No channel group assignments in this channel");
                        }
                    } catch (Exception $e) {
                        $logMessage("     Error getting channel group clients: " . $e->getMessage());
                    }
                    
                    $logMessage("");
                }
            }
            
            $logMessage("=== DEBUG COMPLETE ===");
            
        } catch (Exception $e) {
            $logFile = '/home/query/logs/debugTeleportScanner/' . date('Y-m-d') . '_error.log';
            $timestamp = date('Y-m-d H:i:s');
            $errorMsg = "[$timestamp] EXCEPTION: " . $e->getMessage() . "\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
        }
    }
}