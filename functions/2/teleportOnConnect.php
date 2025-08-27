<?php
class teleportOnConnect {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        try {
            // Log-Verzeichnis definieren
            $logDir = '/home/query/logs/teleportOnConnect';
            
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
            
            // Ignoriere Bots und Admin-Gruppen falls konfiguriert
            if (!$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
                $logMessage("Checking for teleport settings on connect");
                
                $clientUID = $clientInfo['client_unique_identifier'];
                
                // Prüfe ob ein Teleport-Eintrag für diesen Benutzer existiert
                $teleportEntry = $mongoDB->channelTeleports->findOne([
                    'clientUniqueIdentifier' => $clientUID
                ]);
                
                if ($teleportEntry) {
                    $targetChannelId = $teleportEntry['targetChannelId'];
                    $channelName = isset($teleportEntry['channelName']) ? $teleportEntry['channelName'] : 'Unknown';
                    
                    $logMessage("Found teleport entry for channel ID: {$targetChannelId} ({$channelName})");
                    
                    // Prüfe ob der Ziel-Channel noch existiert
                    $channelInfo = $ts->getElement('data', $ts->channelInfo($targetChannelId));
                    
                    if ($channelInfo) {
                        $logMessage("Target channel exists, checking availability");
                        
                        // Prüfe Channel-Verfügbarkeit
                        $canJoin = true;
                        $errorReason = '';
                        
                        // Prüfe Maxclients
                        if (isset($channelInfo['channel_maxclients']) && $channelInfo['channel_maxclients'] > 0) {
                            $clientsInChannel = $ts->getElement('data', $ts->channelClientList($targetChannelId));
                            $currentClients = 0;
                            
                            if ($clientsInChannel) {
                                if (isset($clientsInChannel['total_clients'])) {
                                    $currentClients = (int)$clientsInChannel['total_clients'];
                                } elseif (is_array($clientsInChannel)) {
                                    $currentClients = count($clientsInChannel);
                                }
                            }
                            
                            if ($currentClients >= $channelInfo['channel_maxclients']) {
                                $canJoin = false;
                                $errorReason = 'Channel is full';
                                $logMessage("Channel is full ({$currentClients}/{$channelInfo['channel_maxclients']})");
                            }
                        }
                        
                        // Channel Groups haben "Passwort ignorieren" Recht - kein Problem mit Passwörtern
                        if (isset($channelInfo['channel_flag_password']) && $channelInfo['channel_flag_password'] == 1) {
                            $logMessage("Channel is password protected, but teleport groups have ignore password permission");
                        }
                        
                        if ($canJoin) {
                            $logMessage("Attempting to teleport user to channel");
                            
                            // Kurz warten um sicherzustellen dass der Client vollständig verbunden ist
                            sleep(1);
                            
                            // Teleportiere den Benutzer
                            $moveResult = $ts->clientMove($clientInfo['clid'], $targetChannelId);
                            
                            if ($ts->succeeded($moveResult)) {
                                $logMessage("Successfully teleported user to channel: {$channelName}");
                                
                                // Optional: Willkommensnachricht senden
                                sleep(1); // Kurz warten nach dem Move
                                $ts->sendMessage(1, $clientInfo['clid'], 
                                    "[b][color=#00bf30]🚀 Auto-Teleport![/color][/b] Welcome to '[b][color=#7be24c]{$channelInfo['channel_name']}[/color][/b]'!"
                                );
                                
                            } else {
                                $logMessage("Failed to move client: " . json_encode($moveResult));
                                
                                // Benachrichtige den Benutzer über den Fehler
                                $ts->sendMessage(1, $clientInfo['clid'], 
                                    "[b][color=#cf2157]⚠️ Teleport Failed![/color][/b] Could not move you to '[b]{$channelName}[/b]'. Please check manually."
                                );
                            }
                        } else {
                            $logMessage("Cannot join target channel: {$errorReason}");
                            
                            // Benachrichtige den Benutzer
                            $ts->sendMessage(1, $clientInfo['clid'], 
                                "[b][color=#ffed00]⚠️ Teleport Unavailable![/color][/b] Your target channel '[b]{$channelName}[/b]' is currently {$errorReason}."
                            );
                        }
                        
                    } else {
                        $logMessage("Target channel no longer exists, removing teleport entry");
                        
                        // Channel existiert nicht mehr, entferne den Eintrag
                        $mongoDB->channelTeleports->deleteOne([
                            'clientUniqueIdentifier' => $clientUID,
                            'targetChannelId' => $targetChannelId
                        ]);
                        
                        // Benachrichtige den Benutzer
                        $ts->sendMessage(1, $clientInfo['clid'], 
                            "[b][color=#cf2157]⚠️ Teleport Removed![/color][/b] Your teleport target '[b]{$channelName}[/b]' no longer exists."
                        );
                    }
                } else {
                    $logMessage("No teleport entry found for this user");
                }
            } else {
                $logMessage("User in ignored groups, skipping teleport check");
            }
            
        } catch (Exception $e) {
            // Definiere Log-Verzeichnis wenn noch nicht geschehen
            if (!isset($logDir)) {
                $logDir = '/home/query/logs/teleportOnConnect';
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