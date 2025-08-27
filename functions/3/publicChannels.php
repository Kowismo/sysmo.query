<?php
class publicChannels {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $channelList = $ezApp->channelList();
        
        // Sammle Kanaländerungen in einem Array
        $channelEdits = [];

        foreach ($cfg['channels'] as $channelId => $item) {
            $list = [];

            // Sammle alle Kanäle unter dem Elternkanal
            foreach ($channelList as $channel) {
                if ($channel['pid'] == $channelId) {
                    $list[] = $channel;
                }
            }

            // Zähle die Kanäle und wie viele davon aktiv genutzt werden
            $channels = count($list);
            $takenChannels = 0;
            $emptyChannels = [];
            
            // Identifiziere belegte und leere Kanäle
            foreach ($list as $index => $ch) {
                if ($ch['total_clients'] > 0) {
                    $takenChannels++;
                } else {
                    // Merke leere Kanäle, auch den letzten
                    $emptyChannels[] = $ch;
                }
            }
            
            $freeChannels = $channels - $takenChannels;
            
            // Füge neue Kanäle hinzu, wenn zu wenige freie Kanäle
            if($freeChannels < $item['minChannels'] && $channels < $item['maxChannels']) {
                // Bestimme, wie viele neue Kanäle erstellt werden müssen
                $newChannelsNeeded = min(
                    $item['minChannels'] - $freeChannels,
                    $item['maxChannels'] - $channels
                );
                
                // Ändere vorletzten Kanal zu einem Mittelkanal, wenn wir einen neuen hinzufügen
                if ($newChannelsNeeded > 0 && isset($list[count($list) - 1])) {
                    $lastCid = $list[count($list) - 1]['cid'];
                    $middleName = str_replace(['%i%'], [$channels], '╠ ' . $cfg['channelName']);
                    
                    // Aktualisiere nur den Namen des letzten Kanals
                    if ($list[count($list) - 1]['channel_name'] !== $middleName) {
                        try {
                            // DIREKTE AKTUALISIERUNG: Ändere den letzten Kanal sofort zum Mittelkanal
                            $ts->channelEdit($lastCid, ['channel_name' => $middleName]);
                        } catch (Exception $e) {
                            // Fehler ignorieren
                        }
                    }
                }
                
                // Erstelle die neuen Kanäle
                for($i = 1; $i <= $newChannelsNeeded; $i++) {
                    $newChannelName = str_replace('%i%', $channels + $i, '╚ ' . $cfg['channelName']);
                    
                    $channelOptions = [
                        'channel_name' => $newChannelName,
                        'channel_flag_maxfamilyclients_unlimited' => 1,
                        'channel_flag_permanent' => 1,
                        'channel_codec_quality' => 10,
                        'cpid' => $channelId,
                    ];
                    
                    // Füge die Beschreibung NUR bei der Erstellung eines neuen Channels hinzu
                    if (isset($cfg['channelDescription'])) {
                        $channelOptions['channel_description'] = $cfg['channelDescription'];
                    }
                    
                    if($item['clientsLimit'] == 0) {
                        $channelOptions['channel_flag_maxclients_unlimited'] = 1;
                    } else {
                        $channelOptions['channel_flag_maxclients_unlimited'] = 0;
                        $channelOptions['channel_maxclients'] = $item['clientsLimit'];
                    }
                    
                    // Erstelle den Kanal
                    try {
                        $ts->channelCreate($channelOptions);
                    } catch (Exception $e) {
                        // Fehler ignorieren
                    }
                }
                
                // Nach dem Erstellen neuer Kanäle, aktualisiere die Kanalliste
                $tempChannelList = $ezApp->channelList();
                $list = [];
                foreach ($tempChannelList as $channel) {
                    if ($channel['pid'] == $channelId) {
                        $list[] = $channel;
                    }
                }
                $channels = count($list);
            } 
            // Entferne überschüssige leere Kanäle
            elseif ($freeChannels > $item['minChannels'] && $channels > 0) {
                // Berechne, wie viele Kanäle entfernt werden können
                $channelsToRemove = min($freeChannels - $item['minChannels'], count($emptyChannels));
                
                if ($channelsToRemove > 0) {
                    // Sortiere leere Kanäle in umgekehrter Reihenfolge (vom Ende beginnend)
                    usort($emptyChannels, function($a, $b) {
                        // Vergleiche die Positionsnummern aus den Kanalnamen
                        preg_match('/\d+/', $a['channel_name'], $matchesA);
                        preg_match('/\d+/', $b['channel_name'], $matchesB);
                        $numA = isset($matchesA[0]) ? (int)$matchesA[0] : 0;
                        $numB = isset($matchesB[0]) ? (int)$matchesB[0] : 0;
                        return $numB - $numA; // Absteigend sortieren
                    });
                    
                    // Liste der zu löschenden Kanäle (von hinten beginnend)
                    $channelsToDelete = array_slice($emptyChannels, 0, $channelsToRemove);
                    
                    // Lösche die überschüssigen leeren Kanäle
                    foreach ($channelsToDelete as $index => $channelToDelete) {
                        try {
                            $ts->channelDelete($channelToDelete['cid'], 1);
                        } catch (Exception $e) {
                            // Fehler ignorieren
                        }
                    }
                    
                    // Nach dem Löschen, aktualisiere die Kanalliste
                    $tempChannelList = $ezApp->channelList();
                    $updatedList = [];
                    foreach ($tempChannelList as $channel) {
                        if ($channel['pid'] == $channelId) {
                            $updatedList[] = $channel;
                        }
                    }
                    
                    // Aktualisiere den letzten Kanal sofort
                    if (!empty($updatedList)) {
                        // Sortiere die Liste nach Kanalnummern
                        usort($updatedList, function($a, $b) {
                            preg_match('/\d+/', $a['channel_name'], $matchesA);
                            preg_match('/\d+/', $b['channel_name'], $matchesB);
                            $numA = isset($matchesA[0]) ? (int)$matchesA[0] : 0;
                            $numB = isset($matchesB[0]) ? (int)$matchesB[0] : 0;
                            return $numA - $numB; // Aufsteigend sortieren
                        });
                        
                        $lastIndex = count($updatedList) - 1;
                        if ($lastIndex >= 0) {
                            $lastChannel = $updatedList[$lastIndex];
                            $lastChannelName = str_replace(['%i%'], [$lastIndex + 1], '╚ ' . $cfg['channelName']);
                            
                            if ($lastChannel['channel_name'] !== $lastChannelName) {
                                try {
                                    $ts->channelEdit($lastChannel['cid'], ['channel_name' => $lastChannelName]);
                                } catch (Exception $e) {
                                    // Fehler ignorieren
                                }
                            }
                        }
                    }
                    
                    // Aktualisierte Liste für weitere Verarbeitung
                    $list = $updatedList;
                }
            }
            
            // Aktualisiere alle Kanalnamen entsprechend ihrer Position
            for ($i = 0; $i < count($list); $i++) {
                $ch = $list[$i];
                $position = $i + 1;
                
                $newName = '';
                if ($position == 1) {
                    $newName = str_replace(['%i%'], [$position], '╔ ' . $cfg['channelName']);
                } elseif ($position == count($list)) {
                    $newName = str_replace(['%i%'], [$position], '╚ ' . $cfg['channelName']);
                } else {
                    $newName = str_replace(['%i%'], [$position], '╠ ' . $cfg['channelName']);
                }
                
                if ($ch['channel_name'] !== $newName) {
                    $channelEdits[$ch['cid']] = ['channel_name' => $newName];
                }
            }
        }

        // Führe die restlichen Kanaländerungen durch
        foreach ($channelEdits as $cid => $updateData) {
            try {
                $ts->channelEdit($cid, $updateData);
            } catch (Exception $e) {
                // Fehler ignorieren
            }
        }
    }
}
?>