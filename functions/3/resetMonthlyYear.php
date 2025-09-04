<?php
class resetMonthlyYear {
    public function __construct($ts, $cfg, $mongoDB, $ezApp) {
        $currentDay = date('j');
        $currentHour = date('G');
        
        // Log-Verzeichnis
        $logDir = '/home/query/logs/resetMonthlyYear';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        
        // ROBUSTERE RESET-LOGIK: Prüfe ob Reset für aktuellen Monat noch fehlt
        $lastResetMonth = $mongoDB->botData->findOne(['type' => 'lastMonthReset']);
        $currentMonth = date('Y-m');
        $currentMonthName = date('F Y'); // z.B. "September 2025"
        $lastMonth = date('Y-m', strtotime('last month'));
        $lastMonthName = date('F Y', strtotime('last month')); // z.B. "August 2025"
        
        $needsReset = false;
        
        // Reset ist nötig wenn:
        // 1. Noch nie ein Reset passiert ist
        // 2. Der letzte Reset nicht für den aktuellen Monat war
        // 3. Es der erste Tag des Monats ist (auch außerhalb 0-1 Uhr)
        if(!$lastResetMonth) {
            $needsReset = true;
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - No previous reset found, performing reset\n", FILE_APPEND);
        } elseif($lastResetMonth['month'] != $currentMonth) {
            if($currentDay == 1) {
                $needsReset = true;
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Monthly reset needed for $currentMonth (last was {$lastResetMonth['month']})\n", FILE_APPEND);
            } else {
                // Prüfe ob wir einen verpassten Reset nachholen müssen
                $lastResetTimestamp = strtotime($lastResetMonth['month'] . '-01');
                $currentMonthTimestamp = strtotime($currentMonth . '-01');
                $monthsDifference = ($currentMonthTimestamp - $lastResetTimestamp) / (30 * 24 * 3600); // Ungefähr
                
                if($monthsDifference >= 1) {
                    $needsReset = true;
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Catching up missed reset for $currentMonth\n", FILE_APPEND);
                }
            }
        }
        
        if($needsReset) {
                // Finde den monatlichen Sieger vom letzten Monat
                $topMonthlyUsers = $mongoDB->serverClients->find(
                    [], 
                    ['sort' => ['monthlyActivity' => -1], 'limit' => 20]
                )->toArray();
                
                if (!empty($topMonthlyUsers)) {
                    // Archiviere die Top-Benutzer
                    $mongoDB->botData->insertOne([
                        'type' => 'monthlyTopArchive',
                        'month' => $lastMonth,
                        'monthName' => $lastMonthName,
                        'data' => $topMonthlyUsers,
                        'timestamp' => time()
                    ]);
                    
                    $logMessage = date('Y-m-d H:i:s') . " - Archived monthly top users for $lastMonthName\n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);
                    
                    // VERWENDUNG DER FESTEN TEMPLATE-ID AUS CONFIG (monthlyWinnerTemplate veraltet)
                    if(isset($cfg['writeTops']['winnerGroups']['templateId'])) {
                        // 1. Gewinner-Gruppe für den letzten Monat erstellen
                        $this->createWinnerGroup($ts, $mongoDB, $cfg, $topMonthlyUsers[0], 'month', $lastMonthName, $logFile);
                        
                        // 2. Neue Gruppe für den aktuellen Monat vorbereiten
                        $newMonthGroupName = "Most Active - " . $currentMonthName;
                        $copyResult = $ts->serverGroupCopy($cfg['writeTops']['winnerGroups']['templateId'], 0, $newMonthGroupName, 1);
                        if($ts->succeeded($copyResult)) {
                            // RBMod-Style: Warten nach serverGroupCopy
                            sleep(1);
                            $logMessage = date('Y-m-d H:i:s') . " - Prepared new group for current month: '$newMonthGroupName' from template ID {$cfg['writeTops']['winnerGroups']['templateId']}\n";
                            file_put_contents($logFile, $logMessage, FILE_APPEND);
                        }
                    }
                }
                
                // Setze monthlyActivity für alle Benutzer zurück
                $mongoDB->serverClients->updateMany(
                    [], 
                    ['$set' => ['monthlyActivity' => 0]]
                );
                
                // Aktualisiere den letzten Reset-Zeitstempel
                if($lastResetMonth) {
                    $mongoDB->botData->updateOne(
                        ['type' => 'lastMonthReset'],
                        ['$set' => ['month' => $currentMonth, 'timestamp' => time()]]
                    );
                } else {
                    $mongoDB->botData->insertOne([
                        'type' => 'lastMonthReset',
                        'month' => $currentMonth,
                        'timestamp' => time()
                    ]);
                }
                
                $logMessage = date('Y-m-d H:i:s') . " - Monthly activity reset performed\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
            }
        }
        
        // ROBUSTERE JAHRES-RESET-LOGIK
        $lastResetYear = $mongoDB->botData->findOne(['type' => 'lastYearReset']);
        $currentYear = date('Y');
        $lastYear = date('Y', strtotime('last year'));
        
        $needsYearlyReset = false;
        
        // Jahres-Reset ist nötig wenn:
        // 1. Noch nie ein Jahres-Reset passiert ist
        // 2. Der letzte Reset nicht für das aktuelle Jahr war
        // 3. Es der erste Tag des Jahres ist
        if(!$lastResetYear) {
            if($currentDay == 1 && date('n') == 1) {
                $needsYearlyReset = true;
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - No previous yearly reset found, performing reset\n", FILE_APPEND);
            }
        } elseif($lastResetYear['year'] != $currentYear) {
            if($currentDay == 1 && date('n') == 1) {
                $needsYearlyReset = true;
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Yearly reset needed for $currentYear (last was {$lastResetYear['year']})\n", FILE_APPEND);
            }
        }
        
        if($needsYearlyReset) {
                // Finde den jährlichen Sieger
                $topYearlyUsers = $mongoDB->serverClients->find(
                    [], 
                    ['sort' => ['yearlyActivity' => -1], 'limit' => 20]
                )->toArray();
                
                if (!empty($topYearlyUsers)) {
                    // Archiviere die Top-Benutzer
                    $mongoDB->botData->insertOne([
                        'type' => 'yearlyTopArchive',
                        'year' => $lastYear,
                        'data' => $topYearlyUsers,
                        'timestamp' => time()
                    ]);
                    
                    $logMessage = date('Y-m-d H:i:s') . " - Archived yearly top users for $lastYear\n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);
                    
                    // VERWENDUNG DER FESTEN TEMPLATE-ID AUS CONFIG (yearlyWinnerTemplate veraltet)
                    if(isset($cfg['writeTops']['winnerGroups']['templateId'])) {
                        // 1. Gewinner-Gruppe für das letzte Jahr erstellen
                        $this->createWinnerGroup($ts, $mongoDB, $cfg, $topYearlyUsers[0], 'year', $lastYear, $logFile);
                        
                        // 2. Neue Gruppe für das aktuelle Jahr vorbereiten
                        $newYearGroupName = "Most Active - Year " . $currentYear;
                        $copyResult = $ts->serverGroupCopy($cfg['writeTops']['winnerGroups']['templateId'], 0, $newYearGroupName, 1);
                        if($ts->succeeded($copyResult)) {
                            // RBMod-Style: Warten nach serverGroupCopy
                            sleep(1);
                            $logMessage = date('Y-m-d H:i:s') . " - Prepared new group for current year: '$newYearGroupName' from template ID {$cfg['writeTops']['winnerGroups']['templateId']}\n";
                            file_put_contents($logFile, $logMessage, FILE_APPEND);
                        }
                    }
                }
                
                // Setze yearlyActivity für alle Benutzer zurück
                $mongoDB->serverClients->updateMany(
                    [], 
                    ['$set' => ['yearlyActivity' => 0]]
                );
                
                // Aktualisiere den letzten Reset-Zeitstempel
                if($lastResetYear) {
                    $mongoDB->botData->updateOne(
                        ['type' => 'lastYearReset'],
                        ['$set' => ['year' => $currentYear, 'timestamp' => time()]]
                    );
                } else {
                    $mongoDB->botData->insertOne([
                        'type' => 'lastYearReset',
                        'year' => $currentYear,
                        'timestamp' => time()
                    ]);
                }
                
                $logMessage = date('Y-m-d H:i:s') . " - Yearly activity reset performed\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
            }
        }
        
        // ZUSÄTZLICHE SICHERHEIT: Prüfe auf verpasste Archive-Einträge
        $this->checkMissingArchives($mongoDB, $logFile);
    }
    
    // Neue Funktion: Prüfe und erstelle fehlende Archive-Einträge
    private function checkMissingArchives($mongoDB, $logFile) {
        $currentMonth = date('Y-m');
        
        // Prüfe die letzten 3 Monate ob Archive-Einträge fehlen
        for($i = 1; $i <= 3; $i++) {
            $checkMonth = date('Y-m', strtotime("-$i month"));
            $checkMonthName = date('F Y', strtotime("-$i month"));
            
            // Prüfe ob Archive-Eintrag existiert
            $existingArchive = $mongoDB->botData->findOne([
                'type' => 'monthlyTopArchive',
                'month' => $checkMonth
            ]);
            
            if(!$existingArchive) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Missing archive for $checkMonthName, checking if data available\n", FILE_APPEND);
                
                // Versuche Daten für diesen Monat zu finden
                // (Hier könntest du die Logik einfügen um historische Daten zu rekonstruieren)
            }
        }
    }
    
    private function createWinnerGroup($ts, $mongoDB, $cfg, $winner, $period, $periodName, $logFile) {
        $winnerId = $winner['clientDatabaseId'];
        $winnerNickname = $winner['clientNickname'];
        
        // Bestimme die Template-Gruppe
        $templateKey = $period . 'lyWinnerTemplate';
        // Verwende die in der Config festgelegte Template-ID (z.B. 442 für "TOP » Most Active - TEMPLATE")
        $baseGroupId = $cfg['writeTops']['winnerGroups']['templateId'];
        $newGroupName = "Most Active - " . $periodName;
        
        $logMessage = date('Y-m-d H:i:s') . " - Creating winner group '$newGroupName' for $winnerId ($winnerNickname)\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Kopiere die Template-Gruppe für den Gewinner
        $createResult = $ts->serverGroupCopy($baseGroupId, 0, $newGroupName, 1);
        if($ts->succeeded($createResult)) {
            $data = $ts->getElement('data', $createResult);
            $newGroupId = isset($data['sgid']) ? $data['sgid'] : $data;
            
            $logMessage = date('Y-m-d H:i:s') . " - Successfully created group '$newGroupName' with ID $newGroupId\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            
            // RBMod-Style: Warten nach serverGroupCopy (TS3 braucht Zeit für Gruppe)
            sleep(1);
            
            // Versuche den Gewinner zur neuen Gruppe hinzuzufügen
            $addResult = $ts->serverGroupAddClient($newGroupId, $winnerId);
            if($ts->succeeded($addResult)) {
                $logMessage = date('Y-m-d H:i:s') . " - Successfully added user $winnerId ($winnerNickname) to group $newGroupId\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
                
                // Speichere die Information über die erstellte Gruppe
                $mongoDB->botData->insertOne([
                    'type' => 'winnerGroup',
                    'period' => $period,
                    'periodName' => $periodName,
                    'groupId' => (int)$newGroupId,
                    'groupName' => $newGroupName,
                    'winnerId' => (int)$winnerId,
                    'winnerNickname' => $winnerNickname,
                    'timestamp' => time(),
                    'assigned' => true
                ]);
            } else {
                $errorMsg = $ts->getElement('msg', $addResult);
                $logMessage = date('Y-m-d H:i:s') . " - Failed to add user $winnerId ($winnerNickname) to group $newGroupId. User probably offline. Error: $errorMsg\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
                
                // Speichere Pending Assignment für offline User
                $mongoDB->botData->insertOne([
                    'type' => 'pendingGroupAssignment',
                    'clientDatabaseId' => (int)$winnerId,
                    'clientNickname' => $winnerNickname,
                    'groupId' => (int)$newGroupId,
                    'groupName' => $newGroupName,
                    'reason' => ucfirst($period) . 'ly winner - ' . $periodName,
                    'timestamp' => time(),
                    'retryCount' => 0
                ]);
                
                // Speichere auch die Gruppen-Info
                $mongoDB->botData->insertOne([
                    'type' => 'winnerGroup',
                    'period' => $period,
                    'periodName' => $periodName,
                    'groupId' => (int)$newGroupId,
                    'groupName' => $newGroupName,
                    'winnerId' => (int)$winnerId,
                    'winnerNickname' => $winnerNickname,
                    'timestamp' => time(),
                    'assigned' => false
                ]);
                
                $logMessage = date('Y-m-d H:i:s') . " - Created pending assignment for offline user $winnerId ($winnerNickname)\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
            }
        } else {
            $errorMsg = $ts->getElement('msg', $createResult);
            $logMessage = date('Y-m-d H:i:s') . " - Failed to create group for $periodName. Error: $errorMsg\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
    
    private function renameTemplateGroup($ts, $cfg, $period, $newPeriodName, $logFile) {
        // TemplateKey Logik entfällt – stattdessen feste Template-ID nutzen
        $templateGroupId = $cfg['writeTops']['winnerGroups']['templateId'];
        
        if ($period == 'monthly') {
            $newGroupName = "Most Active - " . $newPeriodName;
        } else {
            $newGroupName = "Most Active - Year " . $newPeriodName;
        }
        
        $logMessage = date('Y-m-d H:i:s') . " - Renaming template group $templateGroupId to '$newGroupName'\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Benenne die Template-Gruppe um
        $renameResult = $ts->serverGroupRename($templateGroupId, $newGroupName);
        if($ts->succeeded($renameResult)) {
            $logMessage = date('Y-m-d H:i:s') . " - Successfully renamed template group to '$newGroupName'\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        } else {
            $errorMsg = $ts->getElement('msg', $renameResult);
            $logMessage = date('Y-m-d H:i:s') . " - Failed to rename template group. Error: $errorMsg\n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }
    }
}