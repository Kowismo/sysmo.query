<?php
class privateChannels {
    public function __construct($ts, $clientInfo, $mongoDB, $cfg, $ezApp) {
        try {
            // Log-Verzeichnis definieren
            $logDir = '/home/query/logs/privateChannels';
            
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
            
            $logMessage("Channel creation process started");
            
            if($ezApp->inGroup($cfg['registerGroups'], $clientInfo['client_servergroups']) && !$ezApp->inGroup($cfg['ignoredGroups'], $clientInfo['client_servergroups'])) {
                $logMessage("User has correct permissions");
                
                // Prüfen, ob der Benutzer einen Kanal im anderen System hat
                $otherChannelSystem = $mongoDB->privateChannels2->findOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']]);
                if($otherChannelSystem) {
                    $logMessage("User has a channel in the other system (privateChannels2), channel ID: " . $otherChannelSystem['channelId']);
                    
                    // Prüfen, ob die Cooldown-Zeit abgelaufen ist
                    $lastChannelSwitch = $mongoDB->channelSwitchCooldown->findOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']]);
                    if($lastChannelSwitch) {
                        $cooldownEndTime = $lastChannelSwitch['switchTime']->toDateTime()->modify('+1 day');
                        $currentTime = new DateTime();
                        
                        if($currentTime < $cooldownEndTime) {
                            // Cooldown ist noch aktiv
                            $remainingTime = $currentTime->diff($cooldownEndTime);
                            $remainingHours = ($remainingTime->days * 24) + $remainingTime->h;
                            $remainingMinutes = $remainingTime->i;
                            
                            $logMessage("Channel switch cooldown active, remaining time: {$remainingHours}h {$remainingMinutes}m");
                            $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                            $ts->sendMessage(1, $clientInfo['clid'], "You need to wait {$remainingHours} hours and {$remainingMinutes} minutes before you can switch channel types again.");
                            return;
                        }
                    }
                    
                    // Lösche den alten Kanal
                    $logMessage("Deleting old channel from other system, ID: " . $otherChannelSystem['channelId']);
                    $channelExists = $ts->getElement('data', $ts->channelInfo($otherChannelSystem['channelId']));
                    if($channelExists) {
                        $ts->channelDelete($otherChannelSystem['channelId'], 1);
                    }
                    $mongoDB->privateChannels2->deleteOne(['channelId' => (int)$otherChannelSystem['channelId']]);
                    
                    // Speichere die Wechselzeit
                    $mongoDB->channelSwitchCooldown->updateOne(
                        ['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']],
                        ['$set' => ['switchTime' => new MongoDB\BSON\UTCDateTime()]],
                        ['upsert' => true]
                    );
                    
                    $logMessage("Channel switch cooldown set for 24 hours");
                }
                
                // Prüfe, ob der Benutzer bereits einen Kanal in diesem System hat
                $checkChannel = $mongoDB->privateChannels->findOne(['clientUniqueIdentifier' => $clientInfo['client_unique_identifier']]);
                
                if($checkChannel == null) {
                    $logMessage("User does not have an existing channel, creating new one");
                    // Zähle die aktuelle Anzahl von Kanälen
                    $privateChannels = $mongoDB->privateChannels->find()->toArray();
                    $channelNumber = count($privateChannels) + 1;
                    $channelPassword = $ezApp->generateString($cfg['channels']['settings']['passwordLength']);
                    
                    $logMessage("Attempting to create channel #$channelNumber");
                    
                    // Versuche, den Kanal zu erstellen
                    $channelCreateResult = $ts->channelCreate([
                        'channel_name' => str_replace(['%i%', '%clientNickname%'], [$channelNumber, $clientInfo['client_nickname']], $cfg['channels']['main']['channelName']),
                        'channel_description' => str_replace(['%clientId%', '%channelCreated%'], [$ezApp->createId($clientInfo), date('d/m/Y H:i:s')], $cfg['channels']['main']['channelDescription']),
                        'channel_password' => $channelPassword,
                        'channel_flag_permanent' => 1,
                        'channel_flag_maxclients_unlimited' => 1,
                        'channel_codec_quality' => 10,
                        'channel_flag_maxfamilyclients_unlimited' => 1,
                        'cpid' => (int)$cfg['channels']['settings']['createUnder'],
                    ]);
                    
                    $logMessage("Channel creation result: " . json_encode($channelCreateResult));
                    
                    $channelInfo = $ts->getElement('data', $channelCreateResult);
                    if($channelInfo) {
                        $logMessage("Channel created successfully with ID: " . $channelInfo['cid']);
                        
                        // Kanal wurde erfolgreich erstellt, erstelle Unterkanäle
                        for($i = 0; $i < (int)$cfg['channels']['subChannels']['channelsCount']; $i++) {
                            $logMessage("Creating subchannel #" . ($i+1));
                            $subChannelResult = $ts->channelCreate([
                                'channel_name' => str_replace(['%i%'], [$i+1], $cfg['channels']['subChannels']['channelName']),
                                'channel_description' => str_replace(['%i%'], [$i+1], $cfg['channels']['subChannels']['channelDescription']),
                                'channel_password' => $channelPassword,
                                'channel_flag_permanent' => 1,
                                'channel_flag_maxclients_unlimited' => 1,
                                'channel_codec_quality' => 10,
                                'cpid' => $channelInfo['cid'],
                            ]);
                            
                            // Überprüfe, ob der Unterkanal erstellt wurde
                            if(!$ts->getElement('data', $subChannelResult)) {
                                $logMessage("Failed to create subchannel #" . ($i+1) . ": " . json_encode($subChannelResult));
                            }
                        }
                        
                        // Setze Berechtigungen und verschiebe den Client
                        $logMessage("Setting channel group " . $cfg['channels']['settings']['channelGroup'] . " for user");
                        $groupResult = $ts->setClientChannelGroup((int)$cfg['channels']['settings']['channelGroup'], $channelInfo['cid'], $clientInfo['client_database_id']);
                        if(!$ts->succeeded($groupResult)) {
                            $logMessage("Failed to set channel group: " . json_encode($groupResult));
                        }
                        
                        $logMessage("Moving client to new channel");
                        $moveResult = $ts->clientMove($clientInfo['clid'], $channelInfo['cid']);
                        if(!$ts->succeeded($moveResult)) {
                            $logMessage("Failed to move client: " . json_encode($moveResult));
                        }
                        
                        // Sende Erfolgsnachrichten
                        $logMessage("Sending success messages to client");
                        foreach($cfg['messages']['toUser'] as $message) {
                            $ts->sendMessage(1, $clientInfo['clid'], str_replace(
                                ['%clientNickname%', '%channelNumber%', '%channelPassword%'], 
                                [$clientInfo['client_nickname'], $channelNumber, $channelPassword], 
                                $message
                            ));
                        }
                        
                        // Wenn ein Kanalwechsel stattgefunden hat, informiere den Benutzer über den Cooldown
                        if($otherChannelSystem) {
                            $ts->sendMessage(1, $clientInfo['clid'], "Note: You've just switched from another channel type. You must wait 24 hours before you can switch again.");
                        }
                        
                        // Speichere in der Datenbank
                        $logMessage("Saving channel to database");
                        $insertResult = $mongoDB->privateChannels->insertOne([
                            'clientUniqueIdentifier' => $clientInfo['client_unique_identifier'],
                            'channelId' => (int)$channelInfo['cid'],
                            'autoTeleport' => false,
                            'createdAt' => new MongoDB\BSON\UTCDateTime(),
                            'channelName' => $clientInfo['client_nickname'],
                            'channelNumber' => $channelNumber
                        ]);
                        
                        if(!$insertResult->getInsertedCount()) {
                            $logMessage("Failed to insert channel record into database");
                        } else {
                            $logMessage("Successfully created private channel with number " . $channelNumber);
                        }
                    } else {
                        // Kanalerstellung fehlgeschlagen
                        $logMessage("Channel creation failed. Full response: " . json_encode($channelCreateResult));
                        $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                        $ts->sendMessage(1, $clientInfo['clid'], $cfg['messages']['channelCreateFailed']);
                    }
                } else {
                    // Benutzer hat bereits einen Kanal
                    $logMessage("User already has a channel with ID: " . $checkChannel['channelId']);
                    $channelExists = $ts->getElement('data', $ts->channelInfo($checkChannel['channelId']));
                    
                    if($channelExists) {
                        $logMessage("Moving user to existing channel");
                        $ts->clientMove($clientInfo['clid'], $checkChannel['channelId']);
                        $ts->sendMessage(1, $clientInfo['clid'], $cfg['messages']['haveChannel']);
                    } else {
                        // Kanal existiert nicht mehr, aber der Datenbankeintrag besteht noch
                        $logMessage("Channel exists in database but not on server. Deleting orphaned record.");
                        $mongoDB->privateChannels->deleteOne(['channelId' => (int)$checkChannel['channelId']]);
                        // Rekursiver Aufruf, um einen neuen Kanal zu erstellen
                        $logMessage("Restarting channel creation process");
                        new privateChannels($ts, $clientInfo, $mongoDB, $cfg, $ezApp);
                    }
                }
            } else {
                $logMessage("User does not have required permissions. Groups: " . json_encode($clientInfo['client_servergroups']));
                $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
                foreach($cfg['messages']['noAccess'] as $message) {
                    $ts->sendMessage(1, $clientInfo['clid'], $message);
                }
            }
        } catch (Exception $e) {
            // Definiere Log-Verzeichnis wenn noch nicht geschehen
            if (!isset($logDir)) {
                $logDir = '/home/query/logs/privateChannels';
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
            
            // Versuche, den Client zu benachrichtigen
            if(isset($clientInfo['clid'])) {
                $ts->sendMessage(1, $clientInfo['clid'], $cfg['messages']['channelCreateFailed']);
                $ts->clientMove($clientInfo['clid'], $clientInfo['lastChannel']);
            }
        }
    }
}