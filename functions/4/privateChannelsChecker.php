<?php
class privateChannelsChecker {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $order = 0;
        $channels = $mongoDB->privateChannels->find([], ['sort' => ['_id' => 1]])->toArray();
        
        if(count($channels) > 0) {
            foreach($channels as $channel) {
                $channelInfo = $ts->getElement('data', $ts->channelInfo($channel['channelId']));
                
                if($channelInfo) {
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
                        // Kanal umbenennen
                        $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. ' . $channelName]);
                        
                        // Den Besitzer benachrichtigen
                        $isOnline = false;
                        $onlineClients = $ts->getElement('data', $ts->clientList());
                        
                        if($onlineClients) {
                            foreach($onlineClients as $client) {
                                if(isset($client['client_unique_identifier']) && $client['client_unique_identifier'] == $channel['clientUniqueIdentifier']) {
                                    $isOnline = true;
                                    // Benachrichtigung senden (Englisch)
                                    $ts->clientPoke($client['clid'], "Your private channel has been renumbered from #{$chNum} to #{$order} to maintain sorting order.");
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
                            $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. ' . $channelName]);
                            continue;
                        }
                    }
                    
                    // Prüfen, ob der Channel gelöscht werden soll
                    if($channelInfo['seconds_empty'] > $cfg['daysExpire'] * 86400) {
                        $mongoDB->privateChannels->deleteOne(['channelId' => (int)$channel['channelId']]);
                        $ts->channelDelete($channel['channelId'], 1);
                        continue;
                    }
                    
                    // Channel als inaktiv markieren, wenn nötig
                    if($channelInfo['seconds_empty'] > $cfg['daysExpire'] * 43200) {
                        // Icon hinzufügen, wenn es noch nicht im Namen ist
                        if(strpos($channelName, '[🚮]') === false) {
                            $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. [🚮]' . $channelName]);
                        }
                        continue;
                    } else {
                        // Channel ist nicht inaktiv genug, also normaler Name ohne Icon
                        $ts->channelEdit($channel['channelId'], ['channel_name' => $order . '. ' . $channelName]);
                    }
                } else {
                    // Channel existiert nicht mehr, aus der Datenbank löschen
                    $mongoDB->privateChannels->deleteOne(['channelId' => (int)$channel['channelId']]);
                    $ts->channelDelete($channel['channelId'], 1);
                }
            }
        }
    }
}