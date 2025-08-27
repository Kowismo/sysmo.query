<?php
class teleportChannelGroups {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        try {
            // Log-Verzeichnis definieren
            $logDir = '/home/query/logs/teleportChannelGroups';
            
            // Sicherstellen, dass das Log-Verzeichnis existiert
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            // Logging-Funktion
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            $logMessage = function($message) use ($logFile, $clientInfo) {
                $timestamp = date('Y-m-d H:i:s');
                $clientId = isset($clientInfo['client_unique_identifier']) ? $clientInfo['client_unique_identifier'] : 'unknown';
                $nickname = isset($clientInfo['client_nickname']) ? $clientInfo['client_nickname'] : 'unknown';
                $logEntry = "[$timestamp] [$clientId] [$nickname] $message\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            };
            
            $logMessage("Channel group change detected");
            
            // Teleport Channel Group IDs aus der Konfiguration
            $teleportGroups = $cfg['teleportChannelGroups'];
            
            // Aktuelle und alte Channel Group ID
            $newGroupId = isset($clientInfo['cgid']) ? (int)$clientInfo['cgid'] : 0;
            $oldGroupId = isset($clientInfo['cgid_old']) ? (int)$clientInfo['cgid_old'] : 0;
            $channelId = isset($clientInfo['cid']) ? (int)$clientInfo['cid'] : 0;
            $clientUID = $clientInfo['client_unique_identifier'];
            
            $logMessage("Group change: Old={$oldGroupId}, New={$newGroupId}, Channel={$channelId}");
            
            // Prüfen ob die neue Group eine Teleport-Group ist
            $isNewGroupTeleport = in_array($newGroupId, $teleportGroups);
            $isOldGroupTeleport = in_array($oldGroupId, $teleportGroups);
            
            if ($isNewGroupTeleport) {
                $logMessage("New group is a teleport group, setting up teleport");
                
                // Alle alten Teleport-Einträge dieser Person löschen
                $deleteResult = $mongoDB->channelTeleports->deleteMany([
                    'clientUniqueIdentifier' => $clientUID
                ]);
                
                $logMessage("Deleted {$deleteResult->getDeletedCount()} old teleport entries");
                
                // Channel-Info abrufen für Validierung
                $channelInfo = $ts->getElement('data', $ts->channelInfo($channelId));
                
                if ($channelInfo) {
                    // Neuen Teleport-Eintrag erstellen
                    $insertResult = $mongoDB->channelTeleports->insertOne([
                        'clientUniqueIdentifier' => $clientUID,
                        'clientNickname' => $clientInfo['client_nickname'],
                        'targetChannelId' => $channelId,
                        'channelName' => $channelInfo['channel_name'],
                        'channelGroupId' => $newGroupId,
                        'setAt' => new MongoDB\BSON\UTCDateTime(),
                        'setBy' => 'auto_system' // Falls später manuell gesetzt werden kann
                    ]);
                    
                    if ($insertResult->getInsertedCount()) {
                        $logMessage("Successfully created teleport entry for channel '{$channelInfo['channel_name']}'");
                        
                        // Optional: Benutzer benachrichtigen (falls online)
                        $onlineClients = $ts->getElement('data', $ts->clientList());
                        if ($onlineClients) {
                            foreach ($onlineClients as $client) {
                                if (isset($client['client_unique_identifier']) && 
                                    $client['client_unique_identifier'] == $clientUID) {
                                    $ts->sendMessage(1, $client['clid'], 
                                        "[b][color=#00bf30]Teleport activated![/color][/b] You will now be automatically moved to '[b][color=#7be24c]{$channelInfo['channel_name']}[/color][/b]' when connecting to the server."
                                    );
                                    break;
                                }
                            }
                        }
                    } else {
                        $logMessage("Failed to create teleport entry in database");
                    }
                } else {
                    $logMessage("Channel {$channelId} not found, cannot create teleport entry");
                }
                
            } elseif ($isOldGroupTeleport && !$isNewGroupTeleport) {
                $logMessage("Old group was teleport, new is not - removing teleport entry");
                
                // Teleport-Eintrag entfernen
                $deleteResult = $mongoDB->channelTeleports->deleteMany([
                    'clientUniqueIdentifier' => $clientUID,
                    'targetChannelId' => $channelId
                ]);
                
                $logMessage("Removed {$deleteResult->getDeletedCount()} teleport entries");
                
                // Optional: Benutzer benachrichtigen (falls online)
                $onlineClients = $ts->getElement('data', $ts->clientList());
                if ($onlineClients) {
                    foreach ($onlineClients as $client) {
                        if (isset($client['client_unique_identifier']) && 
                            $client['client_unique_identifier'] == $clientUID) {
                            $ts->sendMessage(1, $client['clid'], 
                                "[b][color=#cf2157]Teleport deactivated![/color][/b] Auto-teleport has been removed."
                            );
                            break;
                        }
                    }
                }
            } else {
                $logMessage("No teleport group involved, ignoring change");
            }
            
        } catch (Exception $e) {
            // Definiere Log-Verzeichnis wenn noch nicht geschehen
            if (!isset($logDir)) {
                $logDir = '/home/query/logs/teleportChannelGroups';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
            }
            
            // Direktes Logging im Catch-Block
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $clientId = isset($clientInfo['client_unique_identifier']) ? $clientInfo['client_unique_identifier'] : 'unknown';
            $nickname = isset($clientInfo['client_nickname']) ? $clientInfo['client_nickname'] : 'unknown';
            $errorMsg = "[$timestamp] [$clientId] [$nickname] EXCEPTION: " . $e->getMessage() . "\n";
            $errorMsg .= "[$timestamp] [$clientId] [$nickname] TRACE: " . $e->getTraceAsString() . "\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
        }
    }
}