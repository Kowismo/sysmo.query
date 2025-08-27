<?php
class privateChannelsChecker2 {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        try {
            // Log-Verzeichnis definieren
            $logDir = '/home/query/logs/privateChannelsChecker2';
            
            // Sicherstellen, dass das Log-Verzeichnis existiert
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            // Logging-Funktion
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            $logMessage = function($message) use ($logFile) {
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[$timestamp] $message\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            };
            
            $logMessage("Channel checker process started");
            
            $order = 0;
            $channels = $mongoDB->privateChannels2->find([], ['sort' => ['_id' => 1]])->toArray();
            $logMessage("Found " . count($channels) . " private channels to check");
            
            if(count($channels) > 0) {
                foreach($channels as $channel) {
                    $channelInfo = $ts->getElement('data', $ts->channelInfo($channel['channelId']));
                    
                    if($channelInfo) {
                        $logMessage("Checking channel ID: " . $channel['channelId']);
                        $usersInChannel = $ts->getElement('data', $ts->channelClientList($channel['channelId']));
                        $order++;
                        
                        // Channel Name extrahieren
                        $chNum = (int)$channelInfo['channel_name'];
                        $chNameParts = explode('. ', $channelInfo['channel_name'], 2);
                        if(count($chNameParts) == 2) {
                            $channelName = $chNameParts[1];
                        } else {
                            $channelName = $chNameParts[0];
                        }
                        
                        // Das Icon [🚮] entfernen, falls vorhanden
                        $channelName = str_replace('[🚮]', '', $channelName);
                        $channelName = trim($channelName); // Leerzeichen entfernen
                        
                        // Prüfen, ob die Kanalnummer geändert werden muss
                        if($chNum == 0 || $chNum != $order) {
                            $logMessage("Updating channel number from $chNum to $order");
                            $renameResult = $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. ' . $channelName]);
                            
                            if($ts->succeeded($renameResult)) {
                                // Den Besitzer benachrichtigen
                                $isOnline = false;
                                $onlineClients = $ts->getElement('data', $ts->clientList());
                                
                                if($onlineClients) {
                                    foreach($onlineClients as $client) {
                                        if(isset($client['client_unique_identifier']) && $client['client_unique_identifier'] == $channel['clientUniqueIdentifier']) {
                                            $isOnline = true;
                                            // Benachrichtigung senden (Englisch)
                                            $ts->clientPoke($client['clid'], "Your private channel has been renumbered from #{$chNum} to #{$order} to maintain sorting order.");
                                            $logMessage("Poke notification sent to channel owner about renumbering");
                                            break;
                                        }
                                    }
                                }
                                
                                // Wenn der Benutzer offline ist, speichern wir die Nachricht
                                if(!$isOnline) {
                                    $mongoDB->offlineMessages->insertOne([
                                        'clientUniqueIdentifier' => $channel['clientUniqueIdentifier'],
                                        'message' => "Your private channel has been renumbered from #{$chNum} to #{$order} to maintain sorting order.",
                                        'isPokeMessage' => true,
                                        'timestamp' => new MongoDB\BSON\UTCDateTime()
                                    ]);
                                    $logMessage("Offline message saved for channel owner about renumbering");
                                }
                            } else {
                                $logMessage("Failed to rename channel: " . json_encode($renameResult));
                            }
                            continue;
                        }
                        
                        // Prüfen ob Benutzer im Channel sind
                        if (isset($usersInChannel) && !empty($usersInChannel)) {
                            // Hier verschiedene Möglichkeiten prüfen, wie die Benutzeranzahl im Channel sein könnte
                            $hasActiveUsers = false;
                            
                            if (isset($usersInChannel['total_clients']) && is_numeric($usersInChannel['total_clients']) && $usersInChannel['total_clients'] > 0) {
                                $hasActiveUsers = true;
                            } elseif (isset($usersInChannel) && is_array($usersInChannel) && count($usersInChannel) > 0) {
                                $hasActiveUsers = true;
                            }
                            
                            if ($hasActiveUsers) {
                                // Channel ist aktiv, Name ohne Icon
                                $logMessage("Channel has active users, removing trash icon if present");
                                $renameResult = $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. ' . $channelName]);
                                if(!$ts->succeeded($renameResult)) {
                                    $logMessage("Failed to rename channel: " . json_encode($renameResult));
                                }
                                continue;
                            }
                        }
                        
                        // Prüfen, ob der Channel gelöscht werden soll
                        if($channelInfo['seconds_empty'] > $cfg['daysExpire'] * 86400) {
                            $logMessage("Channel empty for more than " . $cfg['daysExpire'] . " days, deleting channel ID: " . $channel['channelId']);
                            $deleteDBResult = $mongoDB->privateChannels2->deleteOne(['channelId' => (int)$channel['channelId']]);
                            if($deleteDBResult->getDeletedCount() == 0) {
                                $logMessage("Failed to delete channel from database, ID: " . $channel['channelId']);
                            }
                            
                            $deleteResult = $ts->channelDelete($channel['channelId'], 1);
                            if(!$ts->succeeded($deleteResult)) {
                                $logMessage("Failed to delete channel from server: " . json_encode($deleteResult));
                            }
                            continue;
                        }
                        
                        // Channel als inaktiv markieren, wenn nötig
                        if($channelInfo['seconds_empty'] > $cfg['daysExpire'] * 43200) {
                            // Icon hinzufügen, wenn es noch nicht im Namen ist
                            if(strpos($channelName, '[🚮]') === false) {
                                $logMessage("Channel inactive, adding trash icon to name");
                                $renameResult = $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. [🚮]' . $channelName]);
                                if(!$ts->succeeded($renameResult)) {
                                    $logMessage("Failed to rename channel: " . json_encode($renameResult));
                                }
                            }
                            continue;
                        } else {
                            // Channel ist nicht inaktiv genug, also normaler Name ohne Icon
                            $logMessage("Channel is active, ensuring normal name");
                            $renameResult = $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. ' . $channelName]);
                            if(!$ts->succeeded($renameResult)) {
                                $logMessage("Failed to rename channel: " . json_encode($renameResult));
                            }
                        }
                    } else {
                        // Channel existiert nicht mehr, aus der Datenbank löschen
                        $logMessage("Channel not found on server, removing from database. ID: " . $channel['channelId']);
                        $deleteDBResult = $mongoDB->privateChannels2->deleteOne(['channelId' => (int)$channel['channelId']]);
                        if($deleteDBResult->getDeletedCount() == 0) {
                            $logMessage("Failed to delete channel from database, ID: " . $channel['channelId']);
                        }
                    }
                }
            }
            
            $logMessage("Channel checker process completed");
            
        } catch (Exception $e) {
            // Definiere Log-Verzeichnis wenn noch nicht geschehen
            if (!isset($logDir)) {
                $logDir = '/home/query/logs/privateChannelsChecker2';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
            }
            
            // Direktes Logging im Catch-Block
            $logFile = $logDir . '/' . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $errorMsg = "[$timestamp] EXCEPTION: " . $e->getMessage() . "\n";
            $errorMsg .= "[$timestamp] TRACE: " . $e->getTraceAsString() . "\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
        }
    }
}